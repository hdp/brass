<?php

$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        'SELECT "VersionGroupID", "VersionName" FROM "GameVersionGroup"'
                        );
if ( $QueryResult === 'NONE' ) {
    myerror( $unexpectederrormessage,
             'Unexpected empty resultset fetching game versions'
             );
}
$BoardName             = '';
$BoardNotExists        = true;
$BoardNamesArray       = array();
$BoardNumbersArray     = array();
$BoardPreselectedArray = array();
while ( $row = db_fetch_assoc($QueryResult) ) {
    $BoardNumbersArray[] = $row['VersionGroupID'];
    $BoardNamesArray[]   = $row['VersionName'];
    if ( $row['VersionGroupID'] == $board ) {
        $BoardNotExists          = false;
        $BoardPreselectedArray[] = ' selected';
        $BoardName               = $row['VersionName'];
    } else {
        $BoardPreselectedArray[] = '';
    }
}

$mypage = page::standard();
if ( $BoardNotExists ) {
    $mypage->title_body('Board does not exist');
    $mypage->leaf( 'p',
                   'The specified game board does not exist. Click <a href="index.php">here</a> to return to the Main Page, or <a href="statistics.php">here</a> to return to the main Statistics page.'
                   );
    $mypage->finish();
}
$BadUserID = false;
if ( $EscapedUserID ) {
    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "Name", "UserValidated" FROM "User" WHERE "UserID" = :user:',
                   'user' , $EscapedUserID
                   );
    if ( $QR === 'NONE' or (int)$QR['UserValidated'] == 0 ) {
        $RequestedUserID = 0;
        $EscapedUserID   = 0;
        $BadUserID       = true;
    }
}

