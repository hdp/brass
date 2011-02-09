<?php
$TrivialPage = true;
$NoSession = 1;
$NoLoginStuff = 1;
define('TEST_MODE', true);
require('_config.php');

$OutputLines = array( 'Cron job mpd.php run at '.date('Y-m-d H:i:s'),
                      'Job frequency: at 4:05am US Central Time every day except Fridays and Sundays',
                      'Command: nice -n 19 php -q hf-brass-p/mpd.php'
                      );

date_default_timezone_set('America/Chicago');
switch ( (int)date('w') ) {
    case 1:
    case 4:
////////// 1. Delete cancelled games that were cancelled more than 10 days ago.
        $OutputLines[] = 'Old cancelled games deleted: '.
                         dbquery( DBQUERY_AFFECTED_ROWS,
                                  'DELETE FROM "GeneralThread" USING "GeneralThread" JOIN "Game" ON "GeneralThread"."ThreadID" = "Game"."GameID" WHERE "Game"."GameStatus" = \'Cancelled\' AND "Game"."LastMove" < TIMESTAMPADD(DAY, -10, UTC_TIMESTAMP())'
                                  );
////////// 2. *** Check for num-moves-made files that were last modified more than a week ago, and delete them if the corresponding game has finished.
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
        break;
    case 2:
////////// 3. *** Refresh the data in table "Metadatum".
////////// 4. *** Recalculate player non-rating statistics, and then calculate completed-game ranks.
        if ( MAINTENANCE_DISABLED ) {
            $OutputLines[] = 'Not recalculating site statistics and player non-rating statistics: maintenance is currently disabled.';
        } else {
            dbquery(DBQUERY_WRITE, 'CALL "Maintenance_RefreshMetadata"()');
            sleep(2); // Be nice
            dbquery(DBQUERY_WRITE, 'CALL "CalculatePlayerStats"()');
            sleep(2); // Be nice
            dbquery(DBQUERY_WRITE, 'CALL "CalculateRanks_GamesCompleted"()');
            $OutputLines[] = 'Successfully recalculated site statistics and player non-rating statistics.';
        }
        break;
    case 3:
////////// 5. ANALYZE tables "Game", "PlayerGameRcd" and "User".
        dbquery(DBQUERY_WRITE, 'ANALYZE TABLE "Game", "PlayerGameRcd", "User"');
        $OutputLines[] = 'ANALYZEd the tables "Game", "PlayerGameRcd" and "User"';
        break;
    case 6:
////////// 6. OPTIMIZE tables "Game", "PlayerGameRcd", "User" and "ChosenTranslatedPhrase".
        dbquery(DBQUERY_WRITE, 'OPTIMIZE TABLE "Game", "PlayerGameRcd", "User", "ChosenTranslatedPhrase"');
        $OutputLines[] = 'OPTIMIZEd the tables "Game", "PlayerGameRcd", "User" and "ChosenTranslatedPhrase"';
        break;
    case 5:
        $OutputLines[] = 'This job is not meant to be run on a Friday!';
        break;
    default:
        $OutputLines[] = 'This job is not meant to be run on a Sunday!';
}

dbquery( DBQUERY_WRITE,
         'INSERT INTO "MaintenanceRecord" ("ScriptName", "RunDate", "MaintenanceDisabled") VALUES (\'mpd\', UTC_TIMESTAMP(), :md:)',
         'md' , MAINTENANCE_DISABLED
         );
dbquery( DBQUERY_WRITE,
         'INSERT INTO "RecentEventLog" ("EventType", "EventTime") VALUES (\'Maintenance\', UTC_TIMESTAMP())'
         );

echo implode("\n\n", $OutputLines);

?>