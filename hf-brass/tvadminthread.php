<?php

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then return to the thread. You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}
if ( !$Administrator ) {
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to take this action. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$ThreadID    = sanitise_int(@$_POST['WhichThread']);
$NewLocation = sanitise_int(@$_POST['NewLocation']);
if      ( @$_POST['Closedness'] == 'FC' ) { $ClosedString = 'Forced Closed'; }
else if ( @$_POST['Closedness'] ==  'C' ) { $ClosedString = 'Closed';        }
else if ( @$_POST['Closedness'] == 'FO' ) { $ClosedString = 'Forced Open';   }
else                                      { $ClosedString = 'Open';          }
if ( @$_POST['ThreadSticky'] ) { $SSticky = 1; }
else                           { $SSticky = 0; }
if ( @$_POST['TitleDeleted'] ) { $STitDel = 1; }
else                           { $STitDel = 0; }
$row = dbquery( DBQUERY_READ_SINGLEROW,
                'SELECT "GeneralThread"."NumberOfPosts", "GeneralThread"."Closed", "NonGameThread"."Board", "Game"."GameID", "Game"."GameStatus" FROM "GeneralThread" LEFT JOIN "NonGameThread" ON "GeneralThread"."ThreadID" = "NonGameThread"."Thread" LEFT JOIN "Game" ON "GeneralThread"."ThreadID" = "Game"."GameID" WHERE "GeneralThread"."ThreadID" = :thread:',
                'thread' , $ThreadID
                );
if ( $row === 'NONE' ) { die($unexpectederrormessage); }
if ( $row['Closed'] != $ClosedString ) {
    dbquery( DBQUERY_WRITE,
             'UPDATE "GeneralThread" SET "Closed" = :closed: WHERE "ThreadID" = :thread:',
             'closed' , $ClosedString ,
             'thread' , $ThreadID
             );
}

if ( is_null($row['Board']) ) {
    if ( $row['GameStatus'] == 'Cancelled' or
         $row['GameStatus'] == 'Recruiting'
         ) {
        $wheretogo = 'lobby.php?GameID='.$row['GameID'];
    } else {
        $wheretogo = 'board.php?GameID='.$row['GameID'];
    }
} else {
    if ( $row['Board'] != $NewLocation ) {
        dbquery( DBQUERY_WRITE,
                 'UPDATE "Board" SET "NumThreads" = "NumThreads" + 1, "NumMessages" = "NumMessages" + :numposts: WHERE "BoardID" = :board:',
                 'numposts' , $row['NumberOfPosts'] ,
                 'board'    , $NewLocation
                 );
        dbquery( DBQUERY_WRITE,
                 'UPDATE "Board" SET "NumThreads" = "NumThreads" - 1, "NumMessages" = GREATEST("NumMessages" - :numposts:, 0) WHERE "BoardID" = :board:',
                 'numposts' , $row['NumberOfPosts'] ,
                 'board'    , $row[Board]
                 );
    }
    $QueryResult = dbquery( DBQUERY_WRITE,
                            'UPDATE "NonGameThread" SET "Board" = :newlocation:, "Sticky" = :sticky:, "TitleDeletedByAdmin" = :titledeleted: WHERE "Thread" = :thread:',
                            'newlocation'  , $NewLocation ,
                            'sticky'       , $SSticky     ,
                            'titledeleted' , $STitDel     ,
                            'thread'       , $ThreadID
                            );
    $wheretogo = 'threadview.php?ThreadID='.$ThreadID;
}
page::redirect( 3,
                $wheretogo,
                'Successfully modified thread.'
                );

?>