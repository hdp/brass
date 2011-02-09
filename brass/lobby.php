<?php
require('_std-include.php');

if ( isset($_POST['GameID']) ) {
    $EscapedGameID = (int)$_POST['GameID'];
} else if ( isset($_GET['GameID']) ) {
    $EscapedGameID = (int)$_GET['GameID'];
} else {
    $mypage = page::standard();
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks as though you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$DoingWork = false;
if ( isset($_POST['FormActionID']) ) {
    $_POST['FormActionID'] = (int)$_POST['FormActionID'];
    if ( $_POST['FormActionID'] > 9 and
         $_POST['FormActionID'] < 15
         ) {
        // Remove player
        require(HIDDEN_FILES_PATH.'loba.php');
        $DoingWork = true;
    } else if ( $_POST['FormActionID'] > 19 and
                $_POST['FormActionID'] < 25
                ) {
        // Join as specific colour
        require(HIDDEN_FILES_PATH.'lobb.php');
        $DoingWork = true;
    } else {
        switch ( $_POST['FormActionID'] ) {
            case 28:
                // Join as first available colour
                require(HIDDEN_FILES_PATH.'lobc.php');
                $DoingWork = true;
                break;
            case 30:
                // Cancel game
                require(HIDDEN_FILES_PATH.'lobd.php');
                break;
            case 31:
                // Leave game
                require(HIDDEN_FILES_PATH.'lobe.php');
                $DoingWork = true;
                break;
            case 32:
                // Change private / password details
                require(HIDDEN_FILES_PATH.'lobf.php');
                break;
            case 33:
                // Admin clear title
                require(HIDDEN_FILES_PATH.'lobg.php');
                break;
            case 34:
                // Admin unclear title
                require(HIDDEN_FILES_PATH.'lobh.php');
                break;
            default:
                die($unexpectederrormessage);
        }
    }
} else {
    require(HIDDEN_FILES_PATH.'lobi.php');
}

require(HIDDEN_FILES_PATH.'gamegetdata_lobby.php');
$GAME = gamegetdata_lobby($EscapedGameID, $DoingWork);
if ( $GAME === false ) {
    $mypage = page::standard();
    $mypage->title_body('Cannot find game');
    $mypage->leaf( 'p',
                   'Cannot find a game with that game ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
} else if ( $GAME === 'WRONG PAGE' ) {
    $mypage = page::standard();
    $mypage->title_body('Game has started');
    $mypage->leaf( 'p',
                   'This game has now started. Please click <a href="board.php?GameID='.
                       $EscapedGameID.
                       '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

DoTask();

?>