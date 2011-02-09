<?php

$mypage = page::standard();
$mypage->title_body($statsb_parameters[0]);
$mypage->loginbox(array( 'Location' => 4                ,
                         'Mode'     => $mode            ,
                         'Players'  => $numplayers      ,
                         'Board'    => $board           ,
                         'UserID'   => $RequestedUserID ,
                         'Page'     => $Page
                         ));
$mypage->leaf('h1', $statsb_parameters[0]);

$StartPoint = 100 * ($Page-1);
$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        'SELECT SQL_CALC_FOUND_ROWS "UserID", "Name", '.$statsb_parameters[1].' FROM "User" WHERE "UserValidated" = 1 ORDER BY '.$statsb_parameters[2].' DESC, "UserID" LIMIT :startpoint:, 100',
                        'startpoint' , $StartPoint
                        );
$CountQueryResult = dbquery( DBQUERY_READ_INTEGER,
                             'SELECT FOUND_ROWS() AS "Co"'
                             );
if ( !$CountQueryResult ) {
    $mypage->leaf( 'p',
                   'There aren\'t any users to display.'
                   );
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page, or <a href="statistics.php">here</a> to return to the main Statistics page.'
                   );
    $mypage->finish();
}

require(HIDDEN_FILES_PATH.'paginate.php');
$PaginationBar = paginationbar( 'user',
                                'users',
                                SITE_ADDRESS.'statistics.php',
                                array('Mode' => $mode),
                                100,
                                $Page,
                                $CountQueryResult
                                );
$mypage->append($PaginationBar[0]);
if ( $QueryResult === 'NONE' ) {
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page, or <a href="statistics.php">here</a> to return to the main Statistics page.'
                   );
    $mypage->finish();
}

$mypage->opennode('table');
$mypage->opennode('thead');
$mypage->opennode('tr');
$mypage->leaf( 'th',
               'Rank',
               'style="min-width: 80px;"'
               );
$mypage->leaf( 'th',
               'Name',
               'style="min-width: 320px;"'
               );
$mypage->leaf( 'th',
               $statsb_parameters[3],
               'style="min-width: 80px;"'
               );
$mypage->closenode(2);
$mypage->opennode('tbody');

while ( $row = db_fetch_assoc($QueryResult) ) {
    if ( $_SESSION['LoggedIn'] and $_SESSION['MyUserID'] == $row['UserID'] ) {
        $RowTagAttributes = 'class="mymove"';
    } else {
        $RowTagAttributes = null;
    }
    $mypage->opennode('tr', $RowTagAttributes);
    $mypage->leaf( 'td',
                   $row[$statsb_parameters[5]].
                       $equalsarray[$row[$statsb_parameters[6]]],
                   'style="font-weight: bold;"'
                   );
    if ( $_SESSION['LoggedIn'] ) {
        $mypage->leaf( 'td',
                       '<a href="userdetails.php?UserID='.
                           $row['UserID'].
                           '">'.
                           $row['Name'].
                           '</a>'
                       );
        if ( $mode == 1 ) {
            $mypage->leaf( 'td',
                           '<a href="oldgames.php?UserID='.
                               $row['UserID'].
                               '">'.
                               $row[$statsb_parameters[4]].
                               '</a>'
                           );
        } else {
            $mypage->leaf('td', $row[$statsb_parameters[4]]);
        }
    } else {
        $mypage->leaf('td', $row['Name']);
        $mypage->leaf('td', $row[$statsb_parameters[4]]);
    }
    $mypage->closenode();
}

$mypage->closenode(2); // tbody, table
$mypage->append($PaginationBar[1]);
$mypage->leaf( 'p',
               'Click <a href="index.php">here</a> to return to the Main Page, or <a href="statistics.php">here</a> to return to the main Statistics page.'
               );
$mypage->finish();

?>