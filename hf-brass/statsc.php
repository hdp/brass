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

$mypage->script('version_select.js');
$mypage->title_body( 'Top 50 scores from all categories (players: '.
                     $numplayers.
                     ', board: '.
                     $BoardName
                     );
$mypage->loginbox(array( 'Location' => 4                ,
                         'Mode'     => 3                ,
                         'Players'  => $numplayers      ,
                         'Board'    => $board           ,
                         'UserID'   => $RequestedUserID ,
                         'Page'     => $Page
                         ));
$mypage->leaf('h1', 'Top 50 scores from all categories');

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
                   '(Click <a href="statistics.php?Mode=3&Players='.
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
if ( $numplayers == 2 ) {
    $colgroupsize =   2;
    $colwidth     = 197;
    $rankcolwidth =  70;
} else {
    $colgroupsize =   3;
    $colwidth     = 144;
    $rankcolwidth =  30;
}
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
                   'id="playersselect" onChange="alter_all_links(1)"'
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
                   'id="boardselect" onChange="alter_all_links(1)"'
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

$mypage->leaf( 'a',
               'Go',
               'href="statistics.php?Mode=3&amp;Players='.
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

$mypage->opennode('table');
$mypage->opennode('thead');
$mypage->opennode('tr');
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               'All',
               'colspan='.$colgroupsize
               );
$mypage->leaf( 'td',
               '"Competitive"',
               'colspan='.$colgroupsize
               );
$mypage->next();
$mypage->leaf( 'td',
               '',
               'style="min-width: '.$rankcolwidth.'px;"'
               );

if ( $_SESSION['LoggedIn'] ) {
    for ($j=0; $j<2; $j++) {
        $mypage->leaf( 'td',
                       '<a href="statistics.php?Mode='.
                           (4+3*$j).
                           '&Players='.
                           $numplayers.
                           '&amp;Board='.
                           $board.
                           '&amp;UserID='.
                           $RequestedUserID.
                           '" id="link_'.
                           get_scores_link_number().
                           '">All</a>',
                       'style="min-width: '.$colwidth.'px;"'
                       );
        if ( $numplayers > 2 ) {
            $mypage->leaf( 'td',
                           '<a href="statistics.php?Mode='.
                               (5+3*$j).
                               '&Players='.
                               $numplayers.
                               '&amp;Board='.
                               $board.
                               '&amp;UserID='.
                               $RequestedUserID.
                               '" id="link_'.
                               get_scores_link_number().
                               '">Didn\'t Win</a>',
                           'style="min-width: '.$colwidth.'px;"'
                           );
        }
        $mypage->leaf( 'td',
                       '<a href="statistics.php?Mode='.
                           (6+3*$j).
                           '&Players='.
                           $numplayers.
                           '&amp;Board='.
                           $board.
                           '&amp;UserID='.
                           $RequestedUserID.
                           '" id="link_'.
                           get_scores_link_number().
                           '">Lost</a>',
                       'style="min-width: '.$colwidth.'px;"'
                       );
    }
} else {
    for ($j=0; $j<2; $j++) {
        $mypage->leaf( 'td',
                       'All',
                       'style="min-width: '.$colwidth.'px;"'
                       );
        if ( $numplayers > 2 ) {
            $mypage->leaf( 'td',
                           'Didn\'t Win',
                           'style="min-width: '.$colwidth.'px;"'
                           );
        }
        $mypage->leaf( 'td',
                       'Lost',
                       'style="min-width: '.$colwidth.'px;"'
                       );
    }
}
$mypage->closenode(2); // tr, thead
$mypage->opennode('tbody');

