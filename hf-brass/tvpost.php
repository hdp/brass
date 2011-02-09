<?php

$ThreadID = sanitise_int(@$_POST['WhichThread']);
$EscapedContent = sanitise_str_fancy( @$_POST['ThePost'],
                                      6,
                                      50000,
                                      STR_GPC |
                                          STR_PERMIT_FORMATTING |
                                          STR_HANDLE_IMAGES |
                                          STR_PERMIT_ADMIN_HTML
                                      );
$rowC = dbquery( DBQUERY_READ_SINGLEROW,
                 'SELECT "GeneralThread"."Closed", "NonGameThread"."Board", "Game"."GameID", "Game"."TalkRules", "Game"."GameStatus", "Game"."GameIsFinished", "Board"."AdminOnly" FROM "GeneralThread" LEFT JOIN "NonGameThread" ON "GeneralThread"."ThreadID" = "NonGameThread"."Thread" LEFT JOIN "Board" ON "NonGameThread"."Board" = "Board"."BoardID" LEFT JOIN "Game" ON "GeneralThread"."ThreadID" = "Game"."GameID" WHERE "GeneralThread"."ThreadID" = :threadid:',
                 'threadid' , $ThreadID
                 );
if ( $rowC === 'NONE' ) { die($unexpectederrormessage); }
if ( is_null($rowC['GameID']) ) {
    $wheretogo = 'threadview.php?ThreadID='.$ThreadID;
    $wheretosay = 'thread';
} else if ( $rowC['GameStatus'] == 'Recruiting' ) {
    $wheretogo = 'lobby.php?GameID='.$rowC['GameID'];
    $wheretosay = 'lobby page';
} else {
    $wheretogo = 'board.php?GameID='.$rowC['GameID'];
    $wheretosay = 'game';
}

