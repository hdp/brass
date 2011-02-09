<?php

function DoTask() {
    global $GAME;
    $PostFailureTitle = false;
    do {
        if ( !$_SESSION['LoggedIn'] ) {
            $PostFailureTitle = 'Not logged in';
            $PostFailureMessage = 'You are not logged in. Please log in and then try again. Click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['GameStatus'] == 'Cancelled' ) {
            $PostFailureTitle = 'Game cancelled';
            $PostFailureMessage = 'This game has been cancelled. Please click <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['GameStatus'] != 'Recruiting' ) {
            $PostFailureTitle = 'Game has already started';
            $PostFailureMessage = 'This game has now been started, so you cannot leave it by this method. If you really want to leave, you can quit the game from the board page. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['MyColour'] == 50 ) {
            $PostFailureTitle = 'Not playing in this game';
            $PostFailureMessage = 'You are not currently playing in this game. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $_SESSION['MyUserID'] == $GAME['GameCreator'] ) {
            $PostFailureTitle = 'Cannot leave game';
            $PostFailureMessage = 'You are the creator of this game, so you cannot leave it. Please select to cancel the game instead. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['CurrentPlayers'] == 1 ) {
            $PostFailureTitle = 'Only one player left';
            $PostFailureMessage = 'You are the only player currently in this game, so you cannot leave it. Please contact an Administrator if you want the game to be cancelled. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
    } while ( false );
    if ( $PostFailureTitle !== false ) {
        $mypage = page::standard();
        $mypage->title_body($PostFailureTitle);
        $mypage->leaf('p', $PostFailureMessage);
        $mypage->finish();
    }
    $GAME['PlayerExists'][$GAME['MyColour']] = 0;
    dbquery( DBQUERY_WRITE,
             'UPDATE "Game" SET "CurrentPlayers" = "CurrentPlayers" - 1, "PlayerExists" = :playerexists: WHERE "GameID" = :game:',
             'playerexists' , $GAME['PlayerExists'] ,
             'game'         , $GAME['GameID']
             );
    dbquery( DBQUERY_WRITE,
             'DELETE FROM "PlayerGameRcd" WHERE "Game" = :game: AND "User" = :user:',
             'game' , $GAME['GameID']                          ,
             'user' , $GAME['PlayerUserID'][$GAME['MyColour']]
             );
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'lobby.php?GameID='.$GAME['GameID'],
                    'Successfully withdrew from game.'
                    );
}

?>