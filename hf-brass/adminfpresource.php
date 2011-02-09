<?php

function AdminFP (tagtree &$tt) {
    global $Administrator;

    $queries = array( 'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'User\' AND "EventTime" > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'User\' AND "EventTime" > TIMESTAMPADD(HOUR, -72, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Message\' AND "EventTime" > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Message\' AND "EventTime" > TIMESTAMPADD(HOUR, -72, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Game\' AND "EventTime" > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Game\' AND "EventTime" > TIMESTAMPADD(HOUR, -72, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Email\' AND "EventTime" > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Email\' AND "EventTime" > TIMESTAMPADD(HOUR, -72, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Maintenance\' AND "EventTime" > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())',
                      'SELECT COUNT(*) AS "Co" FROM "RecentEventLog" WHERE "EventType" = \'Maintenance\' AND "EventTime" > TIMESTAMPADD(HOUR, -72, UTC_TIMESTAMP())',
                      );
    $minor_warning_boundaries_above = array(10, 25, 100, 250, 30,  75,  8, 20, 110, 330);
    $major_warning_boundaries_above = array(15, 37, 150, 375, 45, 112, 12, 30, 112, 336);
    $minor_warning_boundaries_below = array( 0,  0,   0,   0,  0,   0,  0,  0, 109, 328);
    $major_warning_boundaries_below = array( 0,  0,   0,   0,  0,   0,  0,  0, 107, 326);
    for ($i=0; $i<count($queries); $i++) {
        $width = $i ? null : 'width=40 ';
        $stats[$i] = dbquery(DBQUERY_READ_INTEGER, $queries[$i]);
        if ( $stats[$i] > $minor_warning_boundaries_above[$i] or
             $stats[$i] < $minor_warning_boundaries_below[$i]
             ) {
            $alerts[$i] = $width.'class="hugealert"';
        } else if ( $stats[$i] > $major_warning_boundaries_above[$i] or
                    $stats[$i] < $major_warning_boundaries_below[$i]
                    ) {
            $alerts[$i] = $width.'class="bigalert"';
        } else {
            $alerts[$i] = $width;
        }
    }

    if ( $Administrator == 2 ) {
        $tt->opennode('p');
        $tt->leaf( 'a',
                   'SQL Query Page',
                   'href="http://orderofthehammer.com/query.php"'
                   );
        $tt->text('/');
        $tt->leaf( 'a',
                   'SQL squasher',
                   'href="sql_squash.php"'
                   );
        $tt->text('/');
        $tt->leaf( 'a',
                   'Manage Procedures',
                   'href="manageprocedures.php"'
                   );
        $tt->emptyleaf('br');
        $tt->leaf( 'a',
                   'Specification Converter',
                   'href="spec_convert.php"'
                   );
        $tt->text('/');
        $tt->leaf( 'a',
                   'Board Preview',
                   'href="gvpreview.php"'
                   );
        $tt->text('/');$tt->leaf( 'a',
                   'To Do list',
                   'href="todolist.php"'
                   );
        $tt->text('/');
        $tt->leaf( 'a',
                   'Password Generator',
                   'href="pwgen.php"'
                   );
        $tt->closenode(); // p
    }
    $tt->opennode( 'table',
                   'class="table_no_borders" style="text-align: left;"'
                   );

    $tt->opennode('tr');
    $tt->leaf('td', 'New Users in last 24 hours:', 'align=right');
    $tt->leaf('td', '', 'width=10');
    $tt->leaf('td', $stats[0], $alerts[0]);
    $tt->leaf('td', 'and in last 72 hours:');
    $tt->leaf('td', '', 'width=10');
    $tt->leaf('td', $stats[1], $alerts[1]);

    $tt->next();
    $tt->leaf('td', 'New Messages in last 24 hours:', 'align=right');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[2], $alerts[2]);
    $tt->leaf('td', 'and in last 72 hours:');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[3], $alerts[3]);

    $tt->next();
    $tt->leaf('td', 'New Games in last 24 hours:', 'align=right');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[4], $alerts[4]);
    $tt->leaf('td', 'and in last 72 hours:');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[5], $alerts[5]);

    $tt->next();
    $tt->leaf('td', 'New <a href="emails.php?emailtype=3">Emails</a> in last 24 hours:', 'align=right');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[6], $alerts[6]);
    $tt->leaf('td', 'and in last 72 hours:');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[7], $alerts[7]);

    $tt->next();
    $tt->leaf('td', 'New maintenance logs in last 24 hours:', 'align=right');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[8], $alerts[8]);
    $tt->leaf('td', 'and in last 72 hours:');
    $tt->leaf('td', '');
    $tt->leaf('td', $stats[9], $alerts[9]);

    $tt->closenode(2); // tr, table
}

?>