$PostFailureTitle = false;
do {
    if ( $rowC['GameStatus'] === 'Cancelled' ) {
        $PostFailureTitle = 'Game cancelled';
        $PostFailureMessage = 'The game has been cancelled. Please click <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( !$Administrator and $rowC['AdminOnly'] ) {
        $PostFailureTitle = 'Thread is not accessible to users';
        $PostFailureMessage = 'This thread is not currently accessible to normal users. Please click <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( !$_SESSION['LoggedIn'] ) {
        $PostFailureTitle = 'Not logged in';
        $PostFailureMessage = 'You cannot post a message, because you are not logged in. Please click <a href="'.
                              $wheretogo.
                              '">here</a> to return to the '.
                              $wheretosay.
                              ', or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( $Banned ) {
        $PostFailureTitle = 'Banned';
        $PostFailureMessage = 'You cannot post a message, because you are banned. Please click <a href="'.
                              $wheretogo.
                              '">here</a> to return to the '.
                              $wheretosay.
                              ', or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( $EscapedContent[1] == -1 ) {
        $PostFailureTitle = 'Message content too short';
        $PostFailureMessage = 'The message you entered was too short. Please click <a href="'.
                              $wheretogo.
                              '">here</a> to return to the '.
                              $wheretosay.
                              ', or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( $EscapedContent[1] == 1 ) {
        $PostFailureTitle = 'Message content too long';
        $PostFailureMessage = 'The message you entered was too long. The limit is around 50,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before). Please click <a href="'.
                              $wheretogo.
                              '">here</a> to return to the '.
                              $wheretosay.
                              ', or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( $MessagesPosted != @$_POST['MessagesPostedCounter'] ) {
        $PostFailureTitle = 'Check integer mismatch';
        $PostFailureMessage = 'It looks as though you might be inadvertently attempting to post your message twice. (This error message can also be caused by having two windows open and submitting messages in each, one after the other.) If this was not the case, it is suggested to re-navigate to the thread (do not use your "back" button) and reattempt posting. Please click <a href="'.
                              $wheretogo.
                              '">here</a> to return to the '.
                              $wheretosay.
                              ', or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( !$Administrator and
         ( $rowC['Closed'] == 'Closed' or $rowC['Closed'] == 'Forced Closed' )
         ) {
        $PostFailureTitle = 'Thread is closed';
        $PostFailureMessage = 'You cannot post messages in this thread, as it is closed. Please click <a href="'.
                              $wheretogo.
                              '">here</a> to return to the '.
                              $wheretosay.
                              ', or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
    if ( !$Administrator and
         !is_null($rowC['GameID']) and
         $rowC['GameStatus'] != 'Recruiting' and
         $rowC['TalkRules'] != 'No Restrictions' and
         ( $rowC['TalkRules'] != 'No Talk During Game' or
           !$rowC['GameIsFinished']
           ) and
         ( $rowC['TalkRules'] != 'No Talk by Outsiders' or
           dbquery( DBQUERY_READ_RESULTSET,
                    'SELECT "User" FROM "PlayerGameRcd" WHERE "User" = :userid: AND "Game" = :gameid: AND "CurrentOccupant" = 0',
                    'userid' , $_SESSION['MyUserID'] ,
                    'gameid' , $rowC['GameID']
                    ) === 'NONE'
           ) and
         ( $rowC['TalkRules'] != 'No Talk by Outsiders During Game' or
           ( !$rowC['GameIsFinished'] and
             dbquery( DBQUERY_READ_RESULTSET,
                      'SELECT "User" FROM "PlayerGameRcd" WHERE "User" = :userid: AND "Game" = :gameid: AND "CurrentOccupant" = 0',
                      'userid' , $_SESSION['MyUserID'] ,
                      'gameid' , $rowC['GameID']
                      ) === 'NONE'
             )
           )
         ) {
        $PostFailureTitle = 'Cannot post in this thread';
        $PostFailureMessage = 'You are currently unable to post messages in this thread. Please click <a href="'.
                              $wheretogo.
                              '">here</a> to return to the '.
                              $wheretosay.
                              ', or <a href="index.php">here</a> to return to the Main Page. Here is the text of the message you entered:';
        break;
    }
} while ( false );
if ( $PostFailureTitle !== false ) {
    $mypage = page::standard();
    $mypage->title_body($PostFailureTitle);
    $mypage->leaf('p', $PostFailureMessage);
    $mypage->leaf( 'textarea',
                   sanitise_str( @$_POST['ThePost'],
                                 STR_GPC | STR_ESCAPE_HTML
                                 ),
                   'cols=80 rows=20'
                   );
    $mypage->finish();
}

dbquery(DBQUERY_START_TRANSACTION);
dbquery( DBQUERY_WRITE,
         'UPDATE "User" SET "MessagesPosted" = "MessagesPosted" + 1, "LastPostMessage" = UTC_TIMESTAMP() WHERE "UserID" = :userid:',
         'userid' , $_SESSION['MyUserID']
         );
$IDNoM = dbquery( DBQUERY_INSERT_ID,
                  'INSERT INTO "Message" ("PostDate", "User", "MessageText", "Thread") VALUES (UTC_TIMESTAMP(), :userid:, :mt:, :threadid:)',
                  'userid'   , $_SESSION['MyUserID'] ,
                  'threadid' , $ThreadID             ,
                  'mt'       , $EscapedContent[0]
                  );
dbquery( DBQUERY_WRITE,
         'UPDATE "GeneralThread" SET "NumberOfPosts" = "NumberOfPosts" + 1 WHERE "ThreadID" = :threadid:',
         'userid'   , $_SESSION['MyUserID'] ,
         'threadid' , $ThreadID
         );
if ( is_null($rowC['GameID']) ) {
    dbquery( DBQUERY_WRITE,
             'UPDATE "NonGameThread" SET "LastPoster" = :userid:, "LastPost" = UTC_TIMESTAMP() WHERE "Thread" = :thread:',
             'userid' , $_SESSION['MyUserID'] ,
             'thread' , $ThreadID             ,
             'board'  , $rowC['Board']
             );
    dbquery( DBQUERY_WRITE,
             'UPDATE "Board" SET "LastPost" = UTC_TIMESTAMP(), "LastPoster" = :userid:, "LastThread" = :threadid:, "NumMessages" = "NumMessages" + 1 WHERE "BoardID" = :board:',
             'userid'   , $_SESSION['MyUserID'] ,
             'threadid' , $ThreadID             ,
             'board'    , $rowC['Board']
             );
}
dbquery( DBQUERY_WRITE,
         'INSERT INTO "RecentEventLog" ("EventType", "EventTime", "ExtraData") VALUES (\'Message\', UTC_TIMESTAMP(), :idno:)',
         'idno' , $IDNoM
         );
dbquery(DBQUERY_COMMIT);
page::redirect( 3,
                $wheretogo,
                'Message successfully posted.'
                );

?>