<?php
$TrivialPage = true;
$NoSession = 1;
$NoLoginStuff = 1;
define('TEST_MODE', true);
require('_config.php');

$OutputLines = array( 'Cron job mpa.php run at '.date('Y-m-d H:i:s'),
                      'Job frequency: at 0, 15, 30 and 45 minutes past every hour',
                      'Command: nice -n 19 php -q hf-brass-p/mpa.php'
                      );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 1. *** Look for games that have timed out, and act on them.

if ( MAINTENANCE_DISABLED ) {
    $OutputLines[] = 'Not searching for timed-out games: maintenance is currently disabled.';
} else {
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            'CALL "Maintenance_TimeLimitGames"()'
                            );
    if ( $QueryResult === 'NONE' ) {
        $OutputLines[] = 'Games timed out: none';
    } else {
        $gamestimedout = array();
        $SystemActing = true;
        $PersonActing = -50;
        require(HIDDEN_FILES_PATH.'gamegetdata_board_do.php');
        require(HIDDEN_FILES_PATH.'kraftwerk.php');
        require(HIDDEN_FILES_PATH.'downsizeresource.php');
        while ( $row = db_fetch_assoc($QueryResult) ) {
            $GAME = gamegetdata_board_do($row['Game']);
            $gamestimedout[] = $GAME['GameID'];
            if ( ( $GAME['CurrentPlayers'] - $GAME['PlayersMissingThatMatter'] == $GAME['MinimumPlayersAllowed'] and
                   $GAME['MinimumPlayersAllowed'] > 2
                   ) or
                 $GAME['CurrentPlayers'] == 3
                 ) {
                if ( $GAME['DoWhatAtB'] == 'Downsize' ) {
                    $GAME['DoWhatAtB'] = 'Abort';
                }
                if ( $GAME['DoWhatAtB'] == 'Kick current player; subsequently downsize' ) {
                    $GAME['DoWhatAtB'] = 'Kick current player; subsequently abort';
                }
            }
            if ( $GAME['PlayersMissing'] + 1 == $GAME['CurrentPlayers'] ) {
                abortgame(2);
            } else if ( $GAME['DoWhatAtB'] == 'Downsize' ) {
                downsizegame(true);
            } else if ( $GAME['DoWhatAtB'] == 'Abort' ) {
                if ( $GAME['GameStatus'] == 'In Progress' ) {
                    if ( !KickPlayer($GAME['PlayerToMove'],3) ) {
                        abortgame(2);
                    }
                } else {
                    abortgame(2);
                }
            } else if ( $GAME['DoWhatAtB'] == 'Kick current player; subsequently downsize' ) {
                if ( $GAME['GameStatus'] == 'Recruiting Replacement' ) {
                    downsizegame(true);
                } else {
                    KickPlayer($GAME['PlayerToMove'],3);
                }
            } else {
                if ( $GAME['GameStatus'] == 'Recruiting Replacement' ) {
                    abortgame(2);
                } else {
                    KickPlayer($GAME['PlayerToMove'],3);
                }
            }
            $GAME['MoveMade'] = 1;
            $didsomething = 1;
            while ( $didsomething ) { $didsomething = gamecheck(); }
            dbformatgamedata();
        }
        $OutputLines[] = 'Games timed out: '.count($gamestimedout).
                         ', namely '.implode(', ',$gamestimedout);
        sleep(2); // Be nice
    }
}

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 2. Decrement "bad login attempt" counts.

dbquery( DBQUERY_WRITE,
         'UPDATE "User" SET "BadAttempts" = "BadAttempts" - 1 WHERE "BadAttempts" > 0'
         );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 3. Change the appearance likelihood of games in progress.

dbquery( DBQUERY_WRITE,
         'UPDATE "GameInProgress" SET "AppearancePriority" = FLOOR(RAND() * ("AverageRating" + 1000))'
         );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 4. Recount the number of players in games, for the front-page statistic.

dbquery( DBQUERY_WRITE,
         'UPDATE "Metadatum" SET "MetadatumValue" = (SELECT COUNT(DISTINCT "PlayerGameRcd"."User") FROM "GameInProgress" JOIN "PlayerGameRcd" ON "GameInProgress"."Game" = "PlayerGameRcd"."Game" WHERE "PlayerGameRcd"."CurrentOccupant" = 0) WHERE "MetadatumName" = \'Players\''
         );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

dbquery( DBQUERY_WRITE,
         'INSERT INTO "MaintenanceRecord" ("ScriptName", "RunDate", "MaintenanceDisabled") VALUES (\'mpa\', UTC_TIMESTAMP(), :md:)',
         'md' , MAINTENANCE_DISABLED
         );
dbquery( DBQUERY_WRITE,
         'INSERT INTO "RecentEventLog" ("EventType", "EventTime") VALUES (\'Maintenance\', UTC_TIMESTAMP())'
         );

echo implode("\n\n", $OutputLines);

?>