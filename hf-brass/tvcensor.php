<?php

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You must be logged in in order to do this. Please log in and then return to the page containing the message. You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}

$Message  = sanitise_int(@$_GET['TheMessage']);
$ThreadID = sanitise_int(@$_GET['ThreadID']  );
if ( @$_GET['AsAdmin'] ) {
    if ( $Administrator ) {
        $QueryResult = dbquery( DBQUERY_WRITE,
                                'UPDATE "Message" SET "DeletedByAdmin" = 1 - "DeletedByAdmin" WHERE "MessageID" = :message:',
                                'message' , $Message
                                );
    } else {
        $mypage->title_body('Not authorised');
        $mypage->leaf( 'p',
                       'You are not authorised to take this action. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
} else {
    $QueryResult = dbquery( DBQUERY_WRITE,
                            'UPDATE "Message" SET "DeletedByUser" = 1 - "DeletedByUser" WHERE "MessageID" = :message: AND "User" = :user:',
                            'message' , $Message              ,
                            'user'    , $_SESSION['MyUserID']
                            );
}

$row = dbquery( DBQUERY_READ_SINGLEROW,
                'SELECT "GameID", "GameStatus" FROM "Game" WHERE "GameID" = :thread:',
                'thread' , $ThreadID
                );
if ( $row === 'NONE' ) {
    $wheretogo = 'threadview.php?ThreadID='.$ThreadID;
} else if ( $row['GameStatus'] == 'Cancelled' ) {
    $mypage->title_body('Game cancelled');
    $mypage->leaf( 'p',
                   'This game has been cancelled. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
} else if ( $row['GameStatus'] == 'Recruiting' ) {
    $wheretogo = 'lobby.php?GameID='.$row['GameID'];
} else {
    $wheretogo = 'board.php?GameID='.$row['GameID'];
}
page::redirect( 3,
                $wheretogo,
                'Successfully modified message.'
                );

?>