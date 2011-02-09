<?php

function DoTask() {
    global $GAME;
    if ( ( $GAME['GameStatus'] != 'In Progress' and
           $GAME['GameStatus'] != 'Recruiting Replacement'
           ) or
         !$GAME['PlayersMissingThatMatter']
         ) {
        $mypage = page::standard();
        $mypage->title_body('No replacements needed');
        $mypage->leaf( 'p',
                       'No replacement players are needed for this game at the moment. (Perhaps something happened after you loaded the board page.) Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $mycol = dbquery( DBQUERY_READ_SINGLEROW,
                      'SELECT "Colour" FROM "ReplacementOffer" WHERE "Game" = :game: AND "User" = :user:',
                      'game' , $GAME['GameID']       ,
                      'user' , $_SESSION['MyUserID']
                      );
    if ( $mycol === 'NONE' ) {
        $mypage = page::standard();
        $mypage->title_body('Not currently a candidate replacement');
        $mypage->leaf( 'p',
                       'You are not currently a candidate replacement. You might be seeing this message because you clicked the button twice. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    dbquery( DBQUERY_WRITE,
             'DELETE FROM "ReplacementOffer" WHERE "Game" = :game: AND "User" = :user:',
             'game' , $GAME['GameID']       ,
             'user' , $_SESSION['MyUserID']
             );
    if ( $mycol['Colour'] == $GAME['PlayerToMove'] ) {
        $GAME['AltGameTicker'] .= '8F'.
                                  callmovetimediff().
                                  letter_end_number($_SESSION['MyUserID']).
                                  letter_end_number($_SESSION['MyGenderCode']);
        $GAME['GameTickerNames'] .= '|'.$_SESSION['MyUserName'];
        dbformatgamedata();
    } else {
        dbquery(DBQUERY_COMMIT);
    }
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully withdrew request.'
                    );
}

?>