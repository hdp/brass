<?php
require('_std-include.php');

require(HIDDEN_FILES_PATH.'gamelistdisplay.php');
$mypage = page::standard();
$mypage->title_body('Brass Online');
$mypage->loginbox(null, true);
$NumGamesInProgress = dbquery( DBQUERY_READ_INTEGER,
                               'SELECT "MetadatumValue" FROM "Metadatum" WHERE "MetadatumName" = \'Games-InProgress\''
                               );
$NumPlayers = dbquery( DBQUERY_READ_INTEGER,
                       'SELECT "MetadatumValue" FROM "Metadatum" WHERE "MetadatumName" = \'Players\''
                       );
$mypage->leaf('h1', 'Brass Online');
$mypage->leaf( 'p',
               '<i>Brass</i> created by Martin Wallace. Web implementation by Philip Eve. There are currently '.
                   $NumPlayers.' users playing in '.
                   $NumGamesInProgress.' games.'
               );
if ( $Administrator ) {
    require(HIDDEN_FILES_PATH.'adminfpresource.php');
    AdminFP($mypage);
}
if ( $_SESSION['LoggedIn'] and !$Banned ) {
    $create_game_links = '<a href="newgame.php">Create a new game</a>';
    if ( $Administrator ) {
        $create_game_links .= ' / <a href="newgameb.php">Create a new game for others to play</a>';
    }
    $mypage->leaf('p', $create_game_links);
}
$mypage->leaf('h3', 'Games that are currently recruiting:');
gamelistdisplayr($mypage, false);
$mypage->leaf('h3', 'Games that are currently looking for a replacement player:');
gamelistdisplayx($mypage);
$mypage->leaf('h3', 'Games that are currently in progress:');
if ( $NumGamesInProgress > 20 ) {
    $games_in_progress_notice = 'This list shows only 20 of the '.
                                $NumGamesInProgress.
                                ' games that are currently in progress. You can ';
    if ( $_SESSION['LoggedIn'] ) {
        $games_in_progress_notice .= 'see a full list by clicking <a href="allcurrentgames.php">here</a>.';
    } else {
        $games_in_progress_notice .= 'access a full list if you log in.';
    }
    $mypage->leaf('p', $games_in_progress_notice);
}
gamelistdisplayp($mypage, false).
    $mypage->leaf( 'p',
                   '<a href="oldgames.php">View a list of games that are finished</a>'
                   );
if ( $Administrator ) {
    $mypage->leaf( 'p',
                   '<a href="cancelledgames.php">View a list of all games cancelled during recruitment</a> (Admins only)'
                   );
}
$mypage->leaf('h3', 'Discussion boards:');
$mypage->opennode('table', 'class="table_extra_horizontal_padding"');
$mypage->opennode('thead');
$mypage->opennode('tr');
$mypage->leaf('th', 'Board');
$mypage->leaf('th', 'Threads');
$mypage->leaf('th', 'Posts');
$mypage->leaf('th', 'Last post in');
$mypage->leaf('th', 'by');
$mypage->leaf('th', 'at <span style="font-weight: normal;">(GMT)</span>');
$mypage->closenode(2);
$mypage->opennode('tbody');
$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        'CALL "Discussion_ListBoards"()'
                        );
while ( $row = db_fetch_assoc( $QueryResult ) ) {
    if ( !$row['AdminOnly'] or $Administrator ) {
        $mypage->opennode('tr');
        $mypage->leaf( 'td',
                       '<a href="boardview.php?BoardID='.$row['BoardID'].'">'.
                           $row['BName'].
                           '</a>',
                       'align=left'
                       );
        $mypage->leaf('td', $row['NumThreads'] );
        $mypage->leaf('td', $row['NumMessages']);
        if ( is_null($row['UserID']) ) {
            $mypage->leaf('td', 'No posts yet', 'colspan=3');
        } else {
            if ( $_SESSION['LoggedIn'] ) {
                $LastPoster = '<a href="userdetails.php?UserID='.$row['UserID'].'">'.$row['Name'].'</a>';
            } else {
                $LastPoster = $row['Name'];
            }
            $mypage->leaf( 'td',
                           '<a href="threadview.php?ThreadID='.$row['Thread'].'">'.
                               $row['Title'].
                               '</a>',
                           'align=left'
                           );
            $mypage->leaf('td', $LastPoster);
            $mypage->leaf( 'td',
                           date('Y-m-d H:i:s', strtotime($row['LastPost'])),
                           'style="min-width: 145px;"'
                           );
        }
        $mypage->closenode();
    }
}
$mypage->closenode(2);
$mypage->opennode( 'ul',
                   'style="list-style: none; padding: 0px; margin: 0px; margin-top: 16px;"'
                   );
$mypage->leaf( 'li',
               '<a href="http://orderofthehammer.com/credits.htm">Credits, Rules, Disclaimer and FAQ</a>'
               );
$mypage->leaf( 'li',
               '<a href="http://orderofthehammer.com/windowsapp.htm">Windows application to help with learning Brass</a>'
               );
$mypage->leaf( 'li',
               '<a href="statistics.php">Site statistics</a>'
               );
$mypage->finish();

?>