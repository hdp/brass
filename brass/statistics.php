<?php
require('_std-include.php');

if ( isset($_GET['Mode']) ) {
    $mode = (int)$_GET['Mode'];
    if ( $mode < 0 or $mode > 9 ) { $mode = 0; }
} else {
    $mode = 0;
}
if ( $mode > 2 ) {
    if ( isset($_GET['Players']) ) {
        $numplayers = (int)$_GET['Players'];
        if ( $numplayers < 2 or $numplayers > 4 ) {
            $mode = 0;
            $numplayers = 0;
        }
        if ( $numplayers == 2 and ( $mode == 5 or $mode == 8 ) ) { $mode++; }
    } else {
        $mode = 0;
        $numplayers = 0;
    }
} else {
    $numplayers = 0;
}
if ( isset($_GET['Board']) ) {
    $board = (int)$_GET['Board'];
    if ( $board < 1 ) { $board = 1; }
} else {
    $board = 1;
}
if ( $mode > 2 and isset($_GET['UserID']) ) {
    $EscapedUserID = (int)$_GET['UserID'];
    if ( $EscapedUserID < 1 ) { $EscapedUserID = 0; }
} else {
    $EscapedUserID = 0;
}
$RequestedUserID = $EscapedUserID;
if ( !$_SESSION['LoggedIn'] ) {
    $EscapedUserID = 0;
    if ( $mode > 3 ) { $mode = 3; }
}
if ( isset($_GET['Page']) ) {
    $Page = (int)$_GET['Page'];
    if ( $Page < 1 ) { $Page = 1; }
} else {
    $Page = 1;
}

$scores_link_number = -1;
function get_scores_link_number () {
    global $scores_link_number;
    $scores_link_number++;
    return $scores_link_number;
}

$equalsarray = array('', '=');
switch ( $mode ) {
    case 0:
        require(HIDDEN_FILES_PATH.'statsa.php');
        break;
    case 1:
        $statsb_parameters = array( 'Most prolific players',
                                    '"NumGamesCompleted", "RankGamesCompleted", "RankGamesCompletedTie"',
                                    '"NumGamesCompleted"',
                                    'Played',
                                    'NumGamesCompleted',
                                    'RankGamesCompleted',
                                    'RankGamesCompletedTie'
                                    );
        require(HIDDEN_FILES_PATH.'statsb.php');
        break;
    case 2:
        $statsb_parameters = array( 'Top-rated players',
                                    '"Rating", "RankRating", "RankRatingTie"',
                                    '"Rating"',
                                    'Rating',
                                    'Rating',
                                    'RankRating',
                                    'RankRatingTie'
                                    );
        require(HIDDEN_FILES_PATH.'statsb.php');
        break;
    case 3:
        require(HIDDEN_FILES_PATH.'statsc.php');
        break;
    default:
        require(HIDDEN_FILES_PATH.'statsd.php');
}

?>