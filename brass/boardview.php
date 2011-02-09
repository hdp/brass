<?php
require('_std-include.php');

if ( @$_POST['FormSubmit'] == 'Execute' ) {
    if ( $Administrator ) {
        $BoardID = (int)$_POST['WhichBoard'];
        if ( @$_POST['BoardClosed'] ) { $CloseIt = 1; }
        else                          { $CloseIt = 0; }
        if ( @$_POST['BoardAdminOnly'] ) { $AdminOnlyIt = 1; }
        else                             { $AdminOnlyIt = 0; }
        $QueryResult = dbquery( DBQUERY_WRITE,
                                'UPDATE "Board" SET "Closed" = :closeit:, "AdminOnly" = :adminonly: WHERE "BoardID" = :board:',
                                'closeit'   , $CloseIt     ,
                                'adminonly' , $AdminOnlyIt ,
                                'board'     , $BoardID
                                );
        page::redirect( 3,
                        'boardview.php?BoardID='.$BoardID,
                        'Changes made successfully.'
                        );
    } else {
        $mypage = page::standard();
        $mypage->title_body('Not authorised');
        $mypage->leaf( 'p',
                       'You are not authorised to take this action. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
} else if ( @$_POST['FormSubmit'] == 'Post' ) {
    require(HIDDEN_FILES_PATH.'sanitise_str_fancy.php');
    require(HIDDEN_FILES_PATH.'bvpostthread.php');
} else if ( isset($_GET['BoardID']) ) {
    if ( isset($_GET['Page']) ) {
        $Page = (int)$_GET['Page'];
        if ( $Page < 1 ) { $Page = 1; }
    } else {
        $Page = 1;
    }
    $BoardID = (int)$_GET['BoardID'];
    $rowx = dbquery( DBQUERY_READ_SINGLEROW,
                     'SELECT * FROM "Board" WHERE "BoardID" = :board:',
                     'board' , $BoardID
                     );
    if ( $rowx === 'NONE' ) {
        $mypage = page::standard();
        $mypage->title_body('Error');
        $mypage->leaf( 'p',
                       'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $rowx['AdminOnly'] and !$Administrator ) {
        $mypage = page::standard();
        $mypage->title_body('Not authorised');
        $mypage->leaf( 'p',
                       'You are not authorised to view this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $mypage = page::standard();
    $mypage->title_body($rowx['BName']);
    $mypage->loginbox(array( 'Location' => 2        ,
                             'BoardID'  => $BoardID ,
                             'Page'     => $Page
                             ));
    $mypage->leaf('h1', $rowx['BName']);
    if ( $Administrator ) {
        if ( $rowx['Closed'] ) { $SClosed = ' checked'; }
        else                   { $SClosed = '';         }
        if ( $rowx['AdminOnly'] ) { $SAdminOnly = ' checked'; }
        else                      { $SAdminOnly = '';         }
        $mypage->opennode('form', 'action="boardview.php" method="POST"');
        $mypage->opennode('p');
        $mypage->emptyleaf( 'input',
                            'type="checkbox" name="BoardClosed" value=1'.
                                $SClosed
                            );
        $mypage->text('This board is closed ---');
        $mypage->emptyleaf( 'input',
                            'type="checkbox" name="BoardAdminOnly" value=1'.
                                $SAdminOnly
                            );
        $mypage->text('This board is admin-only ---');
        $mypage->emptyleaf( 'input',
                            'type="submit" name="FormSubmit" value="Execute"'
                            );
        $mypage->closenode();
        $mypage->emptyleaf( 'input',
                            'type="hidden" name="WhichBoard" value='.
                                $BoardID
                            );
        $mypage->closenode();
    }
    $CountQueryResult = dbquery( DBQUERY_READ_INTEGER,
                                 'SELECT COUNT(*) AS "Co" FROM "NonGameThread" WHERE "Board" = :board:',
                                 'board' , $BoardID
                                 );
    if ( !$CountQueryResult ) {
        $mypage->leaf( 'p',
                       'There aren\'t any threads to display.'
                       );
    } else {
        $StartPoint = 100 * ( $Page - 1 );
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "GeneralThread"."ThreadID", "GeneralThread"."Closed", "GeneralThread"."NumberOfPosts", "NonGameThread"."LastPost", "NonGameThread"."OriginalPoster", "NonGameThread"."LastPoster", "NonGameThread"."Sticky", "NonGameThread"."Title", "NonGameThread"."TitleDeletedByAdmin", "User"."UserID", "User"."Name", "LastPoster"."UserID" AS "LastPosterUserID", "LastPoster"."Name" AS "LastPosterName" FROM "NonGameThread" LEFT JOIN "GeneralThread" ON "NonGameThread"."Thread" = "GeneralThread"."ThreadID" LEFT JOIN "User" ON "NonGameThread"."OriginalPoster" = "User"."UserID" LEFT JOIN "User" AS "LastPoster" ON "NonGameThread"."LastPoster" = "LastPoster"."UserID" WHERE "NonGameThread"."Board" = :board: ORDER BY "NonGameThread"."Sticky" DESC, "NonGameThread"."LastPost" DESC LIMIT :startpoint:, 100',
                       'board'      , $BoardID    ,
                       'startpoint' , $StartPoint
                       );
        require_once(HIDDEN_FILES_PATH.'paginate.php');
        $PaginationBar = paginationbar( 'thread',
                                        'threads',
                                        SITE_ADDRESS.'boardview.php',
                                        array('BoardID' => $BoardID),
                                        100,
                                        $Page,
                                        $CountQueryResult
                                        );
        $mypage->append($PaginationBar[0]);
        if ( $QR !== 'NONE' ) {
            $mypage->opennode( 'table',
                               'class="table_extra_horizontal_padding"'
                               );
            $mypage->opennode('thead');
            $mypage->opennode('tr');
            $mypage->leaf('th', 'Name', 'style="width: 300px;"');
            $mypage->leaf('th', 'Creator');
            $mypage->leaf('th', 'Posts');
            $mypage->leaf('th', 'Last Post');
            $mypage->leaf('th', 'Last Poster');
            $mypage->closenode(2);
            $mypage->opennode('tbody');
            while ( $row = db_fetch_assoc($QR) ) {
                if ( $row['TitleDeletedByAdmin'] ) {
                    $row['Title'] = 'The title of this thread has been cleared by an Administrator';
                }
                if ( $row['Sticky'] ) { $stickytext = ' (Sticky)'; }
                else                  { $stickytext = '';          }
                if ( $row['Closed'] == 'Forced Open' or
                     $row['Closed'] == 'Open'
                     ) {
                    $closedtext = '';
                } else {
                    $closedtext = ' (Closed)';
                }
                if ( $_SESSION['LoggedIn'] ) {
                    $CreatorText = '<a href="userdetails.php?UserID='.
                                   $row['UserID'].
                                   '">'.
                                   $row['Name'].
                                   '</a>';
                    $LPText = '<a href="userdetails.php?UserID='.
                              $row['LastPosterUserID'].
                              '">'.
                              $row['LastPosterName'].
                              '</a>';
                } else {
                    $CreatorText = $row['Name'];
                    $LPText = $row['LastPosterName'];
                }
                $mypage->opennode('tr');
                $mypage->leaf( 'td',
                               '<a href="threadview.php?ThreadID='.
                                   $row['ThreadID'].
                                   '">'.
                                   $row['Title'].
                                   '</a>'.
                                   $closedtext.
                                   $stickytext
                               );
                $mypage->leaf('td', $CreatorText);
                $mypage->leaf('td', $row['NumberOfPosts']);
                $mypage->leaf( 'td',
                               date('Y-m-d H:i:s', strtotime($row['LastPost']))
                               );
                $mypage->leaf('td', $LPText);
                $mypage->closenode();
            }
            $mypage->closenode(2);
            $mypage->append($PaginationBar[1]);
        }
    }
    if ( $Administrator or
         ( !$rowx['Closed'] and !$Banned and @$_SESSION['LoggedIn'] )
         ) {
        $mypage->leaf('h3', 'Post a new thread');
        $mypage->opennode('form', 'action="boardview.php" method="POST"');
        $mypage->opennode( 'table',
                           'class="table_no_borders" style="text-align: left;"'
                           );
        $mypage->opennode('tr');
        $mypage->leaf( 'td',
                       'Title:<br>(max. 80 characters)',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<input type="text" name="ThreadTitle" size=20 maxlength=80>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       'Content of first post:',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<textarea cols=100 rows=16 name="FirstPost"></textarea>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       'Post Closed?',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<input type="checkbox" name="PostClosed" value=1>'
                       );
        $mypage->next();
        if ( $Administrator ) {
            $mypage->leaf( 'td',
                           'Post Sticky?',
                           'align=right'
                           );
            $mypage->leaf( 'td',
                           '<input type="checkbox" name="PostSticky" value=1>'
                           );
            $mypage->next();
        }
        $mypage->leaf('td', '');
        $mypage->leaf('td', '<input type="submit" name="FormSubmit" value="Post">');
        $mypage->closenode(2);
        $mypage->emptyleaf( 'input',
                            'type="hidden" name="WhichBoard" value='.
                                $BoardID
                            );
        $mypage->emptyleaf( 'input',
                            'type="hidden" name="MessagesPostedCounter" value='.
                                $MessagesPosted
                            );
        $mypage->closenode();
        $mypage->leaf( 'p',
                       'The available formatting options are detailed <a href="http://orderofthehammer.com/credits.htm#formatting">here</a>. The limit is around 50,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before).'
                       );
    }
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
} else {
    $mypage = page::standard();
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

?>