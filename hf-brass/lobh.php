<?php

function DoTask() {
    global $Administrator, $GAME;
    if ( !$_SESSION['LoggedIn'] ) {
        $mypage = page::standard();
        $mypage->title_body('Not logged in');
        $mypage->leaf( 'p',
                       'You are not logged in. Please log in and then try again. Click <a href="lobby.php?GameID='.
                            $GAME['GameID'].
                            '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.'
                            );
        $mypage->finish();
    }
    if ( !$Administrator ) {
        $mypage = page::standard();
        $mypage->title_body('Not authorised');
        $mypage->leaf( 'p',
                       'You are not authorised to make use of this page. Please click <a href="lobby.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    dbquery( DBQUERY_WRITE,
             'UPDATE "Game" SET "GTitleDeletedByAdmin" = 0 WHERE "GameID" = :game:',
             'game' , $GAME['GameID']
             );
    page::redirect( 3,
                    'lobby.php?GameID='.$GAME['GameID'],
                    'Successfully modified title.'
                    );
}

?>