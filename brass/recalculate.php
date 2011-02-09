<?php
require('_std-include.php');

$mypage = page::standard();
if ( $Administrator and @$_GET['DoAll'] ) {
    dbquery(DBQUERY_WRITE, 'CALL "CalculateAllRatings"()');
    dbquery(DBQUERY_WRITE, 'CALL "CalculatePlayerStats"()');
    dbquery(DBQUERY_WRITE, 'CALL "CalculateRanks_Rating"()');
    dbquery(DBQUERY_WRITE, 'CALL "CalculateRanks_GamesCompleted"()');
    dbquery(DBQUERY_WRITE, 'CALL "Maintenance_RefreshMetadata"()');
    $mypage->title_body('Recalculation successful');
    $mypage->leaf( 'p',
                   'Recalculation of player ratings, player statistics, rankings and site statistics was successful. Click <a href="index.php">here</a> to visit the Main Page.'
                   );
} else if ( $_SESSION['LoggedIn'] ) {
    dbquery( DBQUERY_WRITE,
             'CALL "CalculateRating"(:user:)',
             'user' , (int)$_SESSION['MyUserID']
             );
    dbquery(DBQUERY_WRITE, 'CALL "CalculateRanks_Rating"()');
    $mypage->title_body('Recalculation successful');
    $mypage->leaf( 'p',
                   'Recalculation of your rating was successful. Click <a href="userdetails.php?UserID='.
                       $UserID.
                       '">here</a> to return to your User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
} else {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You need to log in to use this page. Click <a href="index.php">here</a> to visit the Main Page.'
                   );
}
$mypage->finish();

?>