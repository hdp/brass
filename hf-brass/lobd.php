<?php

function DoTask() {
    global $Administrator, $cxn, $GAME;
    if ( $Administrator ) {
        $affrows = dbquery( DBQUERY_AFFECTED_ROWS,
                            'UPDATE Game SET GameStatus = \'Cancelled\', LastMove = UTC_TIMESTAMP() WHERE GameID = :game: AND GameStatus = \'Recruiting\'',
                            'game' , $GAME['GameID']
                            );
    } else {
        $affrows = dbquery( DBQUERY_AFFECTED_ROWS,
                            'UPDATE Game SET GameStatus = \'Cancelled\', LastMove = UTC_TIMESTAMP() WHERE GameID = :game: AND GameStatus = \'Recruiting\' AND GameCreator = :user:',
                            'game' , $GAME['GameID']       ,
                            'user' , $_SESSION['MyUserID']
                            );
    }
    $mypage = page::standard();
    if ( $affrows ) {
        $mypage->title_body('Game cancelled');
        $mypage->leaf( 'p',
                       'The game was successfully cancelled. Click <a href="index.php">here</a> to return to the Main Page.'
                       );
    } else {
        $mypage->title_body('Cancellation unsuccessful');
        $mypage->leaf( 'p',
                       'The game was not successfully cancelled. The most likely reason for this is that somebody started it before you submitted the command (but it may have been for another reason). Click <a href="lobby.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to return to the lobby page for the game, or <a href="index.php">here</a> to return to the Main Page.'
                       );
    }
    $mypage->finish();
}

?>