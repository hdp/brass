<?php
require('_std-include.php');
require(HIDDEN_FILES_PATH.'displaythread.php');

if ( @$_POST['FormSubmitA'] == 'Execute' ) {
    require(HIDDEN_FILES_PATH.'tvadminthread.php');
} else if ( @$_POST['FormSubmitA'] == 'Change' ) {
    require(HIDDEN_FILES_PATH.'tvnormalthread.php');
} else if ( isset($_GET['TheMessage']) ) {
    require(HIDDEN_FILES_PATH.'tvcensor.php');
} else if ( @$_POST['FormSubmitA'] == 'Post' ) {
    require(HIDDEN_FILES_PATH.'sanitise_str_fancy.php');
    require(HIDDEN_FILES_PATH.'tvpost.php');
} else if ( !isset($_GET['ThreadID']) ) {
    $mypage = page::standard();
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks like you\'ve been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$ThreadID = sanitise_int(@$_GET['ThreadID']);
$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'SELECT "GeneralThread".*, "NonGameThread".*, "Board"."AdminOnly", "Board"."BName" FROM "GeneralThread" LEFT JOIN "NonGameThread" ON "GeneralThread"."ThreadID" = "NonGameThread"."Thread" LEFT JOIN "Board" ON "NonGameThread"."Board" = "Board"."BoardID" WHERE "GeneralThread"."ThreadID" = :thread:',
               'thread' , $ThreadID
               );
if ( $QR === 'NONE' or is_null($QR['BName']) ) {
    $mypage = page::standard();
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks like you\'ve been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
extract($QR);
if ( $AdminOnly and !$Administrator ) {
    $mypage = page::standard();
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to view this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( $Administrator ) {
    $TtlText = $Title;
    if ( $TitleDeletedByAdmin ) { $TtlText .= ' <font color="#FF0000">(CLEARED)</font>'; }
} else {
    if ( $TitleDeletedByAdmin ) {
        $TtlText = 'The title of this thread has been cleared by an Administrator';
    } else {
        $TtlText = $Title;
    }
}
if ( $TitleDeletedByAdmin ) {
    $TtlBarText = 'The title of this thread has been cleared by an Administrator';
} else {
    $TtlBarText = $Title;
}
echo DOCTYPE_ETC.
     '<script type="text/javascript" src="formatmessage.js"></script><title>'.$TtlBarText.'</title></head><body onload="format_all_messages(false);">';
EchoLoginForm(array( 'Location' => 3         ,
                     'ThreadID' => $ThreadID
                     ));
if ( $OriginalBoard == $Board ) {
    $OriginalLocationText = '';
} else {
    $OLQR = dbquery( DBQUERY_READ_SINGLEROW,
                     'SELECT "AdminOnly", "BName" FROM "Board" WHERE "BoardID" = :board:',
                     'board' , $OriginalBoard
                     );
    if ( $OLQR === 'NONE' or $OLQR['AdminOnly'] ) {
        $OriginalLocationText = '';
    } else {
        $OriginalLocationText = '<p>(Moved from '.$OLQR['BName'].')</p>';
    }
}
echo '<h1><a href="boardview.php?BoardID='.$Board.'">'.$BName.'</a></h1><h3>'.$TtlText.'</h3>'.$OriginalLocationText;
if ( $Administrator ) {
    if ( $Sticky ) { $CSticky = ' checked'; }
    else           { $CSticky = '';         }
    if ( $TitleDeletedByAdmin ) { $CTitleDeleted = ' checked'; }
    else                        { $CTitleDeleted = '';         }
    if ( $Closed == 'Forced Closed' ) { $StringFC = ' selected'; }
    else                              { $StringFC = '';          }
    if ( $Closed == 'Closed' ) { $StringC = ' selected'; }
    else                       { $StringC = '';          }
    if ( $Closed == 'Forced Open' ) { $StringFO = ' selected'; }
    else                            { $StringFO = '';          }
    if ( $Closed == 'Open' ) { $StringO = ' selected'; }
    else                     { $StringO = '';          }
    echo '<form action="threadview.php" method="POST"><p>Move thread to: <select name="NewLocation"><option value="'.$Board.'">(Don\'t)';
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "BName", "BoardID" FROM "Board" WHERE "BoardID" <> :board: ORDER BY "OrderingNumber"',
                       'board' , $Board
                       );
        while ( $row = db_fetch_assoc($QR) ) {
            echo '<option value="'.$row['BoardID'].'">'.$row['BName'];
        }
    echo '</select></p><p><input type="checkbox" name="ThreadSticky" value=1'.
         $CSticky.
         '> Thread is sticky --- Thread is <select name="Closedness"><option value="FC"'.
         $StringFC.
         '>Forced Closed<option value="C"'.
         $StringC.
         '>Closed<option value="O"'.
         $StringO.
         '>Open<option value="FO"'.
         $StringFO.
         '>Forced Open</select> --- <input type="checkbox" name="TitleDeleted" value=1'.
         $CTitleDeleted.
         '> Title cleared --- <input type="submit" name="FormSubmitA" value="Execute"></p><input type="hidden" name="WhichThread" value="'.
         $ThreadID.
         '"></form>';
} else if ( $_SESSION['LoggedIn'] and $OriginalPoster == $_SESSION['MyUserID'] ) {
    if ( $Closed == 'Forced Closed' ) {
        echo '<p>An Administrator has decided that this thread should be closed. You may not open it.</p>';
    } else if ( $Closed == 'Forced Open' ) {
        echo '<p>An Administrator has decided that this thread should remain open. You may not close it.</p>';
    } else {
        if ( $Closed == 'Closed' ) { $CClosed = ' checked'; }
        else                       { $CClosed = '';         }
        echo '<form action="threadview.php" method="POST"><input type="checkbox" name="ThreadClosed" value=1'.
             $CClosed.
             '> Thread is closed --- <input type="submit" name="FormSubmitA" value="Change"></p><input type="hidden" name="WhichThread" value="'.
             $ThreadID.
             '"></form>';
    }
} else {
    if ( $Closed == 'Closed' or $Closed == 'Forced Closed' ) {
        echo '<p>This thread is closed';
    } else {
        echo '<p>This thread is open';
    }
    if ( $Sticky ) {
        echo ', and it is stickied';
    }
    echo '.</p>';
}
displaythread($ThreadID, $Closed, 1, $Administrator, $Banned);
echo '<p>Click <a href="boardview.php?BoardID='.$Board.'">here</a> to return to the Board Page, or <a href="index.php">here</a> to return to the Main Page.</p></body></html>';

?>