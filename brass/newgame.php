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
if ( $Banned ) {
    $mypage->title_body('Banned');
    $mypage->leaf( 'p',
                   'You cannot start a new game, because you are banned. Please return to the <a href="index.php">Main Page</a>.'
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
    $EscapedMinRating = sanitise_int( @$_POST['MinRating'],
                                      SANITISE_NO_FLAGS,
                                      0,
                                      60
                                      );
    $EscapedMaxRating = sanitise_int( @$_POST['MaxRating'],
                                      SANITISE_NO_FLAGS,
                                      0,
                                      60
                                      );
    if ( $EscapedGVersion == 2 ) { $EscapedGVersion = 1; }
    if ( $EscapedMaxRating > 0 and $EscapedMaxRating < 5 ) {
        die($unexpectederrormessage);
    }
    $EscapedMinRating *= 100;
    $EscapedMaxRating *= 100;
    if ( $EscapedMinRating > 0 ) {
        $FriendlyText = 0;
    } else if ( @$_POST['Friendly'] ) {
        $FriendlyText = 1;
    } else {
        $FriendlyText = 0;
    }
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
    if ( $EscapedMinRating > $Rating ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Minimum Rating too high - it cannot be higher than your own rating.'
                          );
    }
    if ( $EscapedMaxRating < $Rating and $EscapedMaxRating != 0 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Maximum Rating too low - it cannot be lower than your own rating.'
                          );
    }
    if ( mb_strlen($EscapedGameName, 'UTF-8') > 50 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The game name you entered is too long. Maximum 50 characters. (Please note that some special characters "count as" more than one character for this purpose, as they have to be stored as a longer sequence of characters.)'
                          );
    }
    if ( $EscapedGameName == '' ) {
        $EscapedGameName = 'Too Lazy';
    }
    if ( mb_strlen($EscapedGameName, 'UTF-8') < 6 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The game name you entered is too short. It should be either completely blank or at least 6 characters long.'
                          );
    }
    if ( $Private and mb_strlen($EscapedGamePassword, 'UTF-8') < 3 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The game password you entered is too short. (If you don\'t want your game to have a password, untick the "Private game" box.)'
                          );
    }
    if ( $Private and
         ( $EscapedMinRating or $EscapedMaxRating )
         ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'You cannot create a private game with a minimum or maximum player rating.'
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
    if ( $EscapedMinRating > $EscapedMaxRating and
         $EscapedMaxRating != 0
         ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Minimum and maximum ratings are the wrong way around!'
                          );
    } else if ( $EscapedMinRating + 1000 > $EscapedMaxRating and
                $EscapedMinRating != 0 and
                $EscapedMaxRating != 0
                ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Minimum and maximum ratings are too close together! If you set both a maximum player rating and a minimum player rating, then they must differ by at least 1000.'
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
    $searcharrayb = array('Red', 'Yellow', 'Green', 'Purple', 'Grey');
    $searcharrayc = array('NTW', 'NTDG', 'NTBO', 'NTBODG', 'NR');
    if ( $EscapedMinPlayers < 2 or
         $EscapedMinPlayers > 4 or
         $EscapedMaxPlayers < 2 or
         $EscapedMaxPlayers > 4 or
         !in_array(@$_POST['InitialTurnOrder'], $searcharraya) or
         !in_array(@$_POST['CreatorColour'], $searcharrayb) or
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
    if ( $DefaultTimeLimitA % 1440 == 0 ) {
        $FormTimeLimitA = $DefaultTimeLimitA / 1440;
        $FormTimeLimitADays = ' selected';
    } else if ( $DefaultTimeLimitA % 60 == 0 ) {
        $FormTimeLimitA = $DefaultTimeLimitA / 60;
        $FormTimeLimitAHours = ' selected';
    } else {
        $FormTimeLimitA = $DefaultTimeLimitA;
        $FormTimeLimitAMins = ' selected';
    }
    if ( $DefaultTimeLimitB % 1440 == 0 ) {
        $FormTimeLimitB = $DefaultTimeLimitB / 1440;
        $FormTimeLimitBDays = ' selected';
    } else if ( $DefaultTimeLimitB % 60 == 0 ) {
        $FormTimeLimitB = $DefaultTimeLimitB / 60;
        $FormTimeLimitBHours = ' selected';
    } else {
        $FormTimeLimitB = $DefaultTimeLimitB;
        $FormTimeLimitBMins = ' selected';
    }
}

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

if ( $ShowForm ) {
    $RatingOptions = fragment::blank();
    $RatingOptions->opennode('tr');
    $RatingOptions->leaf( 'td',
                          'Minimum Player Rating:',
                          'align=right'
                          );
    $RatingOptions->opennode('td');
    if ( $Rating < 100 ) {
        $RatingOptions->emptyleaf( 'input',
                                   'type="hidden" name="MinRating" value=0'
                                   );
        $RatingOptions->text('0 (<i>you can\'t set a minimum rating at present</i>)');
    } else {
        $RatingOptions->opennode('select', 'name="MinRating"');
        for ($i=0; $i<61; $i++) {
            $j = 100 * $i;
            if ( $Rating >= $j ) {
                $RatingOptions->leaf( 'option',
                                      $j,
                                      'value='.$i
                                      );
            }
        }
        $RatingOptions->closenode(); // select
    }
    $RatingOptions->closenode(); // td
    $RatingOptions->next();
    $RatingOptions->leaf( 'td',
                          'Maximum Player Rating:',
                          'align=right'
                          );
    $RatingOptions->opennode('td');
    if ( $Rating > 6000 ) {
        $RatingOptions->emptyleaf( 'input',
                                   'type="hidden" name="MaxRating" value=0'
                                   );
        $RatingOptions->text('n/a (<i>you can\'t set a maximum rating at present</i>)');
    } else {
        $RatingOptions->opennode('select', 'name="MaxRating"');
        $RatingOptions->leaf( 'option',
                              'None',
                              'value=0'
                              );
        for ($i=5; $i<61; $i++) {
            $j = 100 * $i;
            if ( $Rating <= $j ) {
                $RatingOptions->leaf( 'option',
                                      $j,
                                      'value='.$i
                                      );
            }
        }
        $RatingOptions->closenode(); // select
    }
    $RatingOptions->closenode(); // td
    $RatingOptions->next();
    $RatingOptions->leaf('td', '');
    $RatingOptions->leaf( 'td',
                          '(Your rating is '.
                              $Rating.
                              '. You cannot create a private game with a minimum or maximum player rating - if creating a private game, leave these set to "0" and "none" respectively. Your game <b>must</b> be a Competitive game if you set a <b>minimum</b> player rating - the setting of the tickbox option above will be ignored. However, you <b>can</b> create a Friendly game and give it a <b>maximum</b> player rating.)',
                          'class="font_serif"'
                          );
    $RatingOptions->closenode(); // tr

    if ( $Administrator ) {
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "GameVersion"."VersionID", "GameVersionGroup"."VersionName", "GameVersion"."VersionNameSuffix", "GameVersion"."Creators", "GameVersion"."MinimumPlayersAllowed", "GameVersion"."MaximumPlayersAllowed" FROM "GameVersion" JOIN "GameVersionGroup" ON "GameVersion"."VersionGroup" = "GameVersionGroup"."VersionGroupID"'
                       );
    } else {
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "GameVersion"."VersionID", "GameVersionGroup"."VersionName", "GameVersion"."VersionNameSuffix", "GameVersion"."Creators", "GameVersion"."MinimumPlayersAllowed", "GameVersion"."MaximumPlayersAllowed" FROM "GameVersion" LEFT JOIN "GameVersionAuth" JOIN "GameVersionGroup" ON "GameVersion"."VersionGroup" = "GameVersionGroup"."VersionGroupID" ON "GameVersion"."VersionID" = "GameVersionAuth"."Version" AND "GameVersionAuth"."User" = :user: WHERE "GameVersion"."GVAdminOnly" = 0 OR "GameVersionAuth"."User" IS NOT NULL',
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

    if ( $DefaultFriendly ) { $FriendlyChecked = ' checked'; }
    else                    { $FriendlyChecked = '';         }
    if ( $DefaultNoSC ) { $ScotCanalChecked = ' checked'; }
    else                { $ScotCanalChecked = '';         }
    if ( $DefaultRVC ) { $RVCChecked = ' checked'; }
    else               { $RVCChecked = '';         }
    if ( $DefaultORAW ) { $MOverbuildChecked = '';         }
    else                { $MOverbuildChecked = ' checked'; }
    $downsizetext     = '';
    $aborttext        = '';
    $kickdownsizetext = '';
    $kickaborttext    = '';
    switch ( $PreferredDoAtB ) {
        case 0: $downsizetext     = ' selected'; break;
        case 1: $aborttext        = ' selected'; break;
        case 2: $kickdownsizetext = ' selected'; break;
        case 3: $kickaborttext    = ' selected';
    }

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    $mypage->title_body('Create new game');
    $mypage->loginbox();
    $mypage->leaf('h1', 'Create new game');
    if ( $errors ) {
        $mypage->leaf( 'p',
                       'There were some problems with the settings you entered:'
                       );
        $mypage->opennode('ul');
        $mypage->append($errorlist);
        $mypage->closenode(); // ul
    }
    $mypage->opennode( 'form',
                       'action="newgame.php" method="POST"'
                       );
    $mypage->opennode( 'table',
                       'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                       );
    $mypage->opennode('tr');
    $mypage->leaf( 'td',
                   'Game name (6-50 characters <b>or</b> blank):',
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
                   '<input type="checkbox" name="GPrivate" value=1>'
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
                   '(The "Password" field is ignored if the game is not set as "private". If used, the password should be between 3 and 20 characters. Please note that game passwords are stored merely as plain text, and not in encrypted form like user passwords; they are intended only as a simple barrier to joining the game for people to whom you have not given the password.)',
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
                   '<input type="checkbox" name="APS" value=1 checked>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Game should start automatically upon reaching its maximum number of players',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="AutoStart" value=1 checked>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   '"Friendly" game?',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->emptyleaf( 'input',
                        'type="checkbox" name="Friendly" value=1'.
                            $FriendlyChecked
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
    $mypage->leaf('option', '3', 'value=3 selected');
    $mypage->leaf('option', '4', 'value=4');
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
                   '<input type="checkbox" name="ForbidSC" value=1'.
                       $ScotCanalChecked.
                       '>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Permit reverse use of the Virtual Connection?',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->emptyleaf( 'input',
                        'type="checkbox" name="ReverseVC" value=1'.
                            $RVCChecked
                        );
    $mypage->text('(<i><a href="http://orderofthehammer.com/credits.htm#variants">What do these three options do?</a></i>)');
    $mypage->closenode(); // td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Modified Coal Overbuild Rules?',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="ModifiedOverbuild" value=1'.
                       $MOverbuildChecked.
                       '>'
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
                   'I will be:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="CreatorColour"');
    $mypage->leaf('option', 'Red'   , 'value="Red"'   );
    $mypage->leaf('option', 'Yellow', 'value="Yellow"');
    $mypage->leaf('option', 'Green' , 'value="Green"' );
    $mypage->leaf('option', 'Purple', 'value="Purple"');
    $mypage->leaf('option', 'Grey'  , 'value="Grey"'  );
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
                   'value=0'.$downsizetext
                   );
    $mypage->leaf( 'option',
                   'Abort',
                   'value=1'.$aborttext
                   );
    $mypage->leaf( 'option',
                   'Kick current player; subsequently downsize',
                   'value=2'.$kickdownsizetext
                   );
    $mypage->leaf( 'option',
                   'Kick current player; subsequently abort',
                   'value=3'.$kickaborttext
                   );
    $mypage->closenode(3); // select, td, tr
    $mypage->append($RatingOptions);
    $mypage->opennode('tr');
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
    switch ( $_POST['CreatorColour'] ) {
        case 'Red':    $UserCol = 0; $PlayerExistsText = '10000'; break;
        case 'Yellow': $UserCol = 1; $PlayerExistsText = '01000'; break;
        case 'Green':  $UserCol = 2; $PlayerExistsText = '00100'; break;
        case 'Purple': $UserCol = 3; $PlayerExistsText = '00010'; break;
        case 'Grey':   $UserCol = 4; $PlayerExistsText = '00001';
    }
    if ( $EscapedMaxRating == 0 ) {
        $EscapedMaxRating = null;
    }

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    dbquery(DBQUERY_START_TRANSACTION);
    $IDNoA = dbquery( DBQUERY_INSERT_ID,
                      'INSERT INTO "GeneralThread" ("Closed", "NumberOfPosts") VALUES (\'Open\', 0)'
                      );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "Game" ("DoWhatAtB", "GVersion", "SpecialRules", "TalkRules", "GameID", "LastMove", "GameTicker", "GameTickerNames", "PlayerExists", "GameCreator", "GameName", "NumRounds", "TimeLimitA", "TimeLimitB", "Friendly") VALUES (:dowhatatb:, :gversion:, :specialrules:, :talkrules:, :thread:, UTC_TIMESTAMP(), \'\', \'\', :playerexists:, :gamecreator:, :gamename:, 10, :timelimita:, :timelimitb:, :friendly:)',
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
             'friendly'        , $FriendlyText
             );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "LobbySettings" ("Game", "MinimumPlayers", "MaximumPlayers", "CreationTime", "AutoStart", "AnyPlayerStarts", "InitialTurnOrder", "MinimumRating", "MaximumRating", "GPrivate", "Password") VALUES (:game:, :minplayers:, :maxplayers:, UTC_TIMESTAMP(), :autostart:, :aps:, :ito:, :minrating:, :maxrating:, :private:, :password:)',
             'game'       , $IDNoA               ,
             'minplayers' , $EscapedMinPlayers   ,
             'maxplayers' , $EscapedMaxPlayers   ,
             'autostart'  , $AutoStartText       ,
             'aps'        , $APSText             ,
             'ito'        , $ITOText             ,
             'minrating'  , $EscapedMinRating    ,
             'maxrating'  , $EscapedMaxRating    ,
             'private'    , $Private             ,
             'password'   , $EscapedGamePassword
             );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "PlayerGameRcd" ("User", "Game", "GameResult", "Inherited", "GameCounts", "Colour", "NumLongTurns", "CurrentOccupant") VALUES (:user:, :game:, \'Playing\', 0, 1, :colour:, 0, 0)',
             'user'   , $_SESSION['MyUserID'] ,
             'game'   , $IDNoA                ,
             'colour' , $UserCol
             );
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