<?php
require('_std-include.php');

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You must be logged in in order to start a new game. Please return to the <a href="index.php">Main Page</a>.'
                   );
    $mypage->finish();
}
if ( !$Administrator ) {
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to make use of this page. Please click <a href="index.php">here</a> to return to the Main Page'.
                       ( ( @$_SESSION['LoggedIn'] and !$Banned ) ?
                         ', or <a href="newgame.php">here</a> to visit the regular New Game page.' :
                         '.'
                         )
                   );
    $mypage->finish();
}

$errors = false;
$FormTimeLimitAMins  = '';
$FormTimeLimitAHours = '';
$FormTimeLimitADays  = '';
$FormTimeLimitBMins  = '';
$FormTimeLimitBHours = '';
$FormTimeLimitBDays  = '';

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

if ( @$_POST['FormSubmitted'] ) {
    $ShowForm  = false;
    $errorlist = fragment::blank();
    $EscapedGameName = sanitise_str( @$_POST['GameName'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE |
                                         STR_CONVERT_ESCAPE_SEQUENCES
                                     );
    $EscapedGamePassword = sanitise_str( @$_POST['GamePassword'],
                                         STR_GPC |
                                             STR_ESCAPE_HTML |
                                             STR_STRIP_TAB_AND_NEWLINE
                                         );
    $EscapedGVersion   = sanitise_int(@$_POST['GameVersion']);
    $EscapedTimeLimitA = sanitise_int(@$_POST['TimeLimitANumber']);
    $EscapedTimeLimitB = sanitise_int(@$_POST['TimeLimitBNumber']);
    $EscapedMinPlayers = sanitise_int(@$_POST['MinPlayers']);
    $EscapedMaxPlayers = sanitise_int(@$_POST['MaxPlayers']);
    $EscapedPlayers = array( sanitise_int(@$_POST['PlayerA']),
                             sanitise_int(@$_POST['PlayerB']),
                             sanitise_int(@$_POST['PlayerC']),
                             sanitise_int(@$_POST['PlayerD']),
                             0
                             );
    if ( $EscapedGVersion == 2 ) { $EscapedGVersion = 1; }
    if ( @$_POST['GPrivate'] ) { $Private = 1; }
    else                       { $Private = 0; }

    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "GameVersion"."GVAdminOnly", "GameVersion"."MinimumPlayersAllowed", "GameVersion"."MaximumPlayersAllowed", "GameVersionAuth"."User" FROM "GameVersion" LEFT JOIN "GameVersionAuth" ON "GameVersion"."VersionID" = "GameVersionAuth"."Version" AND "GameVersionAuth"."User" = :user: WHERE "VersionID" = :version:',
                   'user'    , $_SESSION['MyUserID'] ,
                   'version' , $EscapedGVersion
                   );
    if ( $QR === 'NONE' ) { die($readerrormessage); }
    if ( $GamesStartedCounter != $_POST['GamesStarted'] ) {
        $mypage->title_body('Check integer mismatch');
        $mypage->leaf( 'p',
                       'It looks like you might be inadvertently attempting to double-post your game. Please return to the <a href="index.php">Main Page</a>.'
                       );
        $mypage->finish();
    }
    if ( $QR['GVAdminOnly'] and
         !$Administrator and
         is_null($QR['User'])
         ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'That game version is not available to non-Administrators.'
                          );
    }
    if ( $EscapedMinPlayers < $QR['MinimumPlayersAllowed'] ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The chosen game version requires at least '.
                              $QR['MinimumPlayersAllowed'].
                              ' players.'
                          );
    }
    if ( $EscapedMaxPlayers > $QR['MaximumPlayersAllowed'] ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The chosen game version does not allow more than '.
                              $QR['MaximumPlayersAllowed'].
                              ' players.'
                          );
    }
    if ( mb_strlen($EscapedGameName, 'UTF-8') > 50 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The game name you entered is too long. Maximum 50 characters. (Please note that some special characters "count as" more than one character for this purpose, as they have to be stored as a longer sequence of characters.)'
                          );
    }
    if ( mb_strlen($EscapedGameName, 'UTF-8') < 6 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The game name you entered is too short. It should be at least 6 characters long.'
                          );
    }
    if ( !strlen($EscapedGamePassword) ) {
        $CharArray = 'abcdefghijklmnopqrstuvwxyz0123456789-~,!';
        for ($p=0; $p<20; $p++) {
            $q = rand(0, 39);
            $EscapedGamePassword .= $CharArray[$q];
        }
    }
    if ( $Private and mb_strlen($EscapedGamePassword, 'UTF-8') < 3 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The game password you entered is too short. (If you don\'t care about the password, leave the "password" field blank.)'
                          );
    }
    if ( mb_strlen($EscapedGamePassword, 'UTF-8') > 20 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The game password you entered is too long. Maximum 20 characters. (Please note that some special characters "count as" more than one character for this purpose, as they have to be stored as a longer sequence of characters.)'
                          );
    }
    if ( $EscapedMinPlayers > $EscapedMaxPlayers ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Minimum and maximum numbers of players are the wrong way around!'
                          );
    }
    $searcharraya = array( 'RYGPW', 'RYGWP', 'RYPGW', 'RYPWG',
                           'RYWGP', 'RYWPG', 'RGYPW', 'RGYWP',
                           'RGPYW', 'RGPWY', 'RGWYP', 'RGWPY',
                           'RPYGW', 'RPYWG', 'RPGYW', 'RPGWY',
                           'RPWYG', 'RPWGY', 'RWYGP', 'RWYPG',
                           'RWGYP', 'RWGPY', 'RWPYG', 'RWPGY',
                           'Random'
                           );
    $searcharrayc = array('NTW', 'NTDG', 'NTBO', 'NTBODG', 'NR');
    if ( $EscapedMinPlayers < 2 or
         $EscapedMinPlayers > 4 or
         $EscapedMaxPlayers < 2 or
         $EscapedMaxPlayers > 4 or
         !in_array(@$_POST['InitialTurnOrder'], $searcharraya) or
         !in_array(@$_POST['TalkRules'], $searcharrayc) or
         $EscapedTimeLimitA < 1 or
         $EscapedTimeLimitA > 120 or
         $EscapedTimeLimitB < 1 or
         $EscapedTimeLimitB > 120
         ) {
        die($unexpectederrormessage);
    }
    if ( @$_POST['TimeLimitAUnits'] == 'minutes' and
         $EscapedTimeLimitA < 15
         ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'That\'s too small for Time Limit A.'
                          );
    }
    if ( @$_POST['TimeLimitAUnits'] == 'days' and
         $EscapedTimeLimitA > 40
         ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'That\'s too large for Time Limit A.'
                          );
    }
    if ( @$_POST['TimeLimitBUnits'] == 'minutes' and
         $EscapedTimeLimitB < 30
         ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'That\'s too small for Time Limit B.'
                          );
    }
    if ( @$_POST['TimeLimitBUnits'] == 'days' and
         $EscapedTimeLimitB > 40
         ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'That\'s too large for Time Limit B.'
                          );
    }

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    $NumValidPlayers = 0;
    $NVPErrors       = true;
    for ($i=0; $i<4; $i++) {
        if ( $EscapedPlayers[$i] ) {
            $qry = dbquery( DBQUERY_READ_INTEGER_TOLERANT_NONE,
                            'SELECT "UserID" FROM "User" WHERE "UserID" = :userid:',
                            'userid' , $EscapedPlayers[$i]
                            );
            if ( $qry === 'NONE' ) {
                $labels = array('1st', '2nd', '3rd', '4th', '5th');
                $errors = true;
                $errorlist->leaf( 'li',
                                  'You seem to have made a mistake entering user ID numbers. First problem detected at the '.
                                      $labels[$i].
                                      ' user ID number field.'
                                  );
                $NVPErrors = false;
                break;
            } else {
                $NumValidPlayers++;
            }
        }
    }
    if ( $NVPErrors and !$NumValidPlayers ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Please specify at least one player to play the game.'
                          );
    }
    if ( $NumValidPlayers > $EscapedMaxPlayers ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Too many players specified! Please either remove players or increase the maximum number of players.'
                          );
    }
    $EPCopy = $EscapedPlayers;
    rsort($EPCopy);
    while ( in_array(0, $EPCopy) ) { array_pop($EPCopy); }
    $checkduplicatearray = array();
    for ($i=0; $i<count($EPCopy)-1; $i++) {
        $checkduplicatearray[] = $EPCopy[$i] - $EPCopy[$i+1];
    }
    if ( in_array(0, $checkduplicatearray) ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Duplicated user IDs - please make sure all of the user IDs you enter are unique.'
                          );
    }

    if ( $errors ) {
        $ShowForm = true;
        switch ( @$_POST['TimeLimitAUnits'] ) {
            case 'minutes': $FormTimeLimitAMins  = ' selected'; break;
            case 'hours':   $FormTimeLimitAHours = ' selected'; break;
            default:        $FormTimeLimitADays  = ' selected';
        }
        switch ( @$_POST['TimeLimitBUnits'] ) {
            case 'minutes': $FormTimeLimitBMins  = ' selected'; break;
            case 'hours':   $FormTimeLimitBHours = ' selected'; break;
            default:        $FormTimeLimitBDays  = ' selected';
        }
        $FormTimeLimitA = $EscapedTimeLimitA;
        $FormTimeLimitB = $EscapedTimeLimitB;
    }
} else {
    $ShowForm            = true;
    $EscapedGameName     = '';
    $EscapedGamePassword = '';
    $FormTimeLimitA      = 7;
    $FormTimeLimitADays  = ' selected';
    $FormTimeLimitB      = 7;
    $FormTimeLimitBDays  = ' selected';
    $EscapedPlayers      = array('', '', '', '', '');
}

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

