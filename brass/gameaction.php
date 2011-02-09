<?php
require('_std-include.php');
get_translation_module(5);

if ( !$_SESSION['LoggedIn'] ) {
    $mypage = page::standard();
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then try again. Click <a href="board.php?GameID='.
                       $GAME['GameID'].
                       '">here</a> to return to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
$SystemActing = true;
$PersonActing = -50;
if ( !isset($_POST['GameID']) or
     !isset($_POST['FormSubmit'])
     ) {
    die($unexpectederrormessage);
}
$EscapedGameID = sanitise_int($_POST['GameID']);
require(HIDDEN_FILES_PATH.'gamegetdata_board_do.php');

switch ( $_POST['FormSubmit'] ) {
    case 'Admin Retitle':
        require(HIDDEN_FILES_PATH.'gaa.php');
        break;
    case 'Extend Clock':
        require(HIDDEN_FILES_PATH.'gab.php');
        break;
    case 'Admin Abort':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gac.php');
        break;
    case 'Admin Kick':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gad.php');
        break;
    case 'Vote To Abort':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gae.php');
        break;
    case 'Vote On Aborting':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gaf.php');
        break;
    case 'Vote To Kick':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gag.php');
        break;
    case 'Vote On Kicking':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gah.php');
        break;
    case 'Vote On Downsizing':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gai.php');
        break;
    case 'Join as Replacement':
        require(HIDDEN_FILES_PATH.'gaj.php');
        break;
    case 'Withdraw Request':
        require(HIDDEN_FILES_PATH.'gak.php');
        break;
    case 'Accept Replacement':
        require(HIDDEN_FILES_PATH.'gal.php');
        break;
    case 'Quit':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gan.php');
        break;
    case 'Save notes':
        require(HIDDEN_FILES_PATH.'sanitise_str_fancy.php');
        require(HIDDEN_FILES_PATH.'gao.php');
        break;
    case 'Swap Cards':
        require(HIDDEN_FILES_PATH.'gap.php');
        break;
    case 'Swap These Cards':
        require(HIDDEN_FILES_PATH.'gaq.php');
        break;
    case 'Sort Cards':
        require(HIDDEN_FILES_PATH.'gar.php');
        break;
    case 'Submit Move':
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'gas.php');
        break;
    case 'Add Comment':
        require(HIDDEN_FILES_PATH.'gat.php');
        break;
    default:
        $mypage = page::standard();
        $mypage->title_body('Error');
        $mypage->leaf( 'p',
                       'It looks as though you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
}

$GAME = gamegetdata_board_do($EscapedGameID);
if ( $GAME === false ) {
    $mypage = page::standard();
    $mypage->title_body('Cannot find game');
    $mypage->leaf( 'p',
                   'Cannot find a game with that game ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
if ( $GAME == 'WRONG PAGE' ) {
    $mypage = page::standard();
    $mypage->title_body('Game has not yet started');
    $mypage->leaf( 'p',
                   'This game has not yet started. Please click <a href="lobby.php?GameID='.
                       $EscapedGameID.
                       '">here</a> to go to the lobby page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
if ( $GAME == 'FINISHED' ) {
    $mypage = page::standard();
    $mypage->title_body('Game has finished');
    $mypage->leaf( 'p',
                   'This game has finished. Please click <a href="board.php?GameID='.
                       $EscapedGameID.
                       '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

DoTask();

?>