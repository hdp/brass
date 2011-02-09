<?php

function calculate_site_age ($CreationTimestamp) {
    if ( $CreationTimestamp === false ) { return '?'; }
    $theyear   = date('Y');
    $themonth  = date('n');
    $theday    = date('d');
    $thehour   = date('H');
    $theminute = date('i');
    $creationtheyear   = date('Y', $CreationTimestamp);
    $creationthemonth  = date('n', $CreationTimestamp);
    $creationtheday    = date('d', $CreationTimestamp);
    $creationthehour   = date('H', $CreationTimestamp);
    $creationtheminute = date('i', $CreationTimestamp);
    $timeyears   = $theyear - $creationtheyear;
    $timemonths  = $themonth - $creationthemonth;
    $timedays    = $theday - $creationtheday;
    $timehours   = $thehour - $creationthehour;
    $timeminutes = $theminute - $creationtheminute;
    if ( $timeminutes < 0 ) {
        $timeminutes += 60;
        $timehours--;
    }
    if ( $timehours < 0 ) {
        $timehours += 24;
        $timedays--;
    }
    if ( $timedays < 0 ) {
        $daysarray = array(0, 31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30);
        $daysarray[3] += date('L');
        $timedays += $daysarray[$themonth];
        $timemonths--;
    }
    if ( $timemonths < 0 ) {
        $timemonths += 12;
        $timeyears--;
    }
    if ( $timeyears == 1 ) { $yearplural = '';  }
    else                   { $yearplural = 's'; }
    if ( $timemonths == 1 ) { $monthplural = '';  }
    else                    { $monthplural = 's'; }
    if ( $timedays == 1 ) { $dayplural = '';  }
    else                  { $dayplural = 's'; }
    if ( $timehours == 1 ) { $hourplural = '';  }
    else                   { $hourplural = 's'; }
    if ( $timeminutes == 1 ) { $minuteplural = '';  }
    else                     { $minuteplural = 's'; }
    return $timeyears.' year'.$yearplural.', '.
           $timemonths.' month'.$monthplural.', '.
           $timedays.' day'.$dayplural.', '.
           $timehours.' hour'.$hourplural.' and '.
           $timeminutes.' minute'.$minuteplural;
}

$metadata = array( 'CreationTimestamp'          => false,
                   'Users-Total'                => '?',
                   'Users-Active'               => '?',
                   'Games-InProgress'           => '?',
                   'Games-Total'                => '?',
                   'Games-Players4'             => '?',
                   'Games-Players3'             => '?',
                   'Games-Players2'             => '?',
                   'Games-Competitive'          => '?',
                   'Games-Competitive-Players4' => '?',
                   'Games-Competitive-Players3' => '?',
                   'Games-Competitive-Players2' => '?',
                   'Players'                    => '?',
                   'Messages'                   => '?'
                   );
$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        'SELECT * FROM "Metadatum"'
                        );
while ( $row = db_fetch_assoc($QueryResult) ) {
    $metadata[$row['MetadatumName']] = $row['MetadatumValue'];
}

/////////////////////////////////////////////////
/////////////////////////////////////////////////
/////////////////////////////////////////////////

$mypage = page::standard();
$mypage->script('version_select.js');
$mypage->title_body('Site statistics');
$mypage->loginbox(array( 'Location' => 4                ,
                         'Mode'     => 0                ,
                         'Players'  => $numplayers      ,
                         'Board'    => $board           ,
                         'UserID'   => $RequestedUserID ,
                         'Page'     => $Page
                         ));
$mypage->leaf('h1', 'Site statistics');
$mypage->leaf( 'p',
               'Note: Some of the statistics in the following list are kept (almost) up-to-date, but some are updated as infrequently as once per week.'
               );
$mypage->opennode( 'table',
                   'class="table_no_borders" style="text-align: left;"'
                   );

$mypage->opennode('tr');
$mypage->leaf( 'td',
               'Site has been active for:',
               'align=right'
               );
$mypage->leaf( 'td',
               calculate_site_age($metadata['CreationTimestamp'])
               );

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '(since '.
                   date('Y-m-d H:i', $metadata['CreationTimestamp']).
                   ' GMT)'
               );

$mypage->next();
$mypage->leaf( 'td',
               'Total users:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Users-Total']);

$mypage->next();
$mypage->leaf( 'td',
               'Number of active users*:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Users-Active']);

$mypage->next();
$mypage->leaf( 'td',
               'Number of users playing games right now:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Players']);

$mypage->next();
$mypage->leaf( 'td',
               'Number of games being played:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-InProgress']);

$mypage->next();
$mypage->leaf( 'td',
               'Total number of games started:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Total']);

$mypage->next();
$mypage->leaf( 'td',
               'of which four-player:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Players4']);

$mypage->next();
$mypage->leaf( 'td',
               'three-player:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Players3']);