$StartPoint = 100 * ($Page-1);
switch ( $mode ) {
    case 4:
        $title_titlebar = 'All scores (players: '.
                          $numplayers.
                          ', board: '.
                          $BoardName.
                          ')';
        $title_html = 'All scores';
        $resultcolumn = true;
        $friendlycolumn = true;
        if ( $EscapedUserID ) {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."User" = :user: AND "Game"."EffectiveNumPlayers" = :numplayers: AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTcp_AA"(:user:, :numplayers:, :board:)';
        } else {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "PGRScore"."User" AS "Owner", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name", "GC"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" STRAIGHT_JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" STRAIGHT_JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" STRAIGHT_JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" STRAIGHT_JOIN "User" ON "PGRScore"."User" = "User"."UserID" STRAIGHT_JOIN "User" AS "GC" ON "Game"."GameCreator" = "GC"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "Game"."EffectiveNumPlayers" = :numplayers: AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTc_AA"(:numplayers:, :board:)';
        }
        break;
    case 5:
        $title_titlebar = 'Non-winning scores (players: '.
                          $numplayers.
                          ', board: '.
                          $BoardName.
                          ')';
        $title_html = 'Non-winning scores';
        $resultcolumn = true;
        $friendlycolumn = true;
        if ( $EscapedUserID ) {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."User" = :user: AND "PGRScore"."NotWon" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTcp_AD"(:user:, :numplayers:, :board:)';
        } else {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "PGRScore"."User" AS "Owner", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name", "GC"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" STRAIGHT_JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" STRAIGHT_JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" STRAIGHT_JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" STRAIGHT_JOIN "User" ON "PGRScore"."User" = "User"."UserID" STRAIGHT_JOIN "User" AS "GC" ON "Game"."GameCreator" = "GC"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."NotWon" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTc_AD"(:numplayers:, :board:)';
        }
        break;
    case 6:
        $title_titlebar = 'Losing scores (players: '.
                          $numplayers.
                          ', board: '.
                          $BoardName.
                          ')';
        $title_html = 'Losing scores';
        $resultcolumn = false;
        $friendlycolumn = true;
        if ( $EscapedUserID ) {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."User" = :user: AND "PGRScore"."Lost" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTcp_AL"(:user:, :numplayers:, :board:)';
        } else {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "PGRScore"."User" AS "Owner", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name", "GC"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" STRAIGHT_JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" STRAIGHT_JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" STRAIGHT_JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" STRAIGHT_JOIN "User" ON "PGRScore"."User" = "User"."UserID" STRAIGHT_JOIN "User" AS "GC" ON "Game"."GameCreator" = "GC"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."Lost" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTc_AL"(:numplayers:, :board:)';
        }
        break;
    case 7:
        $title_titlebar = 'All scores from competitive games (players: '.
                          $numplayers.
                          ', board: '.
                          $BoardName.
                          ')';
        $title_html = 'All scores from competitive games';
        $resultcolumn = true;
        $friendlycolumn = false;
        if ( $EscapedUserID ) {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."User" = :user: AND "Game"."EffectiveNumPlayers" = :numplayers: AND "Game"."Friendly" = 0 AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTcp_CA"(:user:, :numplayers:, :board:)';
        } else {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "PGRScore"."User" AS "Owner", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name", "GC"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" STRAIGHT_JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" STRAIGHT_JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" STRAIGHT_JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" STRAIGHT_JOIN "User" ON "PGRScore"."User" = "User"."UserID" STRAIGHT_JOIN "User" AS "GC" ON "Game"."GameCreator" = "GC"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "Game"."EffectiveNumPlayers" = :numplayers: AND "Game"."Friendly" = 0 AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTc_CA"(:numplayers:, :board:)';
        }
        break;
    case 8:
        $title_titlebar = 'Non-winning scores from competitive games (players: '.
                          $numplayers.
                          ', board: '.
                          $BoardName.
                          ')';
        $title_html = 'Non-winning scores from competitive games';
        $resultcolumn = true;
        $friendlycolumn = false;
        if ( $EscapedUserID ) {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."User" = :user: AND "PGRScore"."NotWon" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "Game"."Friendly" = 0 AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTcp_CD"(:user:, :numplayers:, :board:)';
        } else {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "PGRScore"."User" AS "Owner", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name", "GC"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" STRAIGHT_JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" STRAIGHT_JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" STRAIGHT_JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" STRAIGHT_JOIN "User" ON "PGRScore"."User" = "User"."UserID" STRAIGHT_JOIN "User" AS "GC" ON "Game"."GameCreator" = "GC"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."NotWon" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "Game"."Friendly" = 0 AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTc_CD"(:numplayers:, :board:)';
        }
        break;
    default:
        $title_titlebar = 'Losing scores from competitive games (players: '.
                          $numplayers.
                          ', board: '.
                          $BoardName.
                          ')';
        $title_html = 'Losing scores from competitive games';
        $resultcolumn = false;
        $friendlycolumn = false;
        if ( $EscapedUserID ) {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."User" = :user: AND "PGRScore"."Lost" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "Game"."Friendly" = 0 AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTcp_CL"(:user:, :numplayers:, :board:)';
        } else {
            $query = 'SELECT ROUND("PGRScore"."Score"/100, 2) AS "AdaptedScore", "PGRScore"."User" AS "Owner", "Game"."GameID", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."GameCreator", "Game"."Friendly", "Game"."LastMove", "PlayerGameRcd"."GameResult", "User"."Name", "GC"."Name" AS "GameCreatorName", "ParticipantRcd"."User" FROM "PGRScore" STRAIGHT_JOIN "Game" ON "PGRScore"."Game" = "Game"."GameID" STRAIGHT_JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" STRAIGHT_JOIN "PlayerGameRcd" ON "PGRScore"."Game" = "PlayerGameRcd"."Game" AND "PGRScore"."User" = "PlayerGameRcd"."User" STRAIGHT_JOIN "User" ON "PGRScore"."User" = "User"."UserID" STRAIGHT_JOIN "User" AS "GC" ON "Game"."GameCreator" = "GC"."UserID" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PGRScore"."Lost" = 1 AND "Game"."EffectiveNumPlayers" = :numplayers: AND "Game"."Friendly" = 0 AND "GameVersion"."VersionGroup" = :board: ORDER BY "PGRScore"."Score" DESC LIMIT :startpoint:, 100';
            $countquery = 'CALL "Stats_HSTc_CL"(:numplayers:, :board:)';
        }
}

