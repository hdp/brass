<?php

function DoTask() {
    global $Administrator, $GAME;
    $ColourToRemove = $_POST['FormActionID'] - 10;
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
            $PostFailureMessage = 'This game has been started, so you cannot remove players from it using this method. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['GameCreator'] == $GAME['PlayerUserID'][$ColourToRemove] ) {
            $PostFailureTitle = 'Cannot remove game creator';
            $PostFailureMessage = 'You cannot remove the creator of the game. You may want to cancel the game instead. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['CurrentPlayers'] == 1 ) {
            $PostFailureTitle = 'Only one player left';
            $PostFailureMessage = 'This game has only one player left, so you cannot remove players from it. You may want to cancel the game instead. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( !$Administrator and
             $_SESSION['MyUserID'] != $GAME['GameCreator']
             ) {
            $PostFailureTitle = 'Cannot remove player';
            $PostFailureMessage = 'You cannot remove players from this game, as it was not created by you. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( !$GAME['PlayerExists'][$ColourToRemove] ) {
            $PostFailureTitle = 'Player doesn\'t exist';
            $PostFailureMessage = 'There is no player currently waiting to play as that colour. Perhaps something happened after you loaded the game page. Please click <a href="lobby.php?GameID='.
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
    $GAME['PlayerExists'][$ColourToRemove] = 0;
    dbquery( DBQUERY_WRITE,
             'DELETE FROM "PlayerGameRcd" WHERE "Game" = :game: AND "User" = :user:',
             'game' , $GAME['GameID']                        ,
             'user' , $GAME['PlayerUserID'][$ColourToRemove]
             );
    dbquery( DBQUERY_WRITE,
             'UPDATE "Game" SET "CurrentPlayers" = "CurrentPlayers" - 1, "PlayerExists" = :playerexists: WHERE "GameID" = :game:',
             'playerexists' , $GAME['PlayerExists'] ,
             'game'         , $GAME['GameID']
             );
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'lobby.php?GameID='.$GAME['GameID'],
                    'Successfully removed player.'
                    );
}

?>