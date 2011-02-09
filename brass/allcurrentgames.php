<?php
require('_std-include.php');

$mypage = page::standard();
if ( $_SESSION['LoggedIn'] ) {
    require(HIDDEN_FILES_PATH.'gamelistdisplay.php');
    $mypage->title_body('All games currently in progress');
    $mypage->loginbox(false);
    $mypage->leaf('h1', 'All games currently in progress');
    gamelistdisplayp($mypage, true).
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
} else {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You can\'t access this page, as you are not logged in. Please return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
}
$mypage->finish();

?>