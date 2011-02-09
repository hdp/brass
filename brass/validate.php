<?php
require('_std-include.php');

$mypage = page::standard();
if ( REGISTRATION_DISABLED ) {
    $mypage->title_body('User validation');
    $mypage->leaf( 'p',
                   'New user registration and validation are currently disabled by an Administrator. Please wait until registration and validation are re-enabled.'
                   );
    $mypage->leaf( 'p',
                   'You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
} else if ( $_SESSION['LoggedIn'] ) {
    $mypage->title_body('Logged in');
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
} else if ( !isset($_GET['UserID']) or !isset($_GET['VString']) ) {
    $mypage->title_body('Invalid URL');
    $mypage->leaf( 'p',
                   'The URL you used to get here is not valid. If you copied and pasted the URL from the email, you may not have copied the entire line. Please try again.'
                   );
} else {
    $EscapedUserID = sanitise_int($_GET['UserID']);
    $row = dbquery( DBQUERY_READ_SINGLEROW,
                    'SELECT "Name", "ScrambleKey", "UserValidated" FROM "User" WHERE "UserID" = :user:',
                    'user' , $EscapedUserID
                    );
    if ( $row === 'NONE' ) {
        $mypage->title_body('Error');
        $mypage->leaf( 'p',
                       'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
    } else if ( $row['UserValidated'] ) {
        $mypage->title_body('Already validated');
        $mypage->leaf( 'p',
                       'You are already validated (perhaps you clicked on the link twice). Please return to the Main Page by clicking <a href="index.php">here</a>.'
                       );
    } else {
        $EscapedVString = trim(@$_GET['VString']);
        $EscapedVString = crypt($EscapedVString,$row['ScrambleKey']);
        if ( $row['ScrambleKey'] == $EscapedVString ) {
            dbquery( DBQUERY_WRITE,
                     'UPDATE "User" SET "UserValidated" = 1 WHERE "UserID" = :user:',
                     'user' , $EscapedUserID
                     );
            $mypage->title_body('Validation successful');
            $mypage->leaf( 'p',
                           'Validation was successful. You can click <a href="index.php">here</a> to visit the main page.'
                           );
            $mypage->leaf( 'h3',
                           'If you haven\'t yet played Brass'
                           );
            $mypage->leaf( 'p',
                           'If you have not played Brass before, you may find it helpful to play through a game (or a few rounds) using <a href="http://orderofthehammer.com/windowsapp.htm">the single-player windows application</a>, to give yourself a feel for the rules before you play with other people.'
                           );
        } else {
            $mypage->title_body('Incorrect validation string');
            $mypage->leaf( 'p',
                           'The validation string in the URL is incorrect. If you copied and pasted the line from the email, you may not have copied the entire line. Please try again.'
                           );
        }
    }
}
$mypage->finish();

?>