$mypage->next();
$mypage->leaf( 'td',
               'two-player:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Players2']);

$mypage->next();
$mypage->leaf( 'td',
               'Number of "competitive" games started:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Competitive']);

$mypage->next();
$mypage->leaf( 'td',
               'of which four-player:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Competitive-Players4']);

$mypage->next();
$mypage->leaf( 'td',
               'three-player:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Competitive-Players3']);

$mypage->next();
$mypage->leaf( 'td',
               'two-player:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Games-Competitive-Players2']);

$mypage->next();
$mypage->leaf( 'td',
               'Number of messages posted:',
               'align=right'
               );
$mypage->leaf('td', $metadata['Messages']);

$mypage->closenode(2); // tr, table
$mypage->leaf( 'p',
               '* A user is considered to be "active" if he is not banned and the last time he made a move in a game was less than 60 days ago. (A player who has never made a move in a game is not considered to be "active".)',
               'class="small_footnote"'
               );

/////////////////////////////////////////////////
/////////////////////////////////////////////////
/////////////////////////////////////////////////

$mypage->leaf('h3', 'Other statistics:');
$mypage->opennode( 'ul',
                   'style="list-style: none; padding: 0px; margin: 0px; margin-top: 16px;"'
                   );
$mypage->leaf( 'li',
               '<a href="statistics.php?Mode=1">Most prolific players</a>'
               );
$mypage->leaf( 'li',
               '<a href="statistics.php?Mode=2">Top-rated players</a>'
               );
$mypage->closenode();
$mypage->leaf('h3', 'Best scores');

$mypage->opennode('p');
$mypage->text('Before clicking a link below, first select the board for which you wish to view scores:');
$mypage->opennode( 'select',
                   'id="boardselect" onChange="alter_all_links(0)"'
                   );
$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        'SELECT "VersionGroupID", "VersionName" FROM "GameVersionGroup"'
                        );
if ( $QueryResult === 'NONE' ) { die(); }
while ( $row = db_fetch_assoc($QueryResult) ) {
    $mypage->leaf( 'option',
                   $row['VersionName'],
                   'value='.
                       $row['VersionGroupID'].
                       ( ( $row['VersionGroupID'] == $board ) ?
                         ' selected' :
                         ''
                         )
                   );
}
$mypage->closenode(2); // select, p
$mypage->opennode( 'table',
                   'class="table_no_borders" style="text-align: left;"'
                   );

$number_headers = array( '',
                         '',
                         'With two players:',
                         'With three players:',
                         'With four players:'
                         );
$category_labels = array('Competitive only: ', 'All games: ');
for ($i=4; $i>1; $i--) {
    $mypage->opennode('tr');
    $mypage->leaf( 'td',
                   $number_headers[$i],
                   'align=right style="font-weight: bold; font-style: italic;"'
                   );
    $mypage->leaf( 'td',
                   '<a href="statistics.php?Mode=3&amp;Players='.
                       $i.
                       '&amp;Board='.
                       $board.
                       '&amp;UserID='.
                       $RequestedUserID.
                       '" id="link_'.
                       get_scores_link_number().
                       '">Top 50 in each category</a>'
                   );
    for ($j=0; $j<2; $j++) {
        $mypage->next();
        $mypage->leaf( 'td',
                       $category_labels[$j],
                       'align=right'
                       );
        if ( $_SESSION['LoggedIn'] ) {
            $scores_links = '<a href="statistics.php?Mode='.
                            (7-3*$j).
                            '&Players='.
                            $i.
                            '&Board=1&UserID='.
                            $RequestedUserID.
                            '" id="link_'.
                            get_scores_link_number().
                            '">All results</a>, ';
            if ( $i > 2 ) {
                $scores_links .= '<a href="statistics.php?Mode='.
                                 (8-3*$j).
                                 '&Players='.
                                 $i.
                                 '&Board=1&UserID='.
                                 $RequestedUserID.
                                 '" id="link_'.
                                 get_scores_link_number().
                                 '">Excl. wins</a>, ';
            }
            $scores_links .= '<a href="statistics.php?Mode='.
                             (9-3*$j).
                             '&Players='.
                             $i.
                             '&Board=1&UserID='.
                             $RequestedUserID.
                             '" id="link_'.
                             get_scores_link_number().
                             '">Losses only</a>';
        } else {
            $scores_links = '<del>All result types</del>, ';
            if ( $i > 2 ) {
                $scores_links .= '<del>Excluding wins</del>, ';
            }
            $scores_links .= '<del>Losses only</del> (Log in to access these)';
        }
        $mypage->leaf('td', $scores_links);
    }
    $mypage->closenode();
}

$mypage->closenode(); // table
$mypage->leaf( 'p',
               'Click <a href="index.php">here</a> to return to the Main Page.'
               );
$mypage->finish();

?>