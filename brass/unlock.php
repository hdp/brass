<?php
$NoLoginStuff = true;
require('_std-include.php');

$mypage = page::standard();
if ( @$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Logged in');
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
} else {
    if ( !isset($_GET['UserID']) or !isset($_GET['VString']) ) {
        $mypage->title_body('Invalid URL');
        $mypage->leaf( 'p',
                       'The URL you used to get here is not valid. If you copied and pasted the URL from the email, you may not have copied the entire line. Please try again.'
                       );
    } else {
        $EscapedUserID = sanitise_int($_GET['UserID']);
        $row = dbquery( DBQUERY_READ_SINGLEROW,
                        'SELECT "ScrambleKey", "UserValidated" FROM "User" WHERE "UserID" = :user:',
                        'user' , $EscapedUserID
                        );
        if ( $row === 'NONE' or !$row['UserValidated'] ) {
            $mypage->title_body('Error');
            $mypage->leaf( 'p',
                           'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                           );
        } else {
            $EscapedVString = trim(@$_GET['VString']);
            $EscapedVString = crypt($EscapedVString, $row['ScrambleKey']);
            if ( $row['ScrambleKey'] == $EscapedVString ) {
                dbquery( DBQUERY_WRITE,
                         'UPDATE "User" SET "BecomesAccessible" = UTC_TIMESTAMP() WHERE "UserID" = :user:',
                         'user' , $EscapedUserID
                         );
                $mypage->title_body('Account unlocked successfully');
                $mypage->leaf( 'p',
                               'Your account has been successfully unlocked. You will now be able to reattempt logging in. Please click <a href="index.php">here</a> to visit the main page.'
                               );
            } else {
                $mypage->title_body('Incorrect validation string');
                $mypage->leaf( 'p',
                               'The validation string in the URL is incorrect. If you copied and pasted the line from the email, you may not have copied the entire line. Please try again.'
                               );
            }
        }
    }
}
$mypage->finish();

?>