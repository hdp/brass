<?php
$TrivialPage = true;
$NoSession = 1;
$NoLoginStuff = 1;
define('TEST_MODE', true);
require('_config.php');

$OutputLines = array( 'Cron job mpc.php run at '.date('Y-m-d H:i:s'),
                      'Job frequency: at 3:35am US Central Time every day',
                      'Command: nice -n 19 php -q hf-brass-p/mpc.php'
                      );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 1. Delete users who registered more than 14 days ago and have not validated.

$OutputLines[] = 'Unvalidated users deleted: '.
                 dbquery( DBQUERY_AFFECTED_ROWS,
                          'DELETE FROM "User" WHERE "UserValidated" = 0 AND "RegistrationDate" < TIMESTAMPADD(DAY, -14, UTC_TIMESTAMP())'
                          );

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 2. *** Recalculate all player ratings, and then calculate ranks.

if ( MAINTENANCE_DISABLED ) {
    $OutputLines[] = 'Not recalculating player ratings: maintenance is currently disabled.';
} else {
    dbquery(DBQUERY_WRITE, 'CALL "CalculateAllRatings"()');
    dbquery(DBQUERY_WRITE, 'CALL "CalculateRanks_Rating"()');
    $OutputLines[] = 'Successfully recalculated player ratings.';
}

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

// 3. Delete entries in RecentEventLog that are more than 14 days old.

dbquery( DBQUERY_WRITE,
         'DELETE FROM "RecentEventLog" WHERE "EventTime" < TIMESTAMPADD(DAY, -14, UTC_TIMESTAMP())'
         );
$OutputLines[] = 'Successfully deleted entries in RecentEventLog that were more than 14 days old.';

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

dbquery( DBQUERY_WRITE,
         'INSERT INTO "MaintenanceRecord" ("ScriptName", "RunDate", "MaintenanceDisabled") VALUES (\'mpc\', UTC_TIMESTAMP(), :md:)',
         'md' , MAINTENANCE_DISABLED
         );
dbquery( DBQUERY_WRITE,
         'INSERT INTO "RecentEventLog" ("EventType", "EventTime") VALUES (\'Maintenance\', UTC_TIMESTAMP())'
         );

echo implode("\n\n", $OutputLines);

?>