<?php

function DoTask() {
    global $Administrator, $GAME, $unexpectederrormessage;
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
    $EscapedComment = sanitise_str( @$_POST['AdminComment'],
                                    STR_GPC | STR_STRIP_TAB_AND_NEWLINE
                                    );
    $EscapedComment = str_replace( array('|', '"'),
                                   array('' , '' ),
                                   $EscapedComment
                                   );
    $EscapedComment = htmlspecialchars($EscapedComment);
    if ( $EscapedComment == '' ) {
        $mypage = page::standard();
        $mypage->title_body('Comment is missing');
        $mypage->leaf( 'p',
                       'The coment you entered is missing. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    dbquery( DBQUERY_WRITE,
             'UPDATE "Game" SET "GameTicker" = CONCAT("GameTicker", :tickerconcat:), "GameTickerNames" = CONCAT("GameTickerNames", :namesconcat:) WHERE "GameID" = :game:',
             'tickerconcat' , '3B'.
                              callmovetimediff().
                              letter_end_number($_SESSION['MyUserID']).
                              letter_end_number($_SESSION['MyGenderCode']) ,
             'namesconcat'  , '|'.$_SESSION['MyUserName'].
                                  '|'.$EscapedComment ,
             'game'         , $GAME['GameID']
             );
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully added comment.'
                    );
}

?>