if ( $ShowForm ) {
    for ($i=0; $i<5; $i++) {
        if ( $EscapedPlayers[$i] == 0 ) {
            $EscapedPlayers[$i] = '';
        }
    }

    if ( $Administrator ) {
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "GameVersion"."VersionID", "GameVersionGroup"."VersionName", "GameVersion"."VersionNameSuffix", "GameVersion"."Creators", "GameVersion"."MinimumPlayersAllowed", "GameVersion"."MaximumPlayersAllowed" FROM "GameVersion" JOIN "GameVersionGroup" ON "GameVersion"."VersionGroup" = "GameVersionGroup"."VersionGroupID"'
                       );
    } else {
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "GameVersion"."VersionID", "GameVersionGroup"."VersionName", "GameVersion"."VersionNameSuffix", "GameVersion"."Creators", "GameVersion"."MinimumPlayersAllowed", "GameVersion"."MaximumPlayersAllowed" FROM "GameVersion" LEFT JOIN "GameVersionAuth" ON "GameVersion"."VersionID" = "GameVersionAuth"."Version" AND "GameVersionAuth"."User" = :user: JOIN "GameVersionGroup" ON "GameVersion"."VersionGroup" = "GameVersionGroup"."VersionGroupID" WHERE "GameVersion"."GVAdminOnly" = 0 OR "GameVersionAuth"."User" IS NOT NULL',
                       'user' , $_SESSION['MyUserID']
                       );
    }
    if ( $QR === 'NONE' ) {
        myerror( $unexpectederrormessage,
                 'No game versions to display',
                 MYERROR_NOT_IN_FUNCTION
                 );
    }
    $VersionString = fragment::blank();
    while ( $row = db_fetch_assoc($QR) ) {
        $VersionString->leaf( 'option',
                              vname($row['VersionName'], $row['VersionNameSuffix']).
                                  ' ('.
                                  $row['Creators'].
                                  ') ('.
                                  $row['MinimumPlayersAllowed'].
                                  '-'.
                                  $row['MaximumPlayersAllowed'].
                                  ')',
                              'value='.
                                  $row['VersionID']
                              );
    }

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    $mypage->title_body('Create new game for others to play');
    $mypage->loginbox();
    $mypage->leaf('h1', 'Create new game for others to play');
    if ( $errors ) {
        $mypage->leaf( 'p',
                       'There were some problems with the settings you entered:'
                       );
        $mypage->opennode('ul');
        $mypage->append($errorlist);
        $mypage->closenode(); // ul
    }
    $mypage->leaf( 'p',
                   'Please note: This page will not make use of your default game settings, but instead has its own fixed defaults.'
                   );
    $mypage->opennode( 'form',
                       'action="newgameb.php" method="POST"'
                       );
    $mypage->opennode( 'table',
                       'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                       );
    $mypage->opennode('tr');
    $mypage->leaf( 'td',
                   'Game name (6-50 characters, <b>not</b> blank):',
                   'width=290 align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="text" name="GameName" size=20 maxlength=50 value="'.
                       $EscapedGameName.
                       '">'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Private game?',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="GPrivate" value=1 checked>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Password (3-20 characters <b>or</b> blank):',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="text" name="GamePassword" size=20 maxlength=20 value="'.
                       $EscapedGamePassword.
                       '">'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '(If no password is entered, a password will be generated automatically, regardless of whether the "private game" option is enabled. This password is irrelevant if the "private game" option is not enabled; it can later be changed if necessary.)',
                   'class="font_serif"'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Game version:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="GameVersion"');
    $mypage->append($VersionString);
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Game can be started by any player?',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="APS" value=1>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Game should start automatically upon reaching its maximum number of players',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="AutoStart" value=1>'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '(Note: The game will not start automatically if it is created with the maximum number of players. If you do this then you, or one of the players if appropriate, will still have to start the game manually.)',
                   'class="font_serif"'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   '"Friendly" game?',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->emptyleaf( 'input',
                        'type="checkbox" name="Friendly" value=1'
                        );
    $mypage->text('(<i><a href="http://orderofthehammer.com/credits.htm#friendly">What does this mean?</a></i>)');
    $mypage->closenode(); // td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Minimum number of players:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="MinPlayers"');
    $mypage->leaf('option', '2', 'value=2');
    $mypage->leaf('option', '3', 'value=3');
    $mypage->leaf('option', '4', 'value=4 selected');
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Maximum number of players:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="MaxPlayers"');
    $mypage->leaf('option', '2', 'value=2');
    $mypage->leaf('option', '3', 'value=3');
    $mypage->leaf('option', '4', 'value=4 selected');
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Forbid Canal Link to Scotland?',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="ForbidSC" value=1>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Permit reverse use of the Virtual Connection?',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->emptyleaf( 'input',
                        'type="checkbox" name="ReverseVC" value=1'
                        );
    $mypage->text('(<i><a href="http://orderofthehammer.com/credits.htm#variants">What do these three options do?</a></i>)');
    $mypage->closenode(); // td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Modified Coal Overbuild Rules?',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="ModifiedOverbuild" value=1>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Initial Turn Order:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="InitialTurnOrder"');
    $mypage->leaf( 'option',
                   'Random',
                   'value="Random" selected'
                   );
    $mypage->leaf('option', 'Red, Yellow, Green, Purple, Grey', 'value="RYGPW"');
    $mypage->leaf('option', 'Red, Yellow, Green, Grey, Purple', 'value="RYGWP"');
    $mypage->leaf('option', 'Red, Yellow, Purple, Green, Grey', 'value="RYPGW"');
    $mypage->leaf('option', 'Red, Yellow, Purple, Grey, Green', 'value="RYPWG"');
    $mypage->leaf('option', 'Red, Yellow, Grey, Green, Purple', 'value="RYWGP"');
    $mypage->leaf('option', 'Red, Yellow, Grey, Purple, Green', 'value="RYWPG"');
    $mypage->leaf('option', 'Red, Green, Yellow, Purple, Grey', 'value="RGYPW"');
    $mypage->leaf('option', 'Red, Green, Yellow, Grey, Purple', 'value="RGYWP"');
    $mypage->leaf('option', 'Red, Green, Purple, Yellow, Grey', 'value="RGPYW"');
    $mypage->leaf('option', 'Red, Green, Purple, Grey, Yellow', 'value="RGPWY"');
    $mypage->leaf('option', 'Red, Green, Grey, Yellow, Purple', 'value="RGWYP"');
    $mypage->leaf('option', 'Red, Green, Grey, Purple, Yellow', 'value="RGWPY"');
    $mypage->leaf('option', 'Red, Purple, Yellow, Green, Grey', 'value="RPYGW"');
    $mypage->leaf('option', 'Red, Purple, Yellow, Grey, Green', 'value="RPYWG"');
    $mypage->leaf('option', 'Red, Purple, Green, Yellow, Grey', 'value="RPGYW"');
    $mypage->leaf('option', 'Red, Purple, Green, Grey, Yellow', 'value="RPGWY"');
    $mypage->leaf('option', 'Red, Purple, Grey, Yellow, Green', 'value="RPWYG"');
    $mypage->leaf('option', 'Red, Purple, Grey, Green, Yellow', 'value="RPWGY"');
    $mypage->leaf('option', 'Red, Grey, Yellow, Green, Purple', 'value="RWYGP"');
    $mypage->leaf('option', 'Red, Grey, Yellow, Purple, Green', 'value="RWYPG"');
    $mypage->leaf('option', 'Red, Grey, Green, Yellow, Purple', 'value="RWGYP"');
    $mypage->leaf('option', 'Red, Grey, Green, Purple, Yellow', 'value="RWGPY"');
    $mypage->leaf('option', 'Red, Grey, Purple, Yellow, Green', 'value="RWPYG"');
    $mypage->leaf('option', 'Red, Grey, Purple, Green, Yellow', 'value="RWPGY"');
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Rules on Talking:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="TalkRules"');
    $mypage->leaf('option', 'No Talk Whatsoever'              , 'value="NTW"'        );
    $mypage->leaf('option', 'No Talk During Game'             , 'value="NTDG"'       );
    $mypage->leaf('option', 'No Talk By Outsiders'            , 'value="NTBO"'       );
    $mypage->leaf('option', 'No Talk By Outsiders During Game', 'value="NTBODG"'     );
    $mypage->leaf('option', 'No Restrictions'                 , 'value="NR" selected');
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Time Limit A:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="TimeLimitANumber"');
    for ($i=1; $i<121; $i++) {
        $mypage->leaf( 'option',
                       $i,
                       'value='.$i.
                           ( ( $i == $FormTimeLimitA ) ?
                             ' selected' :
                             ''
                             )
                       );
    }
    $mypage->closenode(); // select
    $mypage->opennode('select', 'name="TimeLimitAUnits"');
    $mypage->leaf( 'option',
                   'minutes',
                   'value="minutes"'.$FormTimeLimitAMins
                   );
    $mypage->leaf( 'option',
                   'hours',
                   'value="hours"'.$FormTimeLimitAHours
                   );
    $mypage->leaf( 'option',
                   'days',
                   'value="days"'.$FormTimeLimitADays
                   );
    $mypage->closenode(); // select
    $mypage->leaf( 'i',
                   '(If days: at most 40, if minutes: at least 15)'
                   );
    $mypage->closenode(); // td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Time Limit B:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="TimeLimitBNumber"');
    for ($i=1; $i<121; $i++) {
        $mypage->leaf( 'option',
                       $i,
                       'value='.$i.
                           ( ( $i == $FormTimeLimitB ) ?
                             ' selected' :
                             ''
                             )
                       );
    }
    $mypage->closenode(); // select
    $mypage->opennode('select', 'name="TimeLimitBUnits"');
    $mypage->leaf( 'option',
                   'minutes',
                   'value="minutes"'.$FormTimeLimitBMins
                   );
    $mypage->leaf( 'option',
                   'hours',
                   'value="hours"'.$FormTimeLimitBHours
                   );
    $mypage->leaf( 'option',
                   'days',
                   'value="days"'.$FormTimeLimitBDays
                   );
    $mypage->closenode(); // select
    $mypage->leaf( 'i',
                   '(If days: at most 40, if minutes: at least 30)'
                   );
    $mypage->closenode(); // td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Behaviour at Time Limit B:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="DoWhatAtB"');
    $mypage->leaf( 'option',
                   'Downsize',
                   'value=0'
                   );
    $mypage->leaf( 'option',
                   'Abort',
                   'value=1'
                   );
    $mypage->leaf( 'option',
                   'Kick current player; subsequently downsize',
                   'value=2'
                   );
    $mypage->leaf( 'option',
                   'Kick current player; subsequently abort',
                   'value=3 selected'
                   );
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'User ID numbers of players:',
                   'rowspan=4 align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="text" name="PlayerA" size=10 maxlength=10 value="'.
                       $EscapedPlayers[0].
                       '">'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   '<input type="text" name="PlayerB" size=10 maxlength=10 value="'.
                       $EscapedPlayers[1].
                       '">'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   '<input type="text" name="PlayerC" size=10 maxlength=10 value="'.
                       $EscapedPlayers[2].
                       '">'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   '<input type="text" name="PlayerD" size=10 maxlength=10 value="'.
                       $EscapedPlayers[3].
                       '">'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '<input type="submit" value="Go!">'
                   );
    $mypage->closenode(2); // tr, table
    $mypage->emptyleaf( 'input',
                        'type="hidden" name="FormSubmitted" value=1'
                        );
    $mypage->emptyleaf( 'input',
                        'type="hidden" name="GamesStarted" value='.
                            $GamesStartedCounter
                        );
    $mypage->closenode(); // form
    $mypage->leaf( 'p',
                   'Or, return to the <a href="index.php">Main Page</a>.'
                   );
    $mypage->finish();

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

} else {
    if ( $GamesStartedCounter < 255 ) {
        $NewGamesStarted = $GamesStartedCounter + 1;
    } else {
        $NewGamesStarted = 0;
    }
    dbquery( DBQUERY_WRITE,
             'UPDATE "User" SET "GamesStartedCounter" = :gamesstarted: WHERE "UserID" = :userid:',
             'gamesstarted' , $NewGamesStarted      ,
             'userid'       , $_SESSION['MyUserID']
             );

    $SpecialRulesText = 0;
    if ( @$_POST['ReverseVC'] )         { $SpecialRulesText  = 1; }
    if ( @$_POST['ForbidSC'] )          { $SpecialRulesText += 2; }
    if ( @$_POST['ModifiedOverbuild'] ) { $SpecialRulesText += 4; }
    if ( @$_POST['Friendly'] ) { $FriendlyText = 1; }
    else                       { $FriendlyText = 0; }
    if ( @$_POST['AutoStart'] ) { $AutoStartText = 1; }
    else                        { $AutoStartText = 0; }
    if ( @$_POST['TimeLimitAUnits'] == 'hours' )     { $EscapedTimeLimitA *=   60; }
    else if ( @$_POST['TimeLimitAUnits'] == 'days' ) { $EscapedTimeLimitA *= 1440; }
    if ( @$_POST['TimeLimitBUnits'] == 'hours' )     { $EscapedTimeLimitB *=   60; }
    else if ( @$_POST['TimeLimitBUnits'] == 'days' ) { $EscapedTimeLimitB *= 1440; }
    switch ( @$_POST['TalkRules'] ) {
        case 'NTW':    $TalkRulesText = 'No Talk Whatsoever';               break;
        case 'NTDG':   $TalkRulesText = 'No Talk During Game';              break;
        case 'NTBO':   $TalkRulesText = 'No Talk By Outsiders';             break;
        case 'NTBODG': $TalkRulesText = 'No Talk By Outsiders During Game'; break;
        default:       $TalkRulesText = 'No Restrictions';
    }
    switch ( $_POST['InitialTurnOrder'] ) {
        case 'RYGPW': $ITOText = 'Red, Yellow, Green, Purple, Grey'; break;
        case 'RYGWP': $ITOText = 'Red, Yellow, Green, Grey, Purple'; break;
        case 'RYPGW': $ITOText = 'Red, Yellow, Purple, Green, Grey'; break;
        case 'RYPWG': $ITOText = 'Red, Yellow, Purple, Grey, Green'; break;
        case 'RYWGP': $ITOText = 'Red, Yellow, Grey, Green, Purple'; break;
        case 'RYWPG': $ITOText = 'Red, Yellow, Grey, Purple, Green'; break;
        case 'RGYPW': $ITOText = 'Red, Green, Yellow, Purple, Grey'; break;
        case 'RGYWP': $ITOText = 'Red, Green, Yellow, Grey, Purple'; break;
        case 'RGPYW': $ITOText = 'Red, Green, Purple, Yellow, Grey'; break;
        case 'RGPWY': $ITOText = 'Red, Green, Purple, Grey, Yellow'; break;
        case 'RGWYP': $ITOText = 'Red, Green, Grey, Yellow, Purple'; break;
        case 'RGWPY': $ITOText = 'Red, Green, Grey, Purple, Yellow'; break;
        case 'RPYGW': $ITOText = 'Red, Purple, Yellow, Green, Grey'; break;
        case 'RPYWG': $ITOText = 'Red, Purple, Yellow, Grey, Green'; break;
        case 'RPGYW': $ITOText = 'Red, Purple, Green, Yellow, Grey'; break;
        case 'RPGWY': $ITOText = 'Red, Purple, Green, Grey, Yellow'; break;
        case 'RPWYG': $ITOText = 'Red, Purple, Grey, Yellow, Green'; break;
        case 'RPWGY': $ITOText = 'Red, Purple, Grey, Green, Yellow'; break;
        case 'RWYGP': $ITOText = 'Red, Grey, Yellow, Green, Purple'; break;
        case 'RWYPG': $ITOText = 'Red, Grey, Yellow, Purple, Green'; break;
        case 'RWGYP': $ITOText = 'Red, Grey, Green, Yellow, Purple'; break;
        case 'RWGPY': $ITOText = 'Red, Grey, Green, Purple, Yellow'; break;
        case 'RWPYG': $ITOText = 'Red, Grey, Purple, Yellow, Green'; break;
        case 'RWPGY': $ITOText = 'Red, Grey, Purple, Green, Yellow'; break;
        default:      $ITOText = 'Random';
    }
    if ( @$_POST['APS'] ) { $APSText = 1; }
    else                  { $APSText = 0; }
    switch ( @$_POST['DoWhatAtB'] ) {
        case 1:  $DoWhatAtB = 'Abort';                                      break;
        case 2:  $DoWhatAtB = 'Kick current player; subsequently downsize'; break;
        case 3:  $DoWhatAtB = 'Kick current player; subsequently abort';    break;
        default: $DoWhatAtB = 'Downsize';
    }

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    $PlayersPreferringColour = array(array(), array(), array(), array(), array());
    $PlayersWithNoPreference = array();
    $AvailableColours        = array();
    $ColoursPlayed = array( array(0, 0, 0, 0, 0),
                            array(0, 0, 0, 0, 0),
                            array(0, 0, 0, 0, 0),
                            array(0, 0, 0, 0, 0),
                            array(0, 0, 0, 0, 0)
                            );
    $GamesPlayed      = array(0, 0, 0, 0, 0);
    $TheRealPlayers   = array();
    $PlayerUnassigned = array(1, 1, 1, 1, 1);
    $PlayersToSend    = array();
    $PlayerExistsText = '00000';
    for ($i=0; $i<5; $i++) {
        if ( $EscapedPlayers[$i] ) {
            $TheRealPlayers[] = $EscapedPlayers[$i];
        }
    }
    $CurrentPlayers    = count($TheRealPlayers);
    $RealPlayersString = implode(', ',$TheRealPlayers);
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   'SELECT COUNT(*) AS "Co", "PlayerGameRcd"."Colour", "PlayerGameRcd"."User" FROM "PlayerGameRcd" JOIN "Game" ON "PlayerGameRcd"."Game" = "Game"."GameID" WHERE "PlayerGameRcd"."User" IN ('.$RealPlayersString.') AND "PlayerGameRcd"."Inherited" = 0 AND "Game"."LastMove" > TIMESTAMPADD(MONTH, -6, UTC_TIMESTAMP()) GROUP BY "PlayerGameRcd"."User", "PlayerGameRcd"."Colour"'
                   );
    while ( $row = db_fetch_assoc($QR) ) {
        for ($i=0; $i<$CurrentPlayers; $i++) {
            if ( $row['User'] == $TheRealPlayers[$i] ) {
                $ColoursPlayed[$i][$row['Colour']] = $row['Co'];
                $GamesPlayed[$i] += $row['Co'];
            }
        }
    }
    for ($i=0; $i<$CurrentPlayers; $i++) {
        if ( !$GamesPlayed[$i] ) {
            $GamesPlayed[$i] = 1;
        }
        for ($j=0; $j<5; $j++) {
            $Proportions[$j] = $ColoursPlayed[$i][$j]/$GamesPlayed[$i];
        }
        $ColourArray = array(0, 1, 2, 3, 4);
        array_multisort($Proportions, SORT_DESC, $ColourArray);
        if ( $Proportions[0] > 2*$Proportions[1] ) {
            $PlayersPreferringColour[$ColourArray[0]][] = $i;
        } else {
            $PlayersWithNoPreference[] = $i;
        }
    }
    for ($i=0; $i<5; $i++) {
        while ( count($PlayersPreferringColour[$i]) > 1 ) {
            shuffle($PlayersPreferringColour[$i]);
            $PlayersWithNoPreference[] = array_pop($PlayersPreferringColour[$i]);
        }
        if ( count($PlayersPreferringColour[$i]) ) {
            $PlayersToSend[] = array( $TheRealPlayers[$PlayersPreferringColour[$i][0]],
                                      $i
                                      );
            $PlayerExistsText[$i] = 1;
            $PlayerUnassigned[$PlayersPreferringColour[$i][0]] = 0;
        } else {
            $AvailableColours[] = $i;
        }
    }
    for ($i=0; $i<$CurrentPlayers; $i++) {
        if ( $PlayerUnassigned[$i] ) {
            shuffle($AvailableColours);
            $j = array_pop($AvailableColours);
            $PlayersToSend[] = array( $TheRealPlayers[$i],
                                      $j
                                      );
            $PlayerExistsText[$j] = 1;
        }
    }

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    dbquery(DBQUERY_START_TRANSACTION);
    $IDNoA = dbquery( DBQUERY_INSERT_ID,
                      'INSERT INTO "GeneralThread" ("Closed", "NumberOfPosts") VALUES (\'Open\', 0)'
                      );
    dbquery( DBQUERY_INSERT_ID,
             'INSERT INTO "Game" ("DoWhatAtB", "GVersion", "SpecialRules", "TalkRules", "GameID", "LastMove", "GameTicker", "GameTickerNames", "PlayerExists", "GameCreator", "GameName", "NumRounds", "TimeLimitA", "TimeLimitB", "Friendly", "CurrentPlayers") VALUES (:dowhatatb:, :gversion:, :specialrules:, :talkrules:, :thread:, UTC_TIMESTAMP(), \'\', \'\', :playerexists:, :gamecreator:, :gamename:, 10, :timelimita:, :timelimitb:, :friendly:, :currentplayers:)',
             'dowhatatb'       , $DoWhatAtB              ,
             'gversion'        , $EscapedGVersion        ,
             'specialrules'    , $SpecialRulesText       ,
             'talkrules'       , $TalkRulesText          ,
             'thread'          , $IDNoA                  ,
             'playerexists'    , $PlayerExistsText       ,
             'gamecreator'     , $_SESSION['MyUserID']   ,
             'gamename'        , $EscapedGameName        ,
             'timelimita'      , $EscapedTimeLimitA      ,
             'timelimitb'      , $EscapedTimeLimitB      ,
             'friendly'        , $FriendlyText           ,
             'currentplayers'  , $CurrentPlayers
             );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "LobbySettings" ("Game", "MinimumPlayers", "MaximumPlayers", "CreationTime", "AutoStart", "AnyPlayerStarts", "InitialTurnOrder", "MinimumRating", "MaximumRating", "GPrivate", "Password") VALUES (:game:, :minplayers:, :maxplayers:, UTC_TIMESTAMP(), :autostart:, :aps:, :ito:, 0, NULL, :private:, :password:)',
             'game'       , $IDNoA               ,
             'minplayers' , $EscapedMinPlayers   ,
             'maxplayers' , $EscapedMaxPlayers   ,
             'autostart'  , $AutoStartText       ,
             'aps'        , $APSText             ,
             'ito'        , $ITOText             ,
             'private'    , $Private             ,
             'password'   , $EscapedGamePassword
             );
    for ($i=0; $i<count($PlayersToSend); $i++) {
        dbquery( DBQUERY_WRITE,
                 'INSERT INTO "PlayerGameRcd" ("User", "Game", "GameResult", "Inherited", "GameCounts", "Colour", "Score", "NumLongTurns", "CurrentOccupant") VALUES (:user:, :game:, \'Playing\', 0, 1, :colour:, 0, 0)',
                 'user'   , $PlayersToSend[$i][0] ,
                 'game'   , $IDNoA                ,
                 'colour' , $PlayersToSend[$i][1]
                 );
    }
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "RecentEventLog" ("EventType", "EventTime", "ExtraData") VALUES (\'Game\', UTC_TIMESTAMP(), :idno:)',
             'idno' , $IDNoA
             );
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'lobby.php?GameID='.$IDNoA,
                    'Game successfully created.'
                    );
}

?>