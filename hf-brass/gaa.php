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
    dbquery( DBQUERY_WRITE,
             'UPDATE "Game" SET "GTitleDeletedByAdmin" = 1 - "GTitleDeletedByAdmin" WHERE "GameID" = :game:',
             'game' , $GAME['GameID']
             );
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully modified title.'
                    );
}

?>