$mypage->script('version_select.js');
$mypage->title_body($title_titlebar);
$mypage->loginbox();
$mypage->leaf('h1', $title_html);
if ( $BadUserID ) {
    $mypage->opennode('h3');
    $mypage->text('Players: '.$numplayers.', board: '.$BoardName);
    $mypage->leaf( 'span',
                   '(Could not find a user with the specified User ID!)',
                   'style="font-weight: normal;"'
                   );
    $mypage->closenode();
} else if ( $EscapedUserID ) {
    $mypage->opennode('h3');
    $mypage->text( 'Players: '.
                   $numplayers.
                   ', board: '.
                   $BoardName.
                   ', user:</b> <a href="userdetails.php?UserID='.
                   $EscapedUserID.
                   '">'.
                   $QR['Name'].
                   '</a>'
                   );
    $mypage->leaf( 'span',
                   '(Click <a href="statistics.php?Mode='.
                       $mode.
                       '&Players='.
                       $numplayers.
                       '&Board='.
                       $board.
                       '" id="link_'.
                       get_scores_link_number().
                       '">here</a> to view all players\' scores)',
                   'style="font-weight: normal;"'
                   );
    $mypage->closenode();
} else {
    $mypage->leaf( 'h3',
                   'Players: '.$numplayers.', board: '.$BoardName
                   );
}

$echo_selected = array('', '', '');
$echo_selected[$numplayers-2] = ' selected';
$echo_modeselected = array('', '', '', '', '', '');
$echo_modeselected[$mode-4] = ' selected';
$mypage->opennode('p');
$mypage->text( 'Click <a href="index.php">here</a> to return to the Main Page, or <a href="statistics.php?UserID='.
               $RequestedUserID.
               '">here</a> to return to the main Statistics page.'
               );
$mypage->emptyleaf('br');
$mypage->text('Alternatively, use the following controls to choose which scores to see, and then click "Go" to refresh the page.');
$mypage->closenode();
$mypage->opennode('p');

$mypage->text('Players:');
$mypage->opennode( 'select',
                   'id="playersselect" onChange="alter_all_links(2)"'
                   );
$mypage->leaf( 'option',
               '2',
               'value=2'.$echo_selected[0]
               );
$mypage->leaf( 'option',
               '3',
               'value=3'.$echo_selected[1]
               );
$mypage->leaf( 'option',
               '4',
               'value=4'.$echo_selected[2]
               );
$mypage->closenode();

$mypage->text('Board:');
$mypage->opennode( 'select',
                   'id="boardselect" onChange="alter_all_links(2)"'
                   );
for ($i=0; $i<count($BoardNamesArray); $i++) {
    $mypage->leaf( 'option',
                   $BoardNamesArray[$i],
                   'value='.
                       $BoardNumbersArray[$i].
                       $BoardPreselectedArray[$i]
                   );
}
$mypage->closenode();

$mypage->text('Score type:');
$mypage->opennode( 'select',
                   'id="modeselect" onChange="alter_all_links(2)"'
                   );
$mypage->leaf( 'option',
               'Top 50 scores from all categories',
               'value=3'
               );
$mypage->leaf( 'option',
               'All scores',
               'value=4'.$echo_modeselected[0]
               );
$mypage->leaf( 'option',
               'Non-winning scores',
               'value=5'.$echo_modeselected[1]
               );
$mypage->leaf( 'option',
               'Losing scores',
               'value=6'.$echo_modeselected[2]
               );
$mypage->leaf( 'option',
               'All scores from competitive games',
               'value=7'.$echo_modeselected[3]
               );
$mypage->leaf( 'option',
               'Non-winning scores from competitive games',
               'value=8'.$echo_modeselected[4]
               );
$mypage->leaf( 'option',
               'Losing scores from competitive games',
               'value=9'.$echo_modeselected[5]
               );
$mypage->closenode();

$mypage->leaf( 'a',
               'Go',
               'href="statistics.php?Mode='.
                   $mode.
                   '&amp;Players='.
                   $numplayers.
                   '&amp;Board='.
                   $board.
                   '&amp;UserID='.
                   $RequestedUserID.
                   '" id="link_'.
                   get_scores_link_number().
                   '"'
               );
$mypage->closenode(); // p

$CountQueryResult = dbquery( DBQUERY_READ_INTEGER,
                             $countquery,
                             'user'       , $EscapedUserID ,
                             'numplayers' , $numplayers    ,
                             'board'      , $board
                             );
if ( !$CountQueryResult ) {
    if ( $EscapedUserID ) {
        $mypage->leaf( 'p',
                       'There aren\'t any scores to display for this user in this category.'
                       );
    } else {
        $mypage->leaf( 'p',
                       'There aren\'t any scores to display in this category.'
                       );
    }
    $mypage->finish();
}
require_once(HIDDEN_FILES_PATH.'paginate.php');
$PaginationBar = paginationbar( 'score',
                                'scores',
                                SITE_ADDRESS.'statistics.php',
                                array( 'Mode'    => $mode          ,
                                       'Players' => $numplayers    ,
                                       'Board'   => $board         ,
                                       'UserID'  => $EscapedUserID
                                       ),
                                100,
                                $Page,
                                $CountQueryResult
                                );
