<?php
$TrivialPage = true;
$NoSession = 1;
$NoLoginStuff = 1;
define('TEST_MODE', true);
require('_config.php');

$OutputLines = array( 'Cron job mt.php run at '.date('Y-m-d H:i:s'),
                      'Job frequency: at 4:35am US Central Time every day',
                      'Command: nice -n 19 php -q hf-brass-t/mt.php'
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

// 2. Zero "bad login attempt" counts.

dbquery( DBQUERY_WRITE,
         'UPDATE "User" SET "BadAttempts" = "BadAttempts" - 1 WHERE "BadAttempts" > 0'
         );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 3. *** Cancel games that are "Recruiting" and were created more than 7 days ago.

if ( MAINTENANCE_DISABLED ) {
    $OutputLines[] = 'Not searching for old recruiting games: maintenance is currently disabled.';
} else {
    $OutputLines[] = 'Old recruiting games cancelled: '.
                     dbquery( DBQUERY_AFFECTED_ROWS,
                              'UPDATE "LobbySettings" JOIN "Game" ON "LobbySettings"."Game" = "Game"."GameID" SET "Game"."GameStatus" = \'Cancelled\' WHERE "Game"."GameStatus" = \'Recruiting\' AND "LobbySettings"."CreationTime" < TIMESTAMPADD(DAY, -7, UTC_TIMESTAMP())'
                              );
}
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 4. Delete players' Notes in games that finished more than 7 days ago.

$OutputLines[] = 'Player notes deleted: '.
                 dbquery( DBQUERY_AFFECTED_ROWS,
                          'DELETE FROM "PlayerGameNotes" USING "Game" JOIN "PlayerGameNotes" ON "Game"."GameID" = "PlayerGameNotes"."Game" WHERE "Game"."GameIsFinished" = 1 AND "Game"."LastMove" < TIMESTAMPADD(DAY, -7, UTC_TIMESTAMP())'
                          );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 5. Close threads for games that finished more than 7 days ago, where the thread is not set to "Forced Open".

$OutputLines[] = 'Game threads closed: '.
                 dbquery( DBQUERY_AFFECTED_ROWS,
                          'UPDATE "Game" JOIN "GeneralThread" ON "Game"."GameID" = "GeneralThread"."ThreadID" SET "GeneralThread"."Closed" = \'Closed\' WHERE "Game"."GameIsFinished" = 1 AND "Game"."LastMove" < TIMESTAMPADD(DAY, -7, UTC_TIMESTAMP()) AND "GeneralThread"."Closed" = \'Open\''
                          );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 6. Delete users who registered more than 14 days ago and have not validated.

$OutputLines[] = 'Unvalidated users deleted: '.
                 dbquery( DBQUERY_AFFECTED_ROWS,
                          'DELETE FROM "User" WHERE "UserValidated" = 0 AND "RegistrationDate" < TIMESTAMPADD(DAY, -14, UTC_TIMESTAMP())'
                          );
sleep(2); // Be nice

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 7. *** Check for num-moves-made files that were last modified more than a week ago, and delete them if the corresponding game has finished.

if ( MAINTENANCE_DISABLED ) {
    $OutputLines[] = 'Not checking for obsolete num-moves-made files: maintenance is currently disabled.';
} else {
    $nummovesmade_directory_resource = @opendir(NUM_MOVES_MADE_DIR_NS); // cut off the slash at the end
    if ( $nummovesmade_directory_resource === false ) {
        $OutputLines[] = 'Not checking for obsolete num-moves-made files: encountered an error while attempting to access the num-moves-made directory.';
    } else {
        $gamestoquery = array();
        while ( true ) {
            $currentfile = readdir($nummovesmade_directory_resource);
            if ( $currentfile === false ) {
                break;
            } else if ( !is_dir(NUM_MOVES_MADE_DIR.$currentfile) and
                        substr($currentfile, 0, 1) == 'g' and
                        substr($currentfile, -4) == '.txt' and
                        time() - filemtime(NUM_MOVES_MADE_DIR.$currentfile) > 604800
                        ) {
                $thisgamenumber = (int)substr($currentfile,1,-4);
                if ( $thisgamenumber > 0 ) {
                    $gamestoquery[] = $thisgamenumber;
                }
            }
        }
        closedir($nummovesmade_directory_resource);
        if ( count($gamestoquery) ) {
            $gamesnotfinished = array();
            $QR = dbquery( DBQUERY_READ_RESULTSET,
                           'SELECT "GameID", "GameIsFinished" FROM "Game" WHERE "GameID" IN :gtq:',
                           'gtq' , $gamestoquery
                           );
            while ( $row = db_fetch_assoc($QR) ) {
                if ( !$row['GameIsFinished'] ) {
                    $gamesnotfinished[] = (int)$row['GameID'];
                }
            }
            $filesdeleted = 0;
            for ($i=0; $i<count($gamestoquery); $i++) {
                $nmmfilename = NUM_MOVES_MADE_DIR.'g'.$gamestoquery[$i].'.txt';
                if ( !in_array($gamestoquery[$i], $gamesnotfinished) and file_exists($nmmfilename) ) {
                    unlink($nmmfilename);
                    $filesdeleted++;
                }
            }
            $OutputLines[] = 'Deleted '.$filesdeleted.' num-moves-made files.';
        } else {
            $OutputLines[] = 'No num-moves-made files more than a week old were found.';
        }
    }
}

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 8. Delete entries in RecentEventLog that are more than 30 days old.

dbquery( DBQUERY_WRITE,
         'DELETE FROM "RecentEventLog" WHERE "EventTime" < TIMESTAMPADD(DAY, -30, UTC_TIMESTAMP())'
         );
$OutputLines[] = 'Successfully deleted entries in RecentEventLog that were more than 30 days old.';

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

dbquery( DBQUERY_WRITE,
         'INSERT INTO "MaintenanceRecord" ("ScriptName", "RunDate", "MaintenanceDisabled") VALUES (\'mt\', UTC_TIMESTAMP(), :md:)',
         'md' , MAINTENANCE_DISABLED
         );
dbquery( DBQUERY_WRITE,
         'INSERT INTO "RecentEventLog" ("EventType", "EventTime") VALUES (\'Maintenance\', UTC_TIMESTAMP())'
         );

echo implode("\n\n", $OutputLines);

?>