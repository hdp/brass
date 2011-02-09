<?php
require('_std-include.php');

if ( !$_SESSION['LoggedIn'] ) {
    $mypage = page::standard();
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You must be logged in to view this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
} else if ( @$_POST['FormSubmit'] == 'Execute' ) {
    /* require(HIDDEN_FILES_PATH.'udtasksadmin.php'); */
    /* UDTasksAdmin(); */ die(); // These need to be rewritten
} else if ( @$_POST['FormSubmit'] == 'Make Changes' ) {
    require(HIDDEN_FILES_PATH.'sanitise_str_fancy.php');
    require(HIDDEN_FILES_PATH.'udtasksnormal.php');
} else if ( !isset($_GET['UserID']) ) {
    $mypage = page::standard();
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks as though you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$EscapedUserID = (int)$_GET['UserID'];
$therow = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT * FROM "User" WHERE "UserID" = :user:',
                   'user' , $EscapedUserID
                   );
if ( $therow === 'NONE' or
     ( !$therow['UserValidated'] and !$Administrator )
     ) {
    $mypage = page::standard();
    $mypage->title_body('No such user');
    $mypage->leaf( 'p',
                   'There is no user with that user ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$scores_link_number = -1;
function get_scores_link_number () {
    global $scores_link_number;
    $scores_link_number++;
    return $scores_link_number;
}

get_translation_module(18);
require(HIDDEN_FILES_PATH.'gamelistdisplayu.php');
require_once(HIDDEN_FILES_PATH.'gamelistdisplayuf.php');
switch ( $therow['Pronoun'] ) {
    case 'He':
        $lowercasepronoun = 'he';
        $possessiveadjective = 'his';
        $lowercaseotherpronoun = 'him';
        break;
    case 'She':
        $lowercasepronoun = 'she';
        $possessiveadjective = 'her';
        $lowercaseotherpronoun = 'her';
        break;
    default:
        $lowercasepronoun = 'it';
        $possessiveadjective = 'its';
        $lowercaseotherpronoun = 'it';
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$pagetitle = str_replace( '\username',
                          $therow['Name'],
                          transtext('udPageTitle')
                          );
$mypage = page::standard();
$mypage->script('formatmessage.js');
$mypage->script('version_select.js');
$mypage->title_body( $pagetitle,
                     'onload="format_all_messages(true);"'
                     );
$mypage->loginbox(false);
$mypage->leaf('h1', $pagetitle);

$displayEmailLink = ( $therow['UserID'] != $_SESSION['MyUserID'] and
                      $therow['AllowContact'] and
                      !$Banned and
                      EMAIL_ENABLED
                      );
if ( $therow['UserValidated'] and
     $displayEmailLink
     ) {
    $mypage->opennode('ul', 'class="navlinks"');
    $mypage->leaf( 'li',
                   '<a href="usersgames.php?UserID='.$EscapedUserID.'">'.
                       transtext('ugLkUsersGames').
                       '</a>'
                   );
    $mypage->leaf( 'li',
                   '<a href="contact.php?UserID='.$EscapedUserID.'">'.
                       transtext('udSendAnEmail').
                       '</a>'
                   );
    $mypage->closenode();
} else if ( $therow['UserValidated'] ) {
    $mypage->leaf( 'p',
                   '<a href="usersgames.php?UserID='.$EscapedUserID.'">'.
                       transtext('ugLkUsersGames').
                       '</a>'
                   );
} else if ( $displayEmailLink ) {
    $mypage->leaf( 'p',
                   '<a href="contact.php?UserID='.$EscapedUserID.'">'.
                       transtext('udSendAnEmail').
                       '</a>'
                   );
}

if ( $therow['Administrator'] ) {
    $mypage->leaf( 'p',
                   '<img src="gfx/badge-administrator.png" alt="" style="vertical-align: text-bottom;"> '.
                       transtext('udUserIsAdmin')
                   );
}
if ( $therow['Translator'] ) {
    $mypage->leaf( 'p',
                   '<img src="gfx/badge-translator.png" alt="" style="vertical-align: text-bottom;"> '.
                       'This user is a Translator.'
                   );
}
if ( $therow['Banned'] ) {
    $mypage->leaf( 'p',
                   '<img src="gfx/badge-banned.png" alt="" style="vertical-align: text-bottom;"> '.
                       transtext('udUserIsBanned')
                   );
}
if ( $therow['Badge'] ) {
    $mypage->leaf( 'p',
                   '<img src="gfx/badge-supporter-14.png" alt="" style="vertical-align: text-bottom;"> '.
                       'This user is a supporter of the site.'
                   );
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$TZStrings = array( 'Not Specified',
                    'GMT - 11:30' , 'GMT - 11:00' , 'GMT - 10:30' ,
                    'GMT - 10:00' , 'GMT - 9:30'  , 'GMT - 9:00'  ,
                    'GMT - 8:30'  , 'GMT - 8:00'  , 'GMT - 7:30'  ,
                    'GMT - 7:00'  , 'GMT - 6:30'  , 'GMT - 6:00'  ,
                    'GMT - 5:30'  , 'GMT - 5:00'  , 'GMT - 4:30'  ,
                    'GMT - 4:00'  , 'GMT - 3:30'  , 'GMT - 3:00'  ,
                    'GMT - 2:30'  , 'GMT - 2:00'  , 'GMT - 1:30'  ,
                    'GMT - 1:00'  , 'GMT - 0:30'  , 'GMT'         ,
                    'GMT + 0:30'  , 'GMT + 1:00'  , 'GMT + 1:30'  ,
                    'GMT + 2:00'  , 'GMT + 2:30'  , 'GMT + 3:00'  ,
                    'GMT + 3:30'  , 'GMT + 4:00'  , 'GMT + 4:30'  ,
                    'GMT + 5:00'  , 'GMT + 5:30'  , 'GMT + 6:00'  ,
                    'GMT + 6:30'  , 'GMT + 7:00'  , 'GMT + 7:30'  ,
                    'GMT + 8:00'  , 'GMT + 8:30'  , 'GMT + 9:00'  ,
                    'GMT + 9:30'  , 'GMT + 10:00' , 'GMT + 10:30' ,
                    'GMT + 11:00' , 'GMT + 11:30' , 'GMT &plusmn; 12:00'
                    );

$regdate = date( 'Y-m-d H:i:s' , strtotime($therow['RegistrationDate']) );
$llogin  = date( 'Y-m-d H:i:s' , strtotime($therow['LastLogin'])        );
$lmove   = date( 'Y-m-d H:i:s' , strtotime($therow['LastMove'])         );
if ( $regdate == $llogin             ) { $llogin = '(Never)'; }
if ( $lmove == '1970-01-01 00:00:00' ) { $lmove  = '(Never)'; }

if ( $therow['NumGamesPlayed'] == 0 ) {
    $PercentageA = '100.00';
    $PercentageB = '100.00';
    $PercentageC = '100.00';
    $PercentageD = '100.00';
} else {
    $PercentageA = number_format( 100 * $therow['NumGamesTwoPlayer']   / $therow['NumGamesPlayed'] , 2 );
    $PercentageB = number_format( 100 * $therow['NumGamesThreePlayer'] / $therow['NumGamesPlayed'] , 2 );
    $PercentageC = number_format( 100 * $therow['NumGamesFourPlayer']  / $therow['NumGamesPlayed'] , 2 );
    $PercentageD = number_format( 100 * $therow['NumGamesCompleted']   / $therow['NumGamesPlayed'] , 2 );
}
if ( $therow['NumCompetitiveGamesPlayed'] == 0 ) {
    $PercentageE = '100.00';
    $PercentageF = '100.00';
    $PercentageG = '100.00';
    $PercentageH = '100.00';
    $PercentageI = '100.00';
} else {
    $PercentageE = number_format( 100 * $therow['NumCompetitiveGamesTwoPlayer']   / $therow['NumCompetitiveGamesPlayed'] , 2 );
    $PercentageF = number_format( 100 * $therow['NumCompetitiveGamesThreePlayer'] / $therow['NumCompetitiveGamesPlayed'] , 2 );
    $PercentageG = number_format( 100 * $therow['NumCompetitiveGamesFourPlayer']  / $therow['NumCompetitiveGamesPlayed'] , 2 );
    $PercentageH = number_format( 100 * $therow['NumCompetitiveGamesCompleted']   / $therow['NumCompetitiveGamesPlayed'] , 2 );
    $PercentageI = number_format( 100 * $therow['NumCompetitiveGamesWon']         / $therow['NumCompetitiveGamesPlayed'] , 2 );
}

$mypage->opennode('div', 'style="position: relative; width: 960px;"');
$mypage->opennode('table', 'class="table_no_borders" style="text-align: left;"');

$mypage->opennode('tr');
$mypage->leaf('td', transtext('udPronoun'), 'align=right');
$mypage->leaf('td', $therow['Pronoun']);

$mypage->next();
$mypage->leaf('td', transtext('udTimeZone'), 'align=right');
$mypage->leaf('td', $TZStrings[$therow['TimeZone']]);

if ( $therow['TimeZone'] ) {
    $mypage->next();
    $mypage->leaf('td', transtext('udUsersLocalTime'), 'align=right');
    $mypage->leaf( 'td',
                   date('H:i:s', strtotime('now')+1800*($therow['TimeZone']-24))
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   transtext('udDisregardDST'),
                   'colspan=2 align=center'
                   );
}

$mypage->next();
$mypage->leaf('td', transtext('udRegDate'), 'align=right');
$mypage->leaf('td', $regdate);

$mypage->next();
$mypage->leaf('td', transtext('udLastLogin'), 'align=right');
$mypage->leaf('td', $llogin);

$mypage->next();
$mypage->leaf('td', transtext('udLastMove'), 'align=right');
$mypage->leaf('td', $lmove);

$mypage->next();
$mypage->leaf('td', transtext('udMessagesPosted'), 'align=right');
$mypage->leaf('td', $therow['MessagesPosted']);

$mypage->next();
$mypage->leaf('td', '&nbsp;', 'colspan=2 align=center');

$mypage->next();
$mypage->leaf('td', transtext('udGamesPlayed'), 'align=right');
$mypage->leaf('td', $therow['NumGamesPlayed']);

$mypage->next();
$mypage->leaf('td', transtext('udOWCompleted'), 'align=right');
$mypage->leaf('td', $therow['NumGamesCompleted'].' ('.$PercentageD.'%)');

$mypage->next();
$mypage->leaf('td', transtext('udOW4Player'), 'align=right');
$mypage->leaf('td', $therow['NumGamesFourPlayer'].' ('.$PercentageC.'%)');

$mypage->next();
$mypage->leaf('td', transtext('udOW3Player'), 'align=right');
$mypage->leaf('td', $therow['NumGamesThreePlayer'].' ('.$PercentageB.'%)');

$mypage->next();
$mypage->leaf('td', transtext('udOW2Player'), 'align=right');
$mypage->leaf('td', $therow['NumGamesTwoPlayer'].' ('.$PercentageA.'%)');

$mypage->next();
$mypage->leaf('td', '&nbsp;', 'colspan=2 align=center');

$mypage->next();
$mypage->leaf('td', transtext('udCompetvePlayed'), 'align=right');
$mypage->leaf('td', $therow['NumCompetitiveGamesPlayed']);

$mypage->next();
$mypage->leaf('td', transtext('udOWCompleted'), 'align=right');
$mypage->leaf( 'td',
               $therow['NumCompetitiveGamesCompleted'].' ('.$PercentageH.'%)'
               );

$mypage->next();
$mypage->leaf('td', transtext('udOWWon'), 'align=right');
$mypage->leaf('td', $therow['NumCompetitiveGamesWon'].' ('.$PercentageI.'%)');

$mypage->next();
$mypage->leaf('td', transtext('udOW4Player'), 'align=right');
$mypage->leaf( 'td',
               $therow['NumCompetitiveGamesFourPlayer'].' ('.$PercentageG.'%)'
               );

$mypage->next();
$mypage->leaf('td', transtext('udOW3Player'), 'align=right');
$mypage->leaf( 'td',
               $therow['NumCompetitiveGamesThreePlayer'].' ('.$PercentageF.'%)'
               );

$mypage->next();
$mypage->leaf('td', transtext('udOW2Player'), 'align=right');
$mypage->leaf( 'td',
               $therow['NumCompetitiveGamesTwoPlayer'].' ('.$PercentageE.'%)'
               );

$mypage->next();
$mypage->leaf('td', transtext('udPlayerRating'), 'align=right');
$mypage->leaf('td', $therow['Rating']);

if ( $_SESSION['MyUserID'] == $EscapedUserID ) {
    $mypage->next();
    $mypage->leaf( 'td',
                   '<a href="recalculate.php">'.
                       transtext('udLkRecalculate').
                       '</a>',
                   'colspan=2 align=center'
                   );
}

$mypage->closenode(2); // tr, table

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$mypage->opennode( 'div',
                   'style="position: absolute; left: 400px; top: 0px; width: 550px; text-align: center;"'
                   );
$mypage->leaf('h3', 'Player\'s best scores');

$mypage->opennode('p');
$mypage->text('Before clicking a link below, first select the board for which you wish to view scores:');
$mypage->opennode('select', 'id="boardselect" onChange="alter_all_links(0)"');
$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        'SELECT "VersionGroupID", "VersionName" FROM "GameVersionGroup"'
                        );
if ( $QueryResult === 'NONE' ) { die(); }
while ( $row = db_fetch_assoc($QueryResult) ) {
    $mypage->leaf( 'option',
                   $row['VersionName'],
                   'value='.$row['VersionGroupID']
                   );
}
$mypage->closenode(2);

$number_headers = array( '',
                         '',
                         'With two players:',
                         'With three players:',
                         'With four players:'
                         );
$category_labels = array('Competitive only: ', 'All games: ');
for ($i=4; $i>1; $i--) {
    $scores_links = '<b>'.
                    $number_headers[$i].
                    '</b><br><a href="statistics.php?Mode=3&Players='.
                    $i.
                    '&Board=1&UserID='.
                    $EscapedUserID.
                    '" id="link_'.
                    get_scores_link_number().
                    '">Top 50 in each category</a>';
    for ($j=0; $j<2; $j++) {
        $scores_links .= '<br>'.
                         $category_labels[$j].
                         '<a href="statistics.php?Mode='.
                         (7-3*$j).
                         '&Players='.
                         $i.
                         '&Board=1&UserID='.
                         $EscapedUserID.
                         '" id="link_'.
                         get_scores_link_number().
                         '">All results</a>, ';
        if ( $i > 2 ) {
            $scores_links .= '<a href="statistics.php?Mode='.
                             (8-3*$j).
                             '&Players='.
                             $i.
                             '&Board=1&UserID='.
                             $EscapedUserID.
                             '" id="link_'.
                             get_scores_link_number().
                             '">Excl. wins</a>, ';
        }
        $scores_links .= '<a href="statistics.php?Mode='.
                         (9-3*$j).
                         '&Players='.
                         $i.
                         '&Board=1&UserID='.
                         $EscapedUserID.
                         '" id="link_'.
                         get_scores_link_number().
                         '">Losses only</a>';
    }
    $mypage->leaf('p', $scores_links, 'class="font_sans_serif"');
}

$mypage->closenode(2); // div, div

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$PStatementHeader = transtext('udPStatement');
if ( $_SESSION['MyUserID'] == $EscapedUserID ) {
    $PStatementHeader .= ' <span style="font-weight: normal; font-style: italic;">'.
                         transtext('udMayEditBelow').
                         '</span>';
}
$mymatch = array();
if ( is_null($therow['PersonalStatement']) ) {
    $PStatementHeader .= ' '.transtext('udPSNone');
    $mypage->leaf('h3', $PStatementHeader);
} else if ( preg_match('/\\A([01]+;[0-9,]+:)/', $therow['PersonalStatement'], $mymatch) ) {
    $mypage->leaf('h3', $PStatementHeader);
    $mypage->opennode('div', 'class="modularbox messagebox"');
    $realstatement = substr($therow['PersonalStatement'], strlen($mymatch[0]));
    $mymatch = explode(';', substr($mymatch[0], 0, -1));
    $types   = str_split($mymatch[0]);
    $offsets = explode(',', $mymatch[1]);
    if ( count($types) != count($offsets) ) {
        $types   = array(0);
        $offsets = array(0);
    }
    $j = count($types) - array_sum($types);
    $k = array_sum($types);
    $leafcontents   = array();
    $leaftypes      = array();
    $leafattributes = array();
    for ($i=count($types)-1; $i>=0; $i--) {
        $leafcontents[] = substr($realstatement, $offsets[$i]);
        if ( $types[$i] === '1' ) {
            $k--;
            $leaftypes[] = 'pre';
            $leafattributes[] = 'id="msgcode_'.$k.'"';
        } else {
            $j--;
            $leaftypes[] = 'p';
            $leafattributes[] = 'id="message_'.$j.'"';
        }
        $realstatement = substr($realstatement, 0, $offsets[$i]);
    }
    for ($i=count($types)-1; $i>=0; $i--) {
        $mypage->leaf($leaftypes[$i], $leafcontents[$i], $leafattributes[$i]);
    }
    $mypage->closenode();
} else {
    $mypage->leaf('h3', $PStatementHeader);
    $mypage->leaf( 'div',
                   '(Personal statement is in an unexpected format!)',
                   'class="modularbox font_sans_serif" style="background-color: #FFCF9F; text-align: center;"'
                   );
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$mypage->leaf('h3', transtext('_ugInProgress'));
gamelistdisplayup($mypage, $EscapedUserID, $therow['Name']);
if ( $therow['PublicWatch'] or $Administrator or $_SESSION['MyUserID'] == $EscapedUserID ) {
    $WatchedGamesHeader = transtext('_ugWatching');
    if ( !$therow['PublicWatch'] ) {
        $WatchedGamesHeader .= ( $_SESSION['MyUserID'] == $EscapedUserID ) ?
                                   // "(NB. You have barred other users from seeing this list)"
                               ' <span style="font-weight: normal;">'.
                                   transtext('_ugPrivWatchlist').
                                   '</span>' :
                               ' <span style="font-weight: normal;">(This list has been made invisible to non-admins)</span>';
    }
    $mypage->leaf('h3', $WatchedGamesHeader);
    gamelistdisplayuw($mypage, $EscapedUserID, 1);
}
$mypage->leaf('h3', transtext('_ugRR'));
gamelistdisplayux($mypage, $EscapedUserID, $therow['Name']);
$mypage->leaf('h3', transtext('_ugRecruiting'));
gamelistdisplayur($mypage, $EscapedUserID, $therow['Name']);
$mypage->leaf('h3', transtext('_ugFinished'));
gamelistdisplayuf($mypage, $EscapedUserID, $therow['Name'], $lowercasepronoun, true, 0, 20, null);

if ( $_SESSION['MyUserID'] == $EscapedUserID ) {
    require(HIDDEN_FILES_PATH.'udform.php');
}

$mypage->leaf('p', 'Click <a href="index.php">here</a> to return to the Main Page.');
$mypage->finish();

?>