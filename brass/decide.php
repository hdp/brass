<?php

require('_std-include.php');
if ( isset($_GET['GameID']) ) {
    $gameid = (int)$_GET['GameID'];
    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "GameStatus" FROM "Game" WHERE "GameID" = :game:',
                   'game' , $gameid
                   );
    if ( $QR === 'NONE' ) { die('Invalid game ID.'); }
    switch ( $QR['GameStatus'] ) {
        case 'Cancelled':
            if ( $Administrator ) {
                header('Location: '.SITE_ADDRESS.'lobby.php?GameID='.$gameid);
            } else {
                die('Invalid game ID.');
            }
        break;
        case 'Recruiting':
            header('Location: '.SITE_ADDRESS.'lobby.php?GameID='.$gameid);
        break;
        default:
            header('Location: '.SITE_ADDRESS.'board.php?GameID='.$gameid);
    }
} else {
    die('No game ID supplied!');
}

?>