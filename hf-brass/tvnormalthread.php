<?php

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then return to the thread. You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}

$ThreadID = sanitise_int(@$_POST['WhichThread']);
$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'SELECT * FROM "GeneralThread" LEFT JOIN "NonGameThread" ON "GeneralThread"."ThreadID" = "NonGameThread"."Thread" WHERE "ThreadID" = :thread:',
               'thread' , $ThreadID
               );
if ( $QR === 'NONE' ) {
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks like you\'ve been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( is_null($QR['Board']) ) {
    $rowB = dbquery( DBQUERY_READ_SINGLEROW,
                     'SELECT "GameID", "GameStatus" FROM "Game" WHERE "GameID" = :thread:',
                     'thread' , $ThreadID
                     );
    if ( $rowB === 'NONE' or $rowB['GameStatus'] == 'Cancelled' ) {
        die($unexpectederrormessage);
    } else if ( $rowB['GameStatus'] == 'Recruiting' ) {
        $wheretogo = 'lobby.php?GameID='.$rowB['GameID'];
        $wheretosay = 'lobby page';
    } else {
        $wheretogo = 'board.php?GameID='.$rowB['GameID'];
        $wheretosay = 'board page';
    }
    $mypage->title_body('Cannot open or close this thread');
    $mypage->leaf( 'p',
                   'You cannot open or close this thread, as it is the thread associated with a game. Please click <a href="'.
                       $wheretogo.
                       '">here</a> to return to the '.
                       $wheretosay.
                       ', or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( $QR['OriginalPoster'] != $_SESSION['MyUserID'] ) {
    $mypage->title_body('Cannot alter thread');
    $mypage->leaf( 'p',
                   'This thread was not created by you, so you are not able to modify it'.
                       ( ( $Administrator ) ?
                         ' in this way - use the special Administrator tools instead' :
                         ''
                         ).
                       '. Please click <a href="threadview.php?ThreadID='.
                       $ThreadID.
                       '">here</a> to return to the thread, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( !$Administrator and
     ( $QR['Closed'] == 'Forced Closed' or $QR['Closed'] == 'Forced Open' )
     ) {
    $mypage->title_body('Cannot alter thread');
    $mypage->leaf( 'p',
                   'An Administrator has decided that this thread should remain'.
                       ( ( $QR['Closed'] == 'Forced Closed' ) ?
                         'closed' :
                         'open'
                         ).
                       '. You may not alter the thread setting, even if the thread was created by you. Please click <a href="threadview.php?ThreadID='.
                       $ThreadID.
                       '">here</a> to return to the thread, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( @$_POST['ThreadClosed'] ) { $ClosedVal = 'Closed'; }
else                           { $ClosedVal = 'Open';   }
$QueryResult = dbquery( DBQUERY_WRITE,
                        'UPDATE "GeneralThread" SET "Closed" = :closed: WHERE "ThreadID" = :thread:',
                        'closed' , $ClosedVal ,
                        'thread' , $ThreadID
                        );
page::redirect( 3,
                $wheretogo,
                'Successfully modified thread.'
                );

?>