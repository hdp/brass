<?php

function DoTask() {
    global $Administrator,$GAME,$unexpectederrormessage;
    $AdminKickList = sanitise_int(@$_POST['AdminKickList']);
    $PostFailureTitle = false;
    do {
        if ( !$Administrator ) {
            $PostFailureTitle = 'Not authorised';
            $PostFailureMessage = 'You are not authorised to make use of this page. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( !@$_POST['CheckC'] ) {
            $PostFailureTitle = 'Tick box left unticked';
            $PostFailureMessage = 'The tick box was left unticked. You need to make sure the box is ticked - this is to prevent accidental use of the administrator controls. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['GameStatus'] != 'In Progress' and
             $GAME['GameStatus'] != 'Recruiting Replacement'
             ) {
            $PostFailureTitle = 'Cannot kick player';
            $PostFailureMessage = 'Players cannot be kicked right now, perhaps because the game has finished. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $AdminKickList < 0 or $AdminKickList >= MAX_PLAYERS ) {
            $PostFailureTitle = 'Invalid input';
            $PostFailureMessage = 'Expected an integer between 0 and '.
                                  ( MAX_PLAYERS - 1 ).
                                  ' inclusive, but received '.
                                  $AdminKickList.
                                  '. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( !$GAME['PlayerExists'][$AdminKickList] or
             $GAME['PlayerMissing'][$AdminKickList]
             ) {
            $PostFailureTitle = 'Seat is empty';
            $PostFailureMessage = 'The chosen seat is empty, or the chosen colour does not exist in this game. Perhaps the player was kicked in the meantime. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['PlayersMissing'] + 1 == $GAME['CurrentPlayers'] ) {
            $PostFailureTitle = 'Only one player is not missing';
            $PostFailureMessage = 'This is the only player who is not missing. If you do not want the game to continue, please select to abort it instead. Click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
    } while ( false );
    if ( $PostFailureTitle !== false ) {
        $mypage = page::standard();
        $mypage->title_body($PostFailureTitle);
        $mypage->leaf('p', $PostFailureMessage);
        $mypage->finish();
    }
    KickPlayer($AdminKickList, 1);
    dbformatgamedata();
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully kicked player.'
                    );
}

?>