<?php
require('_std-include.php');

function gamelistdisplayf (tagtree &$tt, $StartPoint, $Pagenum) {
    if ( $_SESSION['LoggedIn'] ) {
        $Query = 'SELECT "Game"."GameID", "Game"."Friendly", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."LastMove", "Game"."GameCreator", "Game"."OriginalPlayers", "Game"."CurrentPlayers", "Game"."RailPhase", "Game"."Round", "Game"."NumRounds", "Game"."GameStatus", "GameVersion"."ShortVersionName", "GameVersionGroup"."VersionName", "GameVersion"."VersionNameSuffix", "GameVersion"."Creators", "User"."Name" AS "GameCreatorName", "PlayerGameRcd"."User" FROM "Game" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "GameVersionGroup" ON "GameVersion"."VersionGroup" = "GameVersionGroup"."VersionGroupID" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "PlayerGameRcd" ON "PlayerGameRcd"."User" = :me: AND "Game"."GameID" = "PlayerGameRcd"."Game" WHERE "Game"."GameIsFinished" = 1 ORDER BY "Game"."LastMove" DESC LIMIT :startpoint:, 100';
        $me = $_SESSION['MyUserID'];
    } else {
        $Query = 'SELECT "Game"."GameID", "Game"."Friendly", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."LastMove", "Game"."GameCreator", "Game"."OriginalPlayers", "Game"."CurrentPlayers", "Game"."RailPhase", "Game"."Round", "Game"."NumRounds", "Game"."GameStatus", "GameVersion"."ShortVersionName", "GameVersionGroup"."VersionName", "GameVersion"."VersionNameSuffix", "GameVersion"."Creators", "User"."Name" AS "GameCreatorName" FROM "Game" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "GameVersionGroup" ON "GameVersion"."VersionGroup" = "GameVersionGroup"."VersionGroupID" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" WHERE "Game"."GameIsFinished" = 1 ORDER BY "Game"."LastMove" DESC LIMIT :startpoint:, 100';
        $me = 0;
    }
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            $Query,
                            'me'         , $me         ,
                            'startpoint' , $StartPoint
                            );
    $CountQueryResult = dbquery( DBQUERY_READ_INTEGER_TOLERANT_ZERO,
                                 'SELECT "MetadatumValue" FROM "Metadatum" WHERE "MetadatumName" = \'Games-Finished\''
                                 );
    if ( !$CountQueryResult ) { return false; }
    require_once(HIDDEN_FILES_PATH.'paginate.php');
    $PaginationBar = paginationbar( 'game',
                                    'games',
                                    SITE_ADDRESS.'oldgames.php',
                                    null,
                                    100,
                                    $Pagenum,
                                    $CountQueryResult
                                    );
    $tt->append($PaginationBar[0]);
    if ( $QueryResult === 'NONE' ) { return; }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 270px;"');
    $tt->leaf('th', 'Creator'  );
    $tt->leaf('th', 'Friendly?');
    $tt->leaf( 'th',
               'Original',
               'title="The number of players the game had when it started."'
               );
    $tt->leaf( 'th',
               'Final',
               'title="The number of players the game had when it finished."'
               );
    $tt->leaf('th', 'Status');
    $tt->leaf('th', 'Round');
    $tt->leaf('th', 'Last Move <span style="font-weight: normal;">(GMT)</span>');
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $RoundColumnStyles = array('gamelist_roundcol_c', 'gamelist_roundcol_r');
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GTitleDeletedByAdmin'] ) {
            $row['GameName'] = 'The title of this game has been cleared by an Administrator';
        }
        $RowTagAttributes = null;
        if ( $_SESSION['LoggedIn'] ) {
            $GameCreatorColumn = '<a href="userdetails.php?UserID='.
                                 $row['GameCreator'].
                                 '">'.
                                 $row['GameCreatorName'].
                                 '</a>';
            if ( !is_null($row['User']) ) {
                $RowTagAttributes = 'class="mygame"';
            }
        } else {
            $GameCreatorColumn = $row['GameCreatorName'];
        }
        $tt->opennode('tr', $RowTagAttributes);
        $version_name = vname($row['VersionName'], $row['VersionNameSuffix']);
        $tt->leaf( 'td',
                   '<img src="gfx/icon-'.
                       strtolower($row['ShortVersionName']).
                       '.png" alt="'.
                       $version_name.
                       '" title="'.
                       $version_name.
                       ' ('.
                       $row['Creators'].
                       ')">',
                   'width=23 style="border-right: none;"'
                   );
        $tt->leaf( 'td',
                   '<a href="board.php?GameID='.
                       $row['GameID'].
                       '">'.
                       $row['GameName'].
                       '</a>',
                   'style="border-left: none; padding-left: 0px; text-align: left;"'
                   );
        $tt->leaf('td', $GameCreatorColumn);
        if ( $row['Friendly'] ) {
            $tt->leaf('td', transtext('^Yes'), 'bgcolor="#9FFF9F"');
        } else {
            $tt->leaf('td', transtext('^No') , 'bgcolor="#FFC18A"');
        }
        $tt->leaf('td', $row['OriginalPlayers'] );
        $tt->leaf('td', $row['CurrentPlayers']  );
        $tt->leaf('td', $row['GameStatus']);
        $tt->leaf( 'td',
                   ($row['Round'] >= 10 ? '' : '&nbsp;&nbsp;').
                       $row['Round'].' / '.$row['NumRounds'],
                   'class="'.$RoundColumnStyles[$row['RailPhase']].'"'
                   );
        $lmtime = strtotime($row['LastMove']);
        $tt->opennode('td');
        $tt->leaf('span', date('Y', $lmtime), 'style="font-size: 50%;"');
        $tt->text(date('M-d H:i:s', $lmtime));
        $tt->closenode(2); // td, tr
    }
    $tt->closenode(2); // tbody, table
    $tt->append($PaginationBar[1]);
}

if ( isset($_GET['UserID']) ) {
    $RequestedUserID = (int)$_GET['UserID'];
    if ( $_SESSION['LoggedIn'] ) {
        $EscapedUserID = $RequestedUserID;
    } else {
        $EscapedUserID = 0;
    }
} else {
    $RequestedUserID = 0;
    $EscapedUserID = 0;
}
if ( isset($_GET['Page']) ) {
    $Page = (int)$_GET['Page'];
    if ( $Page < 1 ) { $Page = 1; }
} else {
    $Page = 1;
}

$mypage = page::standard();
if ( $EscapedUserID ) {
    $row = dbquery( DBQUERY_READ_SINGLEROW,
                    'SELECT "Name", "UserValidated", "Pronoun" FROM "User" WHERE "UserID" = :user:',
                    'user' , $EscapedUserID
                    );
    if ( $row === 'NONE' or !$row['UserValidated'] ) {
        $mypage->title_body('User Does Not Exist');
        $mypage->leaf( 'p',
                       'The specified user does not exist. You can click <a href="oldgames.php">here</a> to view all users\' Finished Games, or <a href="index.php">here</a> to visit the Main Page.'
                       );
        $mypage->finish();
    }
    $mypage->title_body( 'List of games that '.
                         $row['Name'].
                         ' has finished playing in'
                         );
    $mypage->loginbox();
    $mypage->leaf( 'h2',
                   'List of games that <a href="userdetails.php?UserID='.
                       $EscapedUserID.
                       '">'.
                       $row['Name'].
                       '</a> has finished playing in'
                   );
    require_once(HIDDEN_FILES_PATH.'gamelistdisplayuf.php');
    if ( gamelistdisplayuf( $mypage,
                            $EscapedUserID,
                            $row['Name'],
                            $row['Pronoun'],
                            false,
                            100 * ($Page-1),
                            100,
                            $Page
                            ) === false
         ) {
        $mypage->leaf( 'p',
                       'There aren\'t any finished games to display that were played in by '.
                           $row['Name'].
                           '.'
                       );
    }
    $mypage->leaf( 'p',
                   'You can click <a href="oldgames.php">here</a> to view all users\' Finished Games, or <a href="index.php">here</a> to return to the Main Page.'
                   );
} else {
    $mypage->title_body('List of games that have finished');
    $mypage->loginbox(array( 'Location' => 5                ,
                             'UserID'   => $RequestedUserID ,
                             'Page'     => $Page
                             ));
    $mypage->leaf('h1', 'List of games that have finished');
    if ( gamelistdisplayf($mypage, 100*($Page-1), $Page) === false ) {
        $mypage->leaf( 'p',
                       'There aren\'t any finished games to display.'
                       );
    }
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
}
$mypage->finish();

?>