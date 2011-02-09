<?php

function DoTask() {
    global $GAME;
    if ( $GAME['GameStatus'] != 'In Progress' and
         $GAME['GameStatus'] != 'Recruiting Replacement'
         ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot quit game');
        $mypage->leaf( 'p',
                       'You cannot presently quit this game, perhaps because it has finished. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game. You might be seeing this message because you clicked the button twice. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( !@$_POST['CheckA'] or !@$_POST['CheckB'] ) {
        $mypage = page::standard();
        $mypage->title_body('Tick boxes left unticked');
        $mypage->leaf( 'p',
                       'One or both tick boxes were left unticked. You need to make sure both boxes are ticked - this is to prevent accidental use of the "quit" function. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['PlayersMissing'] + 1 == $GAME['CurrentPlayers'] ) {
        abortgame(0);
        dbformatgamedata();
        $mypage = page::standard();
        $mypage->title_body('Game aborted instead');
        $mypage->leaf( 'p',
                       'Since you were the only player left in the game, the game has instead been aborted. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    KickPlayer($GAME['MyColour'], 0);
    dbformatgamedata();
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully quit game.'
                    );
}

?>