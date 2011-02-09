<?php

function DoTask() {
    global $GAME;
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game, so unfortunately you cannot save notes on it. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $EscapedNotes = sanitise_str_fancy( @$_POST['GameNotes'],
                                        1,
                                        25000,
                                        STR_GPC | STR_ESCAPE_HTML
                                        );
    if ( $EscapedNotes[1] == 1 ) {
        $mypage = page::standard();
        $mypage->title_body('Notes too long');
        $mypage->leaf( 'p',
                       'The notes you entered are too long. The limit is around 25,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before). Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page. Here are the notes you entered:'
                       );
        $mypage->leaf( 'textarea',
                       $EscapedNotes[0],
                       'cols=80 rows=20'
                       );
        $mypage->finish();
    }
    if ( $EscapedNotes[1] == -1 ) {
        dbquery( DBQUERY_WRITE,
                 'DELETE FROM "PlayerGameNotes" WHERE "Game" = :game: AND "User" = :user:',
                 'game' , $GAME['GameID']                          ,
                 'user' , $GAME['PlayerUserID'][$GAME['MyColour']]
                 );
    } else {
        dbquery( DBQUERY_WRITE,
                 'REPLACE INTO "PlayerGameNotes" ("Game", "User", "Notes") VALUES (:game:, :user:, :notes:)',
                 'game'         , $GAME['GameID']                          ,
                 'user'         , $GAME['PlayerUserID'][$GAME['MyColour']] ,
                 'notes'        , $EscapedNotes[0]
                 );
    }
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully saved notes.'
                    );
}

?>