if ( $numplayers == 2 ) {
    $numcolumns = 4;
    if ( $EscapedUserID ) {
        $Queries = array( 'CALL "Stats_HSTsp_AA"(:user:, 2, :board:, :me:)',
                          'CALL "Stats_HSTsp_AL"(:user:, 2, :board:, :me:)',
                          'CALL "Stats_HSTsp_CA"(:user:, 2, :board:, :me:)',
                          'CALL "Stats_HSTsp_CL"(:user:, 2, :board:, :me:)'
                          );
    } else if ( $_SESSION['LoggedIn'] ) {
        $Queries = array( 'CALL "Stats_HSTs_AA_LoggedIn"(2, :board:, :me:)',
                          'CALL "Stats_HSTs_AL_LoggedIn"(2, :board:, :me:)',
                          'CALL "Stats_HSTs_CA_LoggedIn"(2, :board:, :me:)',
                          'CALL "Stats_HSTs_CL_LoggedIn"(2, :board:, :me:)'
                          );
    } else {
        $Queries = array( 'CALL "Stats_HSTs_AA_LoggedOut"(2, :board:)',
                          'CALL "Stats_HSTs_AL_LoggedOut"(2, :board:)',
                          'CALL "Stats_HSTs_CA_LoggedOut"(2, :board:)',
                          'CALL "Stats_HSTs_CL_LoggedOut"(2, :board:)'
                          );
    }
} else {
    $numcolumns = 6;
    if ( $EscapedUserID ) {
        $Queries = array( 'CALL "Stats_HSTsp_AA"(:user:, :numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTsp_AD"(:user:, :numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTsp_AL"(:user:, :numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTsp_CA"(:user:, :numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTsp_CD"(:user:, :numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTsp_CL"(:user:, :numplayers:, :board:, :me:)'
                          );
    } else if ( $_SESSION['LoggedIn'] ) {
        $Queries = array( 'CALL "Stats_HSTs_AA_LoggedIn"(:numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTs_AD_LoggedIn"(:numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTs_AL_LoggedIn"(:numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTs_CA_LoggedIn"(:numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTs_CD_LoggedIn"(:numplayers:, :board:, :me:)',
                          'CALL "Stats_HSTs_CL_LoggedIn"(:numplayers:, :board:, :me:)'
                          );
    } else {
        $Queries = array( 'CALL "Stats_HSTs_AA_LoggedOut"(:numplayers:, :board:)',
                          'CALL "Stats_HSTs_AD_LoggedOut"(:numplayers:, :board:)',
                          'CALL "Stats_HSTs_AL_LoggedOut"(:numplayers:, :board:)',
                          'CALL "Stats_HSTs_CA_LoggedOut"(:numplayers:, :board:)',
                          'CALL "Stats_HSTs_CD_LoggedOut"(:numplayers:, :board:)',
                          'CALL "Stats_HSTs_CL_LoggedOut"(:numplayers:, :board:)'
                          );
    }
}
if ( $_SESSION['LoggedIn'] ) {
    $me = $_SESSION['MyUserID'];
} else {
    $me = 0;
}

for ($j=0; $j<$numcolumns; $j++) {
    $QueryResults[$j] = dbquery( DBQUERY_READ_RESULTSET,
                                 $Queries[$j],
                                 'user'       , $EscapedUserID ,
                                 'board'      , $board         ,
                                 'numplayers' , $numplayers    ,
                                 'me'         , $me
                                 );
}
for ($i=1; $i<51; $i++) {
    $mypage->opennode('tr');
    $mypage->leaf('td', '<b>'.$i.'</b>');
    for ($j=0; $j<$numcolumns; $j++) {
        if ( $row = db_fetch_assoc($QueryResults[$j]) ) {
            if ( $_SESSION['LoggedIn'] ) {
                if ( $EscapedUserID == $_SESSION['MyUserID'] or
                     ( !$EscapedUserID and
                       $row['Owner'] == $_SESSION['MyUserID']
                       )
                     ) {
                    $CellTagAttributes = 'class="mymove"';
                } else if ( is_null($row['User']) ) {
                    $CellTagAttributes = null;
                } else {
                    $CellTagAttributes = 'class="mygame"';
                }
                if ( $EscapedUserID ) {
                    $mypage->leaf( 'td',
                                   '<a href="board.php?GameID='.
                                       $row['GameID'].
                                       '">'.
                                       $row['AdaptedScore'].
                                       '</a>',
                                   $CellTagAttributes
                                   );
                } else {
                    $mypage->opennode('td', $CellTagAttributes);
                    $mypage->leaf( 'a',
                                   $row['Name'],
                                   'href="userdetails.php?UserID='.
                                       $row['Owner'].
                                       '"'
                                   );
                    $mypage->leaf( 'a',
                                   $row['AdaptedScore'],
                                   'href="board.php?GameID='.
                                       $row['GameID'].
                                       '"'
                                   );
                    $mypage->closenode();
                }
            } else {
                $mypage->opennode('td');
                $mypage->text($row['Name']);
                $mypage->leaf( 'a',
                               $row['AdaptedScore'],
                               'href="board.php?GameID='.
                                   $row['GameID'].
                                   '"'
                               );
                $mypage->closenode();
            }
        } else {
            $mypage->leaf('td', 'n/a');
        }
    }
    $mypage->closenode();
}

$mypage->closenode(2); // tbody, table
$mypage->leaf( 'p',
               'Click <a href="index.php">here</a> to return to the Main Page, or <a href="statistics.php">here</a> to return to the main Statistics page.'
               );
$mypage->finish();

?>