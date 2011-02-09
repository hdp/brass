<?php

$BoardID = sanitise_int(@$_POST['WhichBoard']);
$EscapedTitle = sanitise_str_fancy( @$_POST['ThreadTitle'],
                                    6,
                                    80,
                                    STR_GPC |
                                        STR_ESCAPE_HTML |
                                        STR_STRIP_TAB_AND_NEWLINE |
                                        STR_CONVERT_ESCAPE_SEQUENCES
                                    );
$EscapedContent = sanitise_str_fancy( @$_POST['FirstPost'],
                                      6,
                                      50000,
                                      STR_GPC |
                                          STR_PERMIT_FORMATTING |
                                          STR_HANDLE_IMAGES |
                                          STR_PERMIT_ADMIN_HTML
                                      );
if ( $Administrator and @$_POST['PostSticky'] ) { $StickyIt = 1; }
else                                            { $StickyIt = 0; }
if ( @$_POST['PostClosed'] ) { $CloseIt = 'Closed'; }
else                         { $CloseIt = 'Open';   }
$row = dbquery( DBQUERY_READ_INTEGER_TOLERANT_NONE,
                'SELECT "Closed" FROM "Board" WHERE "BoardID" = :boardid:',
                'boardid' , $BoardID
                );

$PostFailureTitle = false;
do {
    if ( !$_SESSION['LoggedIn'] ) {
        $PostFailureTitle = 'Not logged in';
        $PostFailureMessage = 'You must be <a href="login.php">logged in</a> in order to post a thread. You can return to the board page by clicking <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">here</a>. Here is the text of your message:';
        break;
    }
    if ( $Banned ) {
        $PostFailureTitle = 'Banned';
        $PostFailureMessage = 'Banned users cannot post threads. You can return to the board page by clicking <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">here</a>. Here is the text of your message:';
        break;
    }
    if ( $EscapedTitle[1] == -1 ) {
        $PostFailureTitle = 'Title Too Short';
        $PostFailureMessage = 'The thread title you entered was too short. You can return to the board page by clicking <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">here</a>. Here is the text of your message:';
        break;
    }
    if ( $EscapedTitle[1] == 1 ) {
        $PostFailureTitle = 'Title Too Long';
        $PostFailureMessage = 'The thread title you entered was too long. Please try a shorter version. You can return to the board page by clicking <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">here</a>. Here is the text of your message:';
        break;
    }
    if ( $EscapedContent[1] == -1 ) {
        $PostFailureTitle = 'Message Too Short';
        $PostFailureMessage = 'The message you entered was too short. You can return to the board page by clicking <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">here</a>. Here is the text of your message:';
        break;
    }
    if ( $EscapedContent[1] == 1 ) {
        $PostFailureTitle = 'Message Too Long';
        $PostFailureMessage = 'The message you entered was too long. The limit is 50,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before). You can return to the board page by clicking <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">here</a>. Here is the text of your message:';
        break;
    }
    if ( $MessagesPosted != $_POST['MessagesPostedCounter'] ) {
        $PostFailureTitle = 'Check Integer Mismatch';
        $PostFailureMessage = 'It looks like you might be inadvertently attempting to double-post. Please return to the <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">board page</a>. If this was not the case, it is suggested to re-navigate to the board (do not use your "back" button) and reattempt posting. Here is the text of your message:';
        break;
    }
    if ( $row === 'NONE' ) {
        $PostFailureTitle = 'Error';
        $PostFailureMessage = 'The specified board does not exist. Please return to the <a href="index.php">Main Page</a>. Here is the text of your message:';
        break;
    } else if ( $row['Closed'] and !$Administrator ) {
        $PostFailureTitle = 'Board Closed';
        $PostFailureMessage = 'You cannot post threads to this board - it is closed. You can return to the board page by clicking <a href="boardview.php?BoardID='.
                              $BoardID.
                              '">here</a>. Here is the text of your message:';
        break;
    }
} while (false);

if ($PostFailureTitle !== false) {
    $mypage = page::standard();
    $mypage->title_body($PostFailureTitle);
    $mypage->leaf('p', $PostFailureMessage);
    $mypage->leaf('p', $EscapedTitle[1]);
    $mypage->leaf( 'textarea',
                   sanitise_str( @$_POST['FirstPost'],
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
$IDNo = dbquery( DBQUERY_INSERT_ID,
                 'INSERT INTO "GeneralThread" ("Closed") VALUES (:closed:)',
                 'closed' , $CloseIt
                 );
dbquery( DBQUERY_INSERT_ID,
         'INSERT INTO "NonGameThread" ("Thread", "LastPost", "OriginalPoster", "LastPoster", "Sticky", "Board", "OriginalBoard", "Title") VALUES (:thread:, UTC_TIMESTAMP(),  :userid:, :userid:, :sticky:, :boardid:, :boardid:, :title:)',
         'thread'  , $IDNo                 ,
         'userid'  , $_SESSION['MyUserID'] ,
         'sticky'  , $StickyIt             ,
         'boardid' , $BoardID              ,
         'title'   , $EscapedTitle[0]
         );
$IDNoM = dbquery( DBQUERY_INSERT_ID,
                  'INSERT INTO "Message" ("PostDate", "User", "MessageText", "Thread") VALUES (UTC_TIMESTAMP(), :user:, :mt:, :thread:)',
                  'user'   , $_SESSION['MyUserID'] ,
                  'thread' , $IDNo                 ,
                  'mt'     , $EscapedContent[0]
                  );
dbquery( DBQUERY_WRITE,
         'UPDATE "Board" SET "LastPost" = UTC_TIMESTAMP(), "NumThreads" = "NumThreads" + 1, "NumMessages" = "NumMessages" + 1, "LastPoster" = :user:, "LastThread" = :thread: WHERE "BoardID" = :boardid:',
         'user'    , $_SESSION['MyUserID'] ,
         'thread'  , $IDNo                 ,
         'boardid' , $BoardID
         );
dbquery( DBQUERY_WRITE,
         'INSERT INTO "RecentEventLog" ("EventType", "EventTime", "ExtraData") VALUES (\'Message\', UTC_TIMESTAMP(), :idno:)',
         'idno' , $IDNoM
         );
dbquery(DBQUERY_COMMIT);
page::redirect( 3,
                'threadview.php?ThreadID='.$IDNo,
                'Thread posted successfully.'
                );

?>