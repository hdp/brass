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
    $thenumber = sanitise_int(@$_POST['thenumber']);
    if ( $thenumber < -9 or
         $thenumber > 99 or
         $thenumber == 0
         ) {
        $mypage = page::standard();
        $mypage->title_body('Invalid input');
        $mypage->leaf( 'p',
                       'Expected a nonzero integer between -9 and 99 inclusive, but received '.
                           $thenumber.
                           '. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $interval = sanitise_enum( @$_POST['theinterval'],
                               array('MINUTE', 'HOUR', 'DAY')
                               );
    if ( @$_POST['whattime'] == 'now' ) { $whattime = 'UTC_TIMESTAMP()'; }
    else                                { $whattime = '"LastMove"';      }
    $time_expr = 'TIMESTAMPADD('.
                 $interval.
                 ', :thenumber:, '.
                 $whattime.
                 ')';
    dbquery( DBQUERY_WRITE,
             'UPDATE "Game" JOIN "GameInProgress" ON "Game"."GameID" = "GameInProgress"."Game" SET "Game"."LastMove" = '.
                 $time_expr.
                 ', "GameInProgress"."GIPLastMove" = '.
                 $time_expr.
                 ' "Game"."GameTicker" = CONCAT("Game"."GameTicker", :tickerconcat:), "Game"."GameTickerNames" = CONCAT("Game"."GameTickerNames", :namesconcat:) WHERE "GameID" = :game:',
             'thenumber'    , $thenumber ,
             'tickerconcat' , '3A'.
                              callmovetimediff().
                              letter_end_number($_SESSION['MyUserID']).
                              letter_end_number($_SESSION['MyGenderCode']) ,
             'namesconcat'  , '|'.$_SESSION['MyUserName'] ,
             'game'         , $GAME['GameID']
             );
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully altered clock.'
                    );
}

?>