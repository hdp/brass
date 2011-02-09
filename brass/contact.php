<?php
require('_std-include.php');

$mypage = page::standard();
if ( isset($_POST['FormSubmit']) ) {
    require(HIDDEN_FILES_PATH.'sanitise_str_fancy.php');
    $EscapedUserID = sanitise_int(@$_POST['Target']);
    $EscapedContent = sanitise_str_fancy( @$_POST['TheEmail'],
                                          6,
                                          25000,
                                          STR_GPC |
                                              STR_EMAIL_FORMATTING |
                                              STR_CONVERT_ESCAPE_SEQUENCES |
                                              STR_STRIP_TAB_AND_NEWLINE
                                          );
    $therow = dbquery( DBQUERY_READ_SINGLEROW,
                       'SELECT "Name", "UserValidated", "AllowContact", "Email", "Pronoun" FROM "User" WHERE "UserID" = :user:',
                       'user' , $EscapedUserID
                       );
    if ( $therow === 'NONE' ) { die($unexpectederrormessage); }
    if ( !$therow['UserValidated'] ) { die($unexpectederrormessage); }
    $PostFailureTitle = false;
    do {
        if ( !$_SESSION['LoggedIn'] ) {
            $PostFailureTitle = 'Not logged in';
            $PostFailureMessage = 'You may not contact '.
                                  $therow['Name'].
                                  ' by email, because you are not logged in. Please click <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
            break;
        }
        if ( $Banned ) {
            $PostFailureTitle = 'Banned';
            $PostFailureMessage = 'You may not contact '.
                                  $therow['Name'].
                                  ' by email, because you are banned. Please click <a href="userdetails.php?UserID='.
                                  $EscapedUserID.
                                  '">here</a> to visit '.
                                  $therow['Name'].
                                  '\'s User Details page, or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
            break;
        }
        if ( !EMAIL_ENABLED ) {
            $PostFailureTitle = 'User email messaging disabled';
            $PostFailureMessage = 'The user email messaging function has been disabled by an Administrator. Please click <a href="userdetails.php?UserID='.
                                  $EscapedUserID.
                                  '">here</a> to visit this user\'s User Details page, or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
            break;
        }
        if ( !$Administrator and
             ( $therow['Email'] == '' or !$therow['AllowContact'] )
             ) {
            $PostFailureTitle = 'User declines contact';
            $PostFailureMessage = 'This user does not wish to be contacted by email. Please click <a href="userdetails.php?UserID='.
                                  $EscapedUserID.
                                  '">here</a> to visit this user\'s User Details page, or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
            break;
        }
        if ( $Administrator and $therow['Email'] == '' ) {
            $PostFailureTitle = 'User has empty email address';
            $PostFailureMessage = 'This user has an empty email address field. Please click <a href="userdetails.php?UserID='.
                                  $EscapedUserID.
                                  '">here</a> to visit this user\'s User Details page, or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
            break;
        }
        if ( $EscapedContent[1] == 1 ) {
            $PostFailureTitle = 'Message too long';
            $PostFailureMessage = 'Your message to '.
                                  $therow['Name'].
                                  ' was too long. The limit is around 25,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before). Please click <a href="userdetails.php?UserID='.
                                  $EscapedUserID.
                                  '">here</a> to visit '.
                                  $therow['Name'].
                                  '\'s User Details page, or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
            break;
        }
        if ( $EscapedContent[1] == -1 ) {
            $PostFailureTitle = 'Message too short';
            $PostFailureMessage = 'Your message to '.
                                  $therow['Name'].
                                  ' was too short. It should be at least 6 characters long. Please click <a href="userdetails.php?UserID='.
                                  $EscapedUserID.
                                  '">here</a> to visit '.
                                  $therow['Name'].
                                  '\'s User Details page, or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
            break;
        }
    } while ( false );
    if ( $PostFailureTitle !== false ) {
        $mypage->title_body($PostFailureTitle);
        $mypage->leaf('p', $PostFailureMessage);
        $mypage->leaf( 'textarea',
                       sanitise_str( @$_POST['TheEmail'],
                                     STR_GPC | STR_ESCAPE_HTML
                                     ),
                       'cols=80 rows=20'
                       );
        $mypage->finish();
    }
    $MessageForDatabase = substr($EscapedContent[0], 3, -4);
    $ParagraphLocations = array(0);
    $SearchStartPoint = 0;
    $i = 0;
    while ( true ) {
        $CurrentParameterLocation = strpos(substr($MessageForDatabase,$SearchStartPoint), '</p><p>');
        if ( $CurrentParameterLocation === false ) {
            break;
        } else {
            $ParagraphLocations[]  = $SearchStartPoint +
                                         $CurrentParameterLocation -
                                         7 * $i;
            $SearchStartPoint     += $CurrentParameterLocation + 7;
            $i++;
        }
    }
    $MessageForDatabase = implode(',', $ParagraphLocations).
                          ':'.
                          str_replace('</p><p>', '', $MessageForDatabase);
    dbquery(DBQUERY_START_TRANSACTION);
    $EmailIDNo = dbquery( DBQUERY_INSERT_ID,
                          'INSERT INTO "Email" ("Sender", "Recipient", "DateSent", "EmailText") VALUES (:sender:, :recipient:, UTC_TIMESTAMP(), :message:)',
                          'sender'    , $_SESSION['MyUserID'] ,
                          'recipient' , $EscapedUserID        ,
                          'message'   , $MessageForDatabase
                          );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "RecentEventLog" ("EventType", "EventTime", "ExtraData") VALUES (\'Email\', UTC_TIMESTAMP(), :idno:)',
             'idno' , $EmailIDNo
             );
    switch ( $Pronoun ) {
        case 'He':  $PronounLC = 'his'; break;
        case 'She': $PronounLC = 'her'; break;
        default:    $PronounLC = 'its';
    }
    $subject = 'Brass: Message from '.$_SESSION['MyUserName'];
    $body = '<p>This is a message sent by '.
            $_SESSION['MyUserName'].
            ', another user of <a href="'.
            SITE_ADDRESS.
            '">Brass Online</a>. If you do not wish to receive messages like this one, you can prevent users from contacting you by logging in and changing your preferences on your User Details page. '.
            $_SESSION['MyUserName'].
            ' has not seen your email address, and will not see it unless you choose to reply to '.
            $PronounLC.
            ' message via email. What follows is the text of '.
            $_SESSION['MyUserName'].
            '\'s message.</p>'.
            $EscapedContent[0].
            EMAIL_FOOTER;
    if ( send_email($subject, $body, $therow['Email'], $Email) ) {
        dbquery(DBQUERY_COMMIT);
        $mypage->title_body('Email sent');
        $mypage->leaf( 'p',
                       'Your email was successfully sent to '.
                           $therow['Name'].
                           '. Click <a href="userdetails.php?UserID='.
                           $EscapedUserID.
                           '">here</a> to visit '.
                           $therow['Name'].
                           '\'s User Details page, or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you sent:'
                       );
        $mypage->leaf( 'textarea',
                       sanitise_str( @$_POST['TheEmail'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_CONVERT_ESCAPE_SEQUENCES
                                     ),
                       'cols=80 rows=20'
                       );
    } else {
        dbquery( DBQUERY_ALLPURPOSE_TOLERATE_ERRORS,
                 'ROLLBACK'
                 );
            // Sometimes if email sending fails, it's after a long wait. The
            // use here of tolerate-errors stops the script failing if the
            // "MySQL server has gone away" error occurs. (If this happens then
            // the transaction should in theory already have been rolled back.)
        $mypage->title_body('Problem sending email');
        $mypage->leaf( 'p',
                       'There was a problem sending the email to '.
                           $therow[Name].
                           '. Please consider trying again later. <b>If sending the email fails repeatedly, it is more likely to be a problem with the site than a problem with '.
                           $therow['Name'].
                           '\'s email address; please let an administrator know.</b> Click <a href="userdetails.php?UserID='.
                           $EscapedUserID.
                           '">here</a> to visit '.
                           $therow['Name'].
                           '\'s User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->leaf( 'p',
                       'Here is the text of the message you entered:'
                       );
        $mypage->leaf( 'textarea',
                       sanitise_str( @$_POST['TheEmail'],
                                     STR_GPC | STR_ESCAPE_HTML
                                     ),
                       'cols=80 rows=20'
                       );
    }
} else if ( isset($_GET['UserID']) ) {
    $EscapedUserID = sanitise_int($_GET['UserID']);
    $therow = dbquery( DBQUERY_READ_SINGLEROW,
                       'SELECT "Name", "UserValidated", "AllowContact", "Email", "Pronoun" FROM "User" WHERE "UserID" = :user:',
                       'user' , $EscapedUserID
                       );
    if ( $therow === 'NONE' ) {
        $mypage->title_body('No such user');
        $mypage->leaf( 'p',
                       'There is no user with that user ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( !$therow['UserValidated'] ) {
        if ( $Administrator ) {
            $mypage->title_body('User not validated');
            $mypage->leaf( 'p',
                           'That user is not validated. Please click <a href="userdetails.php?UserID='.
                               $EscapedUserID.
                               '">here</a> to visit this user\'s User Details page, or <a href=\"index.php\">here</a> to return to the Main Page.'
                           );
        } else {
            $mypage->title_body('No such user');
            $mypage->leaf( 'p',
                           'There is no user with that user ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                           );
        }
        $mypage->finish();
    }
    if ( !$Administrator and
         ( $therow['Email'] == '' or !$therow['AllowContact'] )
         ) {
        $mypage->title_body('User declines contact');
        $mypage->leaf( 'p',
                       'This user does not wish to be contacted by email. Please click <a href="userdetails.php?UserID='.
                           $EscapedUserID.
                           '">here</a> to visit this user\'s User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
    } else if ( $Administrator and $therow['Email'] == '' ) {
        $mypage->title_body('User has empty email address');
        $mypage->leaf( 'p',
                       'This user has an empty email address field. Please click <a href="userdetails.php?UserID='.
                           $EscapedUserID.
                           '">here</a> to visit this user\'s User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
    } else if ( !$_SESSION['LoggedIn'] ) {
        $mypage->title_body('Not logged in');
        $mypage->leaf( 'p',
                       'You may not contact '.
                           $therow['Name'].
                           ' by email, because you are not logged in. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
    } else if ( $Banned ) {
        $mypage->title_body('Banned');
        $mypage->leaf( 'p',
                       'You may not contact '.
                           $therow['Name'].
                           ' by email, because you are banned. Please click <a href="userdetails.php?UserID='.
                           $EscapedUserID.
                           '">here</a> to visit '.
                           $therow['Name'].
                           '\'s User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
    } else if ( !EMAIL_ENABLED ) {
            $mypage->title_body('User email messaging disabled');
            $mypage->leaf( 'p',
                           'The user email messaging function has been disabled by an Administrator. Please click <a href="userdetails.php?UserID='.
                               $EscapedUserID.
                               '">here</a> to visit this user\'s User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                           );
    } else {
        switch ( $therow['Pronoun'] ) {
            case 'He':
                $PronounLC = 'he';
                $OtherPossessivePronounLC = 'his';
                $OtherPronoun = 'him';
                break;
            case 'She':
                $PronounLC = 'she';
                $OtherPossessivePronounLC = 'hers';
                $OtherPronoun = 'her';
                break;
            default:
                $PronounLC = 'it';
                $OtherPossessivePronounLC = 'its';
                $OtherPronoun = 'it';
        }
        $mypage->title_body('Contact '.$therow['Name']);
        $mypage->loginbox();
        $mypage->leaf('h1', 'Contact '.$therow['Name']);
        $mypage->leaf( 'p',
                       'This page allows you to send an email to '.
                           $therow['Name'].
                           '. The title of the email will be "Brass: Message from '.
                           $_SESSION['MyUserName'].
                           '". '.
                           $therow['Name'].
                           ' will see your email address, but you will not be able to see '.
                           $OtherPossessivePronounLC.
                           ' unless '.
                           $PronounLC.
                           ' chooses to reply to you via email.'
                       );
        if ( !$therow['AllowContact'] ) {
            $mypage->leaf( 'p',
                           'Please note that '.
                               $therow['Name'].
                               ' has indicated that '.
                               $PronounLC.
                               ' does not wish to be contacted in this fashion, and that you are only being given the option to contact '.
                               $OtherPronoun.
                               ' because you are an Administrator.',
                           'style="font-weight: bold;"'
                           );
        }
        $mypage->leaf( 'p',
                       'Enter your message to '.
                           $therow['Name'].
                           ' in the text area, and then click "Send". The available formatting options are detailed <a href="http://orderofthehammer.com/credits.htm#formatting">here</a>. The limit is 25,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before).'
                       );
        $mypage->leaf( 'p',
                       'Please note that a record is kept of the messages that this form is used to send. Do not abuse this form. In particular, don\'t use this form to collude with another player in a game you\'re playing, unless it\'s a "friendly" game in which you and your opponents have agreed that doing so is acceptable.'
                       );
        $mypage->opennode( 'form',
                           'action="contact.php" method="POST"'
                           );
        $mypage->leaf( 'textarea',
                       '',
                       'cols="100" rows="16" name="TheEmail"'
                       );
        $mypage->opennode('p');
        $mypage->emptyleaf( 'input',
                            'type="submit" name="FormSubmit" value="Send"'
                            );
        $mypage->closenode();
        $mypage->emptyleaf( 'input',
                            'type="hidden" name="Target" value="'.
                                $EscapedUserID.
                                '"'
                            );
        $mypage->closenode();
        $mypage->leaf( 'p',
                       'Click <a href="userdetails.php?UserID='.
                           $EscapedUserID.
                           '">here</a> to return to '.
                           $therow['Name'].
                           '\'s User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
    }
} else {
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks like you\'ve been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
}
$mypage->finish();

?>