<?php
require('_std-include.php');

$mypage = page::standard();
if ( !isset($_GET['GameID']) ) {
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$EscapedGameID = (int)$_GET['GameID'];
require(HIDDEN_FILES_PATH.'gamegetdata_lobby.php');
$GAME = gamegetdata_lobby($EscapedGameID, true);
if ( $GAME === false ) {
    $mypage->title_body('Game doesn\'t exist');
    $mypage->leaf( 'p',
                   'There is no game with that ID number. Perhaps the game was cancelled in the meantime. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
} else if ( $GAME == 'WRONG PAGE' ) {
    $mypage->title_body('Game has started');
    $mypage->leaf( 'p',
                   'That game has started already. Perhaps someone else started it in the meantime, or perhaps you inadvertently loaded this page twice. Click <a href="board.php?GameID='.
                       $EscapedGameID.
                       '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( $_GET['NumPlayers'] != $GAME['CurrentPlayers'] ) {
    $mypage->title_body('Number of players has changed');
    $mypage->leaf( 'p',
                   'The number of players in the game has changed since you loaded the lobby page. As a precaution, you must review the lobby page to make sure you really do want to start the game with the current number of players.'
                   );
    $mypage->leaf( 'p',
                   'Please click <a href="lobby.php?GameID='.
                       $GAME['GameID'].
                       '">here</a> to return to the lobby page.'
                   );
    $mypage->finish();
}

if ( $Administrator ) {
    if ( $GAME['CurrentPlayers'] < $GAME['MinimumPlayersAllowed'] ) {
        $mypage->title_body('Too few players');
        $mypage->leaf( 'p',
                       'This game has fewer players than the minimum for this game version, so it cannot be started.'
                       );
        $mypage->leaf( 'p',
                       'Please click <a href="lobby.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to return to the lobby page.'
                       );
        $mypage->finish();
    } else if ( $GAME['CurrentPlayers'] > $GAME['MaximumPlayers'] ) {
        $mypage->title_body('Too many players');
        $mypage->leaf( 'p',
                       'This game has more than the maximum number of players, so it cannot be started.'
                       );
        $mypage->leaf( 'p',
                       'Please click <a href="lobby.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to return to the lobby page.'
                       );
        $mypage->finish();
    }
} else if ( $_SESSION['LoggedIn'] ) {
    if ( $_SESSION['MyUserID'] == $GAME['GameCreator'] or
         ( $GAME['AnyPlayerStarts'] and $GAME['MyColour'] != 50 )
         ) {
        if ( $GAME['CurrentPlayers'] < $GAME['MinimumPlayers'] ) {
            $mypage->title_body('Too few players');
            $mypage->leaf( 'p',
                           'This game has fewer than the minimum number of players, so it cannot be started.'
                           );
            $mypage->leaf( 'p',
                           'Please click <a href="lobby.php?GameID='.
                               $GAME['GameID'].
                               '">here</a> to return to the lobby page.'
                           );
            $mypage->finish();
        } else if ( $GAME['CurrentPlayers'] > $GAME['MaximumPlayers'] ) {
            $mypage->title_body('Too many players');
            $mypage->leaf( 'p',
                           'This game has more than the maximum number of players, so it cannot be started.'
                           );
            $mypage->leaf( 'p',
                           'Please click <a href="lobby.php?GameID='.
                               $GAME['GameID'].
                               '">here</a> to return to the lobby page.'
                           );
            $mypage->finish();
        }
    } else {
        $mypage->title_body('Not your game');
        $mypage->leaf( 'p',
                       'You can\'t start this game, as you didn\'t create it.'
                       );
        $mypage->leaf( 'p',
                       'Please click <a href="lobby.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to return to the lobby page.'
                       );
        $mypage->finish();
    }
} else {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in, and then try again.'
                   );
    $mypage->leaf( 'p',
                   'Click <a href="lobby.php?GameID='.
                       $GAME['GameID'].
                       '">here</a> to return to the lobby page, or <a href="index.php">here</a> to visit the Main Page.'
                   );
    $mypage->finish();
}

require(HIDDEN_FILES_PATH.'sgresource.php');
startgame(false);
page::redirect( 3,
                'board.php?GameID='.$GAME['GameID'],
                'Game started successfully.'
                );

?>