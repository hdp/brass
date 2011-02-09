<?php
require('_std-include.php');

if ( @$_SESSION['LoggedIn'] ) {
    if ( isset($_GET['GameID']) ) {
        $EscapedGameID = (int)$_GET['GameID'];
        $QR = dbquery( DBQUERY_READ_SINGLEROW,
                       'SELECT "GameStatus" FROM "Game" WHERE "GameID" = :game:',
                       'game' , $EscapedGameID
                       );
        if ( $QR === 'NONE' ) {
            $mypage = page::standard();
            $mypage->title_body('Game doesn\'t exist');
            $mypage->leaf( 'p',
                           'Couldn\'t find a game with that ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                           );
        } else if ( $QR['GameStatus'] == 'Recruiting' or
                    $QR['GameStatus'] == 'Cancelled'
                    ) {
            $mypage = page::standard();
            $mypage->title_body('Cannot add game');
            $mypage->leaf( 'p',
                           'You cannot watch a game that has not yet started, or that has been cancelled. Please return to the <a href="index.php">Main Page</a>.'
                           );
        } else {
            if ( @$_GET['Add'] ) {
                dbquery( DBQUERY_WRITE,
                         'INSERT IGNORE INTO "WatchedGame" ("User", "Game") VALUES (:user:, :game:)',
                         'user' , $_SESSION['MyUserID'] ,
                         'game' , $EscapedGameID
                         );
                $blurb = 'Succesfully added game.';
            } else {
                dbquery( DBQUERY_WRITE,
                         'DELETE FROM "WatchedGame" WHERE "User" = :user: AND "Game" = :game:',
                         'user' , $_SESSION['MyUserID'] ,
                         'game' , $EscapedGameID
                         );
                $blurb = 'Successfully removed game.';
            }
            if ( isset($_GET['ReturnWhere']) ) {
                switch ( $_GET['ReturnWhere'] ) {
                    case 0:
                        $Destination = 'userdetails.php?UserID='.$_SESSION['MyUserID'];
                        break;
                    case 1:
                        $Destination = 'usersgames.php?UserID='.$_SESSION['MyUserID'];
                        break;
                    default:
                        $Destination = 'board.php?GameID='.$EscapedGameID;
                }
            } else {
                $Destination = 'board.php?GameID='.$EscapedGameID;
            }
            page::redirect(3, $Destination, $blurb);
        }
    } else {
        $mypage = page::standard();
        $mypage->title_body('Error');
        $mypage->leaf( 'p',
                       'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
    }
} else if ( isset($_GET['GameID']) and @$_GET['ReturnWhere'] == 2 ) {
    $EscapedGameID = (int)$_GET['GameID'];
    $mypage = page::standard();
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You must be logged in in order to add or remove watched games. Click <a href="board.php?GameID='.
                       $EscapedGameID.
                       '">here</a> to return to your previous location, or <a href="index.php">here</a> to return to the Main Page.'
                   );
} else {
    $mypage = page::standard();
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You must be logged in in order to add or remove watched games. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
}
$mypage->finish();

?>