<?php
require('_std-include.php');

$mypage = page::standard();
if ( $_SESSION['LoggedIn'] ) {
    $mypage->title_body('Logged in');
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
} else {
    if ( isset($_POST['username']) ) {
        $EscapedUserName = sanitise_str( $_POST['username'],
                                         STR_GPC |
                                             STR_ESCAPE_HTML |
                                             STR_STRIP_TAB_AND_NEWLINE
                                         );
        $row = dbquery( DBQUERY_READ_SINGLEROW,
                        'SELECT "UserID", "Email", "BecomesAccessible", "UserValidated" FROM "User" WHERE "Name" = :name:',
                        'name' , $EscapedUserName
                        );
        if ( $row === 'NONE' ) {
            $mypage->title_body('Unlock an account');
            $mypage->leaf( 'p',
                           'Couldn\'t find a user named '.
                               $EscapedUserName.
                               '. Please check that you spelled the account name correctly. You can click <a href="unlocke.php">here</a> to try again.'
                           );
        } else if ( !$row['UserValidated'] ) {
            $mypage->title_body('Unlock an account');
            $mypage->leaf( 'p',
                           'That user account isn\'t validated yet. If you haven\'t received your validation email, you can visit <a href="resendvalemail.php">this page</a> to re-send it (although you will need your password to do so).'
                           );
        } else if ( strtotime($row['BecomesAccessible']) - strtotime(now) > 0 ) {
            if ( $row[Email] != '' ) {
                $BAtime = date( 'Y-m-d H:i:s',
                                strtotime($row['BecomesAccessible'])
                                );
                $CharArray = 'abcdefghijklmnopqrstuvwxyz0123456789';
                $thevstring = '';
                for ($i=0; $i<20; $i++) {
                    $j = rand(0, 35);
                    $thevstring .= $CharArray[$j];
                }
                $encryptedthevstring = crypt($thevstring, generateSalt());
                dbquery( DBQUERY_WRITE,
                         'UPDATE "User" SET "ScrambleKey" = :scramblekey: WHERE "UserID" = :user:',
                         'scramblekey' , $encryptedthevstring ,
                         'user'        , $row['UserID']
                         );
                $subject = 'Unlock Brass Online Account';
                $body = '<p>Too many bad attempts have been made to access your account in too short a time. Your account has been locked and will remain locked until '.
                        $BATime.
                        '. To unlock it now without having to wait, please click on the url on the next line, or copy and paste it into your browser\'s address bar.</p><p><a href="'.
                        SITE_ADDRESS.
                        'unlock.php?UserID='.
                        $row['UserID'].
                        '&amp;VString='.
                        $thevstring.
                        '">'.
                        SITE_ADDRESS.
                        'unlock.php?UserID='.
                        $row['UserID'].
                        '&amp;VString='.
                        $thevstring.
                        '</a></p>'.
                        EMAIL_FOOTER;
                if ( send_email($subject, $body, $row['Email'], null) ) {
                    $mypage->title_body('Email sent successfully');
                    $mypage->leaf( 'p',
                                   'You have been sent an email containing instructions for unlocking your account. You can click <a href="index.php">here</a> to return to the Main Page.'
                                   );
                } else {
                    $mypage->title_body('Problem sending email');
                    $mypage->leaf( 'p',
                                   'There was a problem sending the email. Either the account email address is blank, or the email could not be sent for some other reason. You can click <a href="unlocke.php">here</a> to try again, or <a href="index.php">here</a> to return to the Main Page.'
                                   );
                }
            } else {
                $mypage->title_body('Problem sending email');
                $mypage->leaf( 'p',
                               'There was a problem sending the email. Either the account email address is blank, or the email could not be sent for some other reason. You can click <a href="unlocke.php">here</a> to try again, or <a href="index.php">here</a> to return to the Main Page.'
                               );
            }
        } else {
            $mypage->title_body('Account is not locked');
            $mypage->leaf( 'p',
                           'The account whose name you entered is not currently locked; you can log in if you know the password. If you do not know the password, please click <a href="recoveraccount.php">here</a>. Otherwise, please click <a href="index.php">here</a> to return to the Main Page.'
                           );
        }
    } else {
        $mypage->title_body('Unlock an account');
        $mypage->loginbox();
        $mypage->leaf( 'p',
                       'This page allows you to unlock an account that has been locked due to attempts to access it using an incorrect password. To do this, you need to have access to the email address associated to the account. Please enter the name of the account into the box and click "submit", and you will be emailed; follow the instructions in the email to unlock your account.'
                       );
        $mypage->opennode( 'form',
                           'action="unlocke.php" method="POST"'
                           );
        $mypage->emptyleaf( 'input',
                            'type="text" name="username" size=20 maxlength=20'
                            );
        $mypage->emptyleaf( 'input',
                            'type="submit" name="FormSubmit" value="Submit"'
                            );
        $mypage->closenode(); // form
        $mypage->leaf( 'p',
                       'You can return to the Main Page by clicking <a href=\"index.php\">here</a>.'
                       );
    }
}
$mypage->finish();

?>