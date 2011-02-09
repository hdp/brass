<?php

function DoTask() {
    global $Administrator, $GAME;
    if ( !$Administrator ) {
        $mypage = page::standard();
        $mypage->title_body('Not authorised');
        $mypage->leaf( 'p',
                       'You are not authorised to make use of this page. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( !@$_POST['CheckD'] ) {
        $mypage = page::standard();
        $mypage->title_body('Tick box left unticked');
        $mypage->leaf( 'p',
                       'The tick box was left unticked. You need to make sure the box is ticked - this is to prevent accidental use of the administrator controls. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['GameStatus'] != 'In Progress' and
         $GAME['GameStatus'] != 'Recruiting Replacement'
         ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot abort game');
        $mypage->leaf( 'p',
                       'This game cannot be aborted just now, perhaps because it has finished. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    abortgame(1);
    dbformatgamedata();
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully aborted game.'
                    );
}

?>