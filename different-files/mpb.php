<?php
$TrivialPage = true;
$NoSession = 1;
$NoLoginStuff = 1;
define('TEST_MODE', true);
require('_config.php');

$OutputLines = array( 'Cron job mpb.php run at '.date('Y-m-d H:i:s'),
                      'Job frequency: at 5 minutes past every odd hour US Central Time',
                      'Command: nice -n 19 php -q hf-brass-p/mpb.php'
                      );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 1. *** Cancel games that are "Recruiting" and were created more than 7 days ago.

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

// 2. Delete players' Notes in games that finished more than 7 days ago.

$OutputLines[] = 'Player notes deleted: '.
                 dbquery( DBQUERY_AFFECTED_ROWS,
                          'DELETE FROM "PlayerGameNotes" USING "Game" JOIN "PlayerGameNotes" ON "Game"."GameID" = "PlayerGameNotes"."Game" WHERE "Game"."GameIsFinished" = 1 AND "Game"."LastMove" < TIMESTAMPADD(DAY, -7, UTC_TIMESTAMP())'
                          );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 3. Close threads for games that finished between 7 and 10 days ago, where the thread is not set to "Forced Open".
//    --- At every call except 3:05am on Sundays.
// 4. Close threads for games that finished more than 7 days ago, where the thread is not set to "Forced Open".
//    --- Only at 3:05am on Sundays.

date_default_timezone_set('America/Chicago');
if ( (int)date('w') or (int)date('G') != 3 ) {
    $ExtraSQL = ' AND "Game"."LastMove" > TIMESTAMPADD(DAY, -10, UTC_TIMESTAMP())';
} else {
    $ExtraSQL = '';
    $OutputLines[] = 'Checking that ALL old game threads are closed in this run.';
}

$OutputLines[] = 'Game threads closed: '.
                 dbquery( DBQUERY_AFFECTED_ROWS,
                          'UPDATE "Game" JOIN "GeneralThread" ON "Game"."GameID" = "GeneralThread"."ThreadID" SET "GeneralThread"."Closed" = \'Closed\' WHERE "Game"."GameIsFinished" = 1 AND "Game"."LastMove" < TIMESTAMPADD(DAY, -7, UTC_TIMESTAMP())'.$ExtraSQL.' AND "GeneralThread"."Closed" = \'Open\''
                          );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

dbquery( DBQUERY_WRITE,
         'INSERT INTO "MaintenanceRecord" ("ScriptName", "RunDate", "MaintenanceDisabled") VALUES (\'mpb\', UTC_TIMESTAMP(), :md:)',
         'md' , MAINTENANCE_DISABLED
         );
dbquery( DBQUERY_WRITE,
         'INSERT INTO "RecentEventLog" ("EventType", "EventTime") VALUES (\'Maintenance\', UTC_TIMESTAMP())'
         );

echo implode("\n\n", $OutputLines);

?>