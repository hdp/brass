<?php

function DoTask() {
    global $GAME;
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    sort($GAME['Cards'][$GAME['MyColour']]);
    dbformatgamedata();
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully sorted cards.'
                    );
}

?>