$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        $query,
                        'user'        , $EscapedUserID        ,
                        'numplayers'  , $numplayers           ,
                        'board'       , $board                ,
                        'me'          , $_SESSION['MyUserID'] ,
                        'startpoint'  , $StartPoint
                        );
$mypage->append($PaginationBar[0]);
if ( $QueryResult != 'NONE' ) {
    $TranslatedResults = array( 'Finished 1st' => transtext('_ugResult1st') ,
                                'Finished 2nd' => transtext('_ugResult2nd') ,
                                'Finished 3rd' => transtext('_ugResult3rd') ,
                                'Finished 4th' => transtext('_ugResult4th') ,
                                'Finished 5th' => transtext('_ugResult5th')
                                );
    $mypage->opennode( 'table',
                       'class="table_extra_horizontal_padding"'
                       );
    $mypage->opennode('thead');
    $mypage->opennode('tr');
    $mypage->leaf('th', '');
    $mypage->leaf('th', 'Score');
    if ( !$EscapedUserID ) {
        $mypage->leaf('th', 'User');
    }
    if ( $resultcolumn ) {
        $mypage->leaf('th', 'Result');
    }
    if ( $friendlycolumn ) {
        $mypage->leaf('th', 'Friendly?');
    }
    $mypage->leaf('th', 'Game Name');
    $mypage->leaf('th', 'Game Creator');
    $mypage->leaf('th', 'Last Move');
    $mypage->closenode(2); // tr, thead
    $mypage->opennode('tbody');
    $j = 0;
    while ( $row = db_fetch_assoc($QueryResult) ) {
        $j++;
        if ( $row['GTitleDeletedByAdmin'] ) {
            $row['GameName'] = 'The title of this game has been cleared by an Administrator';
        }
        if ( $EscapedUserID == $_SESSION['MyUserID'] or
             ( !$EscapedUserID and
               $row['Owner'] == $_SESSION['MyUserID']
               )
             ) {
            $RowTagAttributes = 'class="mymove"';
        } else if ( is_null($row['User']) ) {
            $RowTagAttributes = null;
        } else {
            $RowTagAttributes = 'class="mygame"';
        }
        $mypage->opennode('tr', $RowTagAttributes);
        $mypage->leaf( 'td',
                       '<b>'.($StartPoint + $j).'</b>'
                       );
        $mypage->leaf( 'td',
                       '<a href="board.php?GameID='.
                           $row['GameID'].
                           '">'.
                           $row['AdaptedScore']
                       );
        if ( !$EscapedUserID ) {
            $mypage->leaf( 'td',
                           '<a href="userdetails.php?UserID='.
                               $row['Owner'].
                               '">'.
                               $row['Name']
                           );
        }
        if ( $resultcolumn ) {
            $mypage->leaf( 'td',
                           $TranslatedResults[$row['GameResult']]
                           );
        }
        if ( $friendlycolumn ) {
            if ( $row['Friendly'] ) {
                $mypage->leaf( 'td',
                               transtext('^Yes'),
                               'align=center bgcolor="#9FFF9F"'
                               );
            } else {
                $mypage->leaf( 'td',
                               transtext('^No'),
                               'align=center bgcolor="#FFC18A"'
                               );
            }
        }
        $mypage->leaf( 'td',
                       '<a href="board.php?GameID='.
                           $row['GameID'].
                           '">'.
                           $row['GameName'].
                           '</a>'
                       );
        $mypage->leaf( 'td',
                       '<a href="userdetails.php?UserID='.
                           $row['GameCreator'].
                           '">'.
                           $row['GameCreatorName'].
                           '</a>'
                       );
        $mypage->leaf( 'td',
                       date( 'Y-M-d H:i:s',
                             strtotime($row['LastMove'])
                             )
                       );
        $mypage->closenode();
    }
    $mypage->closenode(2); // tbody, table
    $mypage->append($PaginationBar[1]);
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page, or <a href="statistics.php">here</a> to return to the main Statistics page.'
                   );
}
$mypage->finish();

?>