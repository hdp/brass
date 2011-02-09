<?php
require('_std-include.php');

$mypage = page::standard();
$mypage->title_body('Send account recovery email');
if ( @$_SESSION['LoggedIn'] ) {
    $_SESSION['AllowUse'] = 0;
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
    $mypage->finish();
} else if ( isset($_POST['AccountName']) ) {
    $EscapedAccountName = sanitise_str( $_POST['AccountName'],
                                        STR_GPC |
                                            STR_ESCAPE_HTML |
                                            STR_STRIP_TAB_AND_NEWLINE
                                        );
    if ( $EscapedAccountName == '' ) {
        $mypage->leaf( 'p',
                       'You did not enter an account name. Please click <a href="recoveraccount.php">here</a> to try again, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "UserID", "Email", "UserValidated" FROM "User" WHERE "Name" = :name:',
                   'name' , $EscapedAccountName
                   );
    if ( $QR === 'NONE' ) {
        $mypage->leaf( 'p',
                       'Couldn\'t find a user named '.
                           $EscapedAccountName.
                           '. Please check that you spelled the account name correctly.'
                       );
    } else if ( !$QR['UserValidated'] ) {
        $mypage->leaf( 'p',
                       'That user account isn\'t validated yet. If you haven\'t received your validation email, you can visit <a href="resendvalemail.php">this page</a> to re-send it (although you will need your password to do so).'
                       );
    } else if ( $QR['Email'] == '' ) {
        $mypage->leaf( 'p',
                       'There was a problem sending the email. Either the account email address is blank, or the email could not be sent for some other reason. You might want to try again; if it still doesn\'t work, you can ask the Administrator to investigate, but be aware that the Administrator will only go so far as to check for problems with this script and with the site\'s ability to send emails, not give you access to your account.'
                       );
    } else {
        $CharArray = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $thevstring = '';
        for ($i=0; $i<20; $i++) {
            $j = rand(0, 35);
            $thevstring .= $CharArray[$j];
        }
        $encryptedthevstring = crypt($thevstring, generateSalt());
        $QueryResult = dbquery( DBQUERY_WRITE,
                                'UPDATE "User" SET "ScrambleKey" = :scramblekey: WHERE "UserID" = :user:',
                                'scramblekey' , $encryptedthevstring ,
                                'user'        , $QR['UserID']
                                );
        $subject = 'Account Recovery Email for Brass';
        $body = '<p>Account recovery has been requested for your account '.
                $EscapedAccountName.
                ' for Brass. If it was not you who submitted the request, please ignore this email.</p><p>Please click on the url on the next line, or copy and paste it into your browser\'s address bar.</p><p><a href="'.
                SITE_ADDRESS.
                'recoveraccountb.php?UserID='.
                $QR['UserID'].
                '&amp;VString='.
                $thevstring.
                '">'.
                SITE_ADDRESS.
                'recoveraccountb.php?UserID='.
                $QR['UserID'].
                '&amp;VString='.
                $thevstring.
                '</a></p>'.
                EMAIL_FOOTER;
        if ( send_email($subject, $body, $QR['Email'], null) ) {
            $mypage->leaf( 'p',
                           'An account recovery email for '.
                               $EscapedAccountName.
                               ' has been sent.'
                           );
            $mypage->finish();
        } else {
            $mypage->leaf( 'p',
                           'There was a problem sending the email. Either the account email address is blank, or the email could not be sent for some other reason. You might want to try again; if it still doesn\'t work, you can ask the Administrator to investigate, but be aware that the Administrator will only go so far as to check for problems with this script and with the site\'s ability to send emails, not give you access to your account.'
                           );
        }
    }
} else {
    $EscapedAccountName = '';
    $mypage->leaf( 'p',
                   'This page may be used to attempt to recover access to an account for which you have forgotten the password. Use this feature if you are the owner of the account and you cannot remember your password. You will need to know your Secret Answer, and have access to your email address.'
                   );
    $mypage->leaf( 'p',
                   'Note that this feature is of no use to you if you no longer have access to the email address registered to your account, or if you cleared the email address field after responding to your validation email. In this case you will have to try and remember your password!'
                   );
    $mypage->leaf( 'p',
                   'Note: The Administrator will NOT give you access to accounts to which you have forgotten the password. If you forgot the password to your account, and you either lost access to your email address, removed the record of your email address on here, or forgot your Secret Answer, you will just have to register a new account.'
                   );
}
$mypage->opennode( 'form',
                   'action="recoveraccount.php" method="POST"'
                   );
$mypage->text('Account name:');
$mypage->emptyleaf( 'input',
                    'type="text" name="AccountName" size="20" maxlength="20" value="'.
                        $EscapedAccountName.
                        '"'
                    );
$mypage->emptyleaf( 'input',
                    'type="submit" value="Send Email"'
                    );
$mypage->closenode(); // form
$mypage->leaf( 'p',
               'Click <a href="index.php">here</a> to return to the Main Page.'
               );
$mypage->finish();

?>