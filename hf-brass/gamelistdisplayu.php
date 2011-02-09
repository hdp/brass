<?php

function minutes_mhd ($mins) {
    if ( $mins % 1440 == 0 ) {
        return ($mins / 1440).' d';
    }
    if ( $mins % 60 == 0 ) {
        return ($mins / 60).' h';
    }
    return $mins.' m';
}

function gamelistdisplayup (tagtree &$tt, $ThePlayerID, $PlayerName) {
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            'CALL "ListUsersGamesInProgress"(:user:, :me:)',
                            'user' , $ThePlayerID          ,
                            'me'   , $_SESSION['MyUserID']
                            );
    if ( $QueryResult === 'NONE' ) {
        $tt->leaf('p', 'None');
        return;
    }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 175px;"');
    $tt->leaf('th', 'Creator'  );
    $tt->leaf('th', 'Friendly?');
    $tt->leaf( 'th',
               'Orig',
               'title="The number of players the game had when it started."'
               );
    $tt->leaf( 'th',
               'Cur',
               'title="The number of players the game has at present."'
               );
    $tt->leaf('th', 'Round');
    $tt->leaf( 'th',
               'Last Move <span style="font-weight: normal;">(GMT)</span>'
               );
    $tt->leaf('th', 'Next To Move');
    $tt->leaf( 'th',
               transtext('_ugColColour'),
               'title="The colour '.$PlayerName.' is playing as in this game."'
               );
    $tt->leaf( 'th',
               transtext('_ugColInherited'),
               'title="Whether '.$PlayerName.' took over from another player who had left the game."'
               );
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $PlayerColours = array('FFC18A', 'FFFFAF', '9FFF9F', 'FFC6FF', 'C4C4C4');
    $ColourNames = array( transtext('_colourRed'),
                          transtext('_colourYellow'),
                          transtext('_colourGreen'),
                          transtext('_colourPurple'),
                          transtext('_colourGrey')
                          );
    $RoundColumnStyles = array('gamelist_roundcol_c', 'gamelist_roundcol_r');
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GTitleDeletedByAdmin'] ) {
            $row['GameName'] = 'The title of this game has been cleared by an Administrator';
        }
        if ( $row['PlayerToMoveID'] == $_SESSION['MyUserID'] ) {
            $RowTagAttributes = 'class="mymove"';
        } else if ( is_null($row['User']) ) {
            $RowTagAttributes = null;
        } else if ( $row['AbortVote'] == '00000' and
                    $row['KickVote'] == '00000'
                    ) {
            $RowTagAttributes = 'class="mygame"';
        } else {
            $RowTagAttributes = 'class="myattn"';
        }
        $tt->opennode('tr', $RowTagAttributes);
        $version_name = vname($row['VersionName'], $row['VersionNameSuffix']);
        $tt->leaf( 'td',
                   '<img src="gfx/icon-'.
                       strtolower($row['ShortVersionName']).
                       '.png" alt="'.
                       $version_name.
                       '" title="'.
                       $version_name.
                       ' ('.
                       $row['Creators'].
                       ')">',
                   'width=23 style="border-right: none;"'
                   );
        $tt->leaf( 'td',
                   '<a href="board.php?GameID='.
                       $row['GameID'].
                       '">'.
                       $row['GameName'].
                       '</a>',
                   'style="border-left: none; padding-left: 0px; text-align: left;"'
                   );
        $tt->leaf( 'td',
                   '<a href="userdetails.php?UserID='.
                       $row['GameCreator'].
                       '">'.
                       $row['GameCreatorName'].
                       '</a>'
                   );
        if ( $row['Friendly'] ) {
            $tt->leaf('td', transtext('^Yes'), 'bgcolor="#9FFF9F"');
        } else {
            $tt->leaf('td', transtext('^No') , 'bgcolor="#FFC18A"');
        }
        $tt->leaf('td', $row['OriginalPlayers'] );
        $tt->leaf('td', $row['CurrentPlayers']  );
        $tt->leaf( 'td',
                   ($row['Round'] >= 10 ? '' : '&nbsp;&nbsp;').
                       $row['Round'].' / '.$row['NumRounds'],
                   'class="'.$RoundColumnStyles[$row['RailPhase']].'"'
                   );
        $tt->leaf( 'td',
                   date('M-d H:i', strtotime($row['LastMove']))
                   );
        $tt->leaf( 'td',
                   '<a href="userdetails.php?UserID='.
                       $row['PlayerToMoveID'].
                       '">'.
                       $row['PlayerToMoveName'].
                       '</a>',
                   'bgcolor="#'.
                       $PlayerColours[$row['PlayerToMove']].
                       '"'
                   );
        $tt->leaf( 'td',
                   $ColourNames[$row['UsersColour']],
                   'bgcolor="#'.
                       $PlayerColours[$row['UsersColour']].
                       '"'
                   );
        $tt->leaf( 'td',
                   $row['Inherited'] ?
                       transtext('^Yes') :
                       transtext('^No')
                   );
        $tt->closenode();
    }
    $tt->closenode(2); // tbody, table
}

function gamelistdisplayur (tagtree &$tt, $ThePlayerID, $PlayerName) {
    global $Rating;
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            'CALL "ListUsersGamesRecruiting"(:user:, :me:)',
                            'user' , $ThePlayerID          ,
                            'me'   , $_SESSION['MyUserID']
                            );
    if ( $QueryResult === 'NONE' ) {
        $tt->leaf('p', 'None');
        return;
    }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name'     , 'colspan=2' );
    $tt->leaf('th', 'Creator'                );
    $tt->leaf('th', 'Friendly?'              );
    $tt->leaf( 'th',
               'Min',
               'title="The minimum number of players for this game."'
               );
    $tt->leaf( 'th',
               'Max',
               'title="The maximum number of players for this game."'
               );
    $tt->leaf( 'th',
               'Cur',
               'title="The number of players who have joined the game so far."'
               );
    $tt->leaf( 'th',
               'Priv/Rating',
               'title="Whether the game is private (requires a password to join), and if not, what the minimum and maximum permitted ratings are."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLA</a>',
               'title="&quot;Time Limit A&quot; - click the link for more information."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLB</a>',
               'title="&quot;Time Limit B&quot; - click the link for more information."'
               );
    $tt->leaf('th', 'Talk Rules');
    $tt->leaf('th', 'Created <span style="font-weight: normal;">(GMT)</span>');
    $tt->leaf( 'th',
               transtext('_ugColColour'),
               'title="The colour '.$PlayerName.' is going to play as in this game."'
               );
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $PlayerColours = array('FFC18A', 'FFFFAF', '9FFF9F', 'FFC6FF', 'C4C4C4');
    $ColourNames = array( transtext('_colourRed'),
                          transtext('_colourYellow'),
                          transtext('_colourGreen'),
                          transtext('_colourPurple'),
                          transtext('_colourGrey')
                          );
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GTitleDeletedByAdmin'] ) {
            $row['GameName'] = 'The title of this game has been cleared by an Administrator';
        }
        $RowTagAttributes = null;
        if ( is_null($row['User']) ) {
            $RowTagAttributes = null;
        } else if ( $row['CurrentPlayers'] == $row['MaximumPlayers'] and
             ( $_SESSION['MyUserID'] == $row['GameCreator'] or
               $row['AnyPlayerStarts']
               )
             ) {
            $RowTagAttributes = 'class="mymove"';
        } else if ( $row['CurrentPlayers'] >= $row['MinimumPlayers'] and
                    ( $_SESSION['MyUserID'] == $row['GameCreator'] or
                      $row['AnyPlayerStarts']
                      )
                    ) {
            $RowTagAttributes = 'class="myattn"';
        } else {
            $RowTagAttributes = 'class="mygame"';
        }
        if ( is_null($row['MaximumRating']) ) {
            $ReportedMaxRating  = 'n/a';
            $NumericalMaxRating = 10000;
        } else {
            $ReportedMaxRating  = $row['MaximumRating'];
            $NumericalMaxRating = $row['MaximumRating'];
        }
        if ( $row['GPrivate'] ) {
            $RatingCellContent = 'Private';
            $RatingCellAttributes = 'bgcolor="#FFC18A"';
        } else {
            $RatingCellContent = $row['MinimumRating'].
                                 '-'.
                                 $ReportedMaxRating;
            if ( $row['MinimumRating'] > $Rating or
                 $NumericalMaxRating < $Rating
                 ) {
                $RatingCellAttributes = 'bgcolor="#FFDF0F"';
            } else {
                $RatingCellAttributes = 'bgcolor="#9FFF9F"';
            }
        }
        if ( $row['TimeLimitA'] <= 1080 ) {
            $TLACellAttributes = 'class="alert"';
        } else if ( $row['TimeLimitA'] <= 1800 ) {
            $TLACellAttributes = 'style="font-weight: bold;"';
        } else {
            $TLACellAttributes = null;
        }
        if ( $row['TimeLimitB'] <= 2160 ) {
            $TLBCellAttributes = 'class="alert"';
        } else if ( $row['TimeLimitB'] <= 3600 ) {
            $TLBCellAttributes = 'style="font-weight: bold;"';
        } else {
            $TLBCellAttributes = null;
        }
        $tt->opennode('tr', $RowTagAttributes);
        $version_name = vname($row['VersionName'], $row['VersionNameSuffix']);
        $tt->leaf( 'td',
                   '<img src="gfx/icon-'.
                       strtolower($row['ShortVersionName']).
                       '.png" alt="'.
                       $version_name.
                       '" title="'.
                       $version_name.
                       ' ('.
                       $row['Creators'].
                       ')">',
                   'width=23 style="border-right: none;"'
                   );
        $tt->leaf( 'td',
                   '<a href="board.php?GameID='.
                       $row['GameID'].
                       '">'.
                       $row['GameName'].
                       '</a>',
                   'style="border-left: none; padding-left: 0px; text-align: left;"'
                   );
        $tt->leaf( 'td',
                   '<a href="userdetails.php?UserID='.
                       $row['GameCreator'].
                       '">'.
                       $row['GameCreatorName'].
                       '</a>'
                   );
        if ( $row['Friendly'] ) {
            $tt->leaf('td', transtext('^Yes'), 'bgcolor="#9FFF9F"');
        } else {
            $tt->leaf('td', transtext('^No') , 'bgcolor="#FFC18A"');
        }
        $tt->leaf('td', $row['MinimumPlayers']  );
        $tt->leaf('td', $row['MaximumPlayers']  );
        $tt->leaf('td', $row['CurrentPlayers']  );
        $tt->leaf('td', $RatingCellContent, $RatingCellAttributes);
        $tt->leaf( 'td',
                   minutes_mhd($row['TimeLimitA']),
                   $TLACellAttributes
                   );
        $tt->leaf( 'td',
                   minutes_mhd($row['TimeLimitB']),
                   $TLBCellAttributes
                   );
        $tt->leaf('td', $row['TalkRules']);
        $tt->leaf( 'td',
                   date('M-d H:i', strtotime($row['CreationTime']))
                   );
        $tt->leaf( 'td',
                   $ColourNames[$row['UsersColour']],
                   'bgcolor="#'.
                       $PlayerColours[$row['UsersColour']].
                       '"'
                   );
        $tt->closenode();
    }
    $tt->closenode(2); // tbody, table
}

function gamelistdisplayuw (tagtree &$tt, $ThePlayerID, $WhichPage) {
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            'CALL "ListUsersGamesWatching"(:user:, :me:)',
                            'user' , $ThePlayerID          ,
                            'me'   , $_SESSION['MyUserID']
                            );
    if ( $QueryResult === 'NONE' ) {
        $tt->leaf('p', 'None');
        return;
    }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 175px;"');
    $tt->leaf('th', 'Creator'  );
    $tt->leaf('th', 'Friendly?');
    $tt->leaf( 'th',
               'Orig',
               'title="The number of players the game had when it started."'
               );
    $tt->leaf( 'th',
               'Cur',
               'title="The number of players (as in seats, whether filled or unfilled) the game has at present."'
               );
    $tt->leaf('th', 'Round');
    $tt->leaf( 'th',
               'Last Move <span style="font-weight: normal;">(GMT)</span>'
               );
    $tt->leaf('th', 'Next To Move');
    if ( $_SESSION['MyUserID'] == $ThePlayerID ) {
        $tt->leaf('th', '');
    }
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $PlayerColours = array('FFC18A', 'FFFFAF', '9FFF9F', 'FFC6FF', 'C4C4C4');
    $RoundColumnStyles = array('gamelist_roundcol_c', 'gamelist_roundcol_r');
    $OldGameID = 0;
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GameID'] != $OldGameID ) {
            $OldGameID = $row['GameID'];
            if ( $row['GTitleDeletedByAdmin'] ) {
                $row['GameName'] = 'The title of this game has been cleared by an Administrator';
            }
            if ( is_null($row['CurrentOccupant']) or
                 ( $row['CurrentOccupant'] > 0 and
                   !$row['GameIsFinished']
                   )
                 ) {
                $RowTagAttributes = null;
            } else if ( ( $row['GameStatus'] == 'In Progress' and
                          $row['PlayerToMoveID'] == $_SESSION['MyUserID']
                          ) or
                        ( $row['GameStatus'] == 'Recruiting Replacement' and
                          !is_null($row['Colour'])
                          )
                        ) {
                $RowTagAttributes = 'class="mymove"';
            } else if ( ( $row['GameStatus'] == 'In Progress' or
                          $row['GameStatus'] == 'Recruiting Replacement'
                          ) and
                        ( $row['AbortVote'] != '00000' or
                          $row['KickVote'] != '00000'
                          )
                        ) {
                $RowTagAttributes = 'class="myattn"';
            } else {
                $RowTagAttributes = 'class="mygame"';
            }
            if ( $row['GameStatus'] == 'In Progress' ) {
                $ptmCellContents = '<a href="userdetails.php?UserID='.
                                   $row['PlayerToMoveID'].
                                   '">'.
                                   $row['PlayerToMoveName'].
                                   '</a>';
                $ptmCellAttributes = 'bgcolor="#'.
                                      $PlayerColours[$row['PlayerToMove']].
                                      '"';
            } else if ( $row['GameStatus'] == 'Recruiting Replacement' ) {
                $ptmCellContents = '(Replacement Needed)';
                $ptmCellAttributes = null;
            } else {
                $ptmCellContents = '(Finished)';
                $ptmCellAttributes = null;
            }
            $tt->opennode('tr', $RowTagAttributes);
            $version_name = vname($row['VersionName'], $row['VersionNameSuffix']);
            $tt->leaf( 'td',
                       '<img src="gfx/icon-'.
                           strtolower($row['ShortVersionName']).
                           '.png" alt="'.
                           $version_name.
                           '" title="'.
                           $version_name.
                           ' ('.
                           $row['Creators'].
                           ')">',
                       'width=23 style="border-right: none;"'
                       );
            $tt->leaf( 'td',
                       '<a href="board.php?GameID='.
                           $row['GameID'].
                           '">'.
                           $row['GameName'].
                           '</a>',
                       'style="border-left: none; padding-left: 0px; text-align: left;"'
                       );
            $tt->leaf( 'td',
                       '<a href="userdetails.php?UserID='.
                           $row['GameCreator'].
                           '">'.
                           $row['GameCreatorName'].
                           '</a>'
                       );
            if ( $row['Friendly'] ) {
                $tt->leaf('td', transtext('^Yes'), 'bgcolor="#9FFF9F"');
            } else {
                $tt->leaf('td', transtext('^No') , 'bgcolor="#FFC18A"');
            }
            $tt->leaf('td', $row['OriginalPlayers'] );
            $tt->leaf('td', $row['CurrentPlayers']  );
            $tt->leaf( 'td',
                       ($row['Round'] >= 10 ? '' : '&nbsp;&nbsp;').
                           $row['Round'].' / '.$row['NumRounds'],
                       'class="'.$RoundColumnStyles[$row['RailPhase']].'"'
                       );
            $tt->leaf( 'td',
                       date('M-d H:i', strtotime($row['LastMove']))
                       );
            $tt->leaf('td', $ptmCellContents, $ptmCellAttributes);
            if ( $_SESSION['MyUserID'] == $ThePlayerID ) {
                $tt->leaf( 'td',
                           '<a href="watch.php?ReturnWhere='.
                               $WhichPage.
                               '&amp;GameID='.
                               $row['GameID'].
                               '">'.
                               transtext('_ugWatchlistRmv')
                           );
            }
            $tt->closenode();
        }
    }
    $tt->closenode(2); // tbody, table
}

function gamelistdisplayux (tagtree &$tt, $ThePlayerID, $PlayerName) {
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            'CALL "ListUsersGamesNeedingReplacements"(:user:, :me:)',
                            'user' , $ThePlayerID          ,
                            'me'   , $_SESSION['MyUserID']
                            );
    if ( $QueryResult === 'NONE' ) {
        $tt->leaf('p', 'None');
        return;
    }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 175px;"');
    $tt->leaf('th', 'Creator'  );
    $tt->leaf('th', 'Friendly?');
    $tt->leaf( 'th',
               'Found?',
               'title="Has at least one candidate replacement been found for the seat that is unfilled?"'
               );
    $tt->leaf( 'th',
               'Orig',
               'title="The number of players the game had when it started."'
               );
    $tt->leaf( 'th',
               'Cur',
               'title="The number of players (as in seats, whether filled or unfilled) the game has at present."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLA</a>',
               'title="&quot;Time Limit A&quot; - click the link for more information."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLB</a>',
               'title="&quot;Time Limit B&quot; - click the link for more information."'
               );
    $tt->leaf('th', 'Round'     );
    $tt->leaf('th', 'Talk Rules');
    $tt->leaf( 'th',
               transtext('_ugColColour'),
               'title="The colour '.$PlayerName.' is playing as in this game."'
               );
    $tt->leaf( 'th',
               transtext('_ugColInherited'),
               'title="Whether '.$PlayerName.' took over from another player who had left the game."'
               );
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $PlayerColours = array('FFC18A', 'FFFFAF', '9FFF9F', 'FFC6FF', 'C4C4C4');
    $ColourNames = array( transtext('_colourRed'),
                          transtext('_colourYellow'),
                          transtext('_colourGreen'),
                          transtext('_colourPurple'),
                          transtext('_colourGrey')
                          );
    $RoundColumnStyles = array('gamelist_roundcol_c', 'gamelist_roundcol_r');
    $OldGameID = 0;
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GameID'] != $OldGameID ) {
            $OldGameID = $row['GameID'];
            if ( $row['GTitleDeletedByAdmin'] ) {
                $row['GameName'] = 'The title of this game has been cleared by an Administrator';
            }
            $RowTagAttributes = null;
            if ( is_null($row['User']) ) {
                $RowTagAttributes = null;
            } else if ( !is_null($row['Colour']) ) {
                $RowTagAttributes = 'class="mymove"';
            } else if ( $row['AbortVote'] == '00000' and
                        $row['KickVote'] == '00000'
                        ) {
                $RowTagAttributes = 'class="mygame"';
            } else {
                $RowTagAttributes = 'class="myattn"';
            }
            if ( $row['TimeLimitA'] <= 1080 ) {
                $TLACellAttributes = 'class="alert"';
            } else if ( $row['TimeLimitA'] <= 1800 ) {
                $TLACellAttributes = 'style="font-weight: bold;"';
            } else {
                $TLACellAttributes = null;
            }
            if ( $row['TimeLimitB'] <= 2160 ) {
                $TLBCellAttributes = 'class="alert"';
            } else if ( $row['TimeLimitB'] <= 3600 ) {
                $TLBCellAttributes = 'style="font-weight: bold;"';
            } else {
                $TLBCellAttributes = null;
            }
            $tt->opennode('tr', $RowTagAttributes);
            $version_name = vname($row['VersionName'], $row['VersionNameSuffix']);
            $tt->leaf( 'td',
                       '<img src="gfx/icon-'.
                           strtolower($row['ShortVersionName']).
                           '.png" alt="'.
                           $version_name.
                           '" title="'.
                           $version_name.
                           ' ('.
                           $row['Creators'].
                           ')">',
                       'width=23 style="border-right: none;"'
                       );
            $tt->leaf( 'td',
                       '<a href="board.php?GameID='.
                           $row['GameID'].
                           '">'.
                           $row['GameName'].
                           '</a>',
                       'style="border-left: none; padding-left: 0px; text-align: left;"'
                       );
            $tt->leaf( 'td',
                       '<a href="userdetails.php?UserID='.
                           $row['GameCreator'].
                           '">'.
                           $row['GameCreatorName'].
                           '</a>'
                       );
            if ( $row['Friendly'] ) {
                $tt->leaf('td', transtext('^Yes'), 'bgcolor="#9FFF9F"');
            } else {
                $tt->leaf('td', transtext('^No') , 'bgcolor="#FFC18A"');
            }
            if ( is_null($row['Colour']) ) {
                $tt->leaf('td', transtext('^No') , 'bgcolor="#FFC18A"');
            } else {
                $tt->leaf('td', transtext('^Yes'), 'bgcolor="#9FFF9F"');
            }
            $tt->leaf('td', $row['OriginalPlayers']);
            $tt->leaf('td', $row['CurrentPlayers']);
            $tt->leaf( 'td',
                       minutes_mhd($row['TimeLimitA']),
                       $TLACellAttributes
                       );
            $tt->leaf( 'td',
                       minutes_mhd($row['TimeLimitB']),
                       $TLBCellAttributes
                       );
            $tt->leaf( 'td',
                       ($row['Round'] >= 10 ? '' : '&nbsp;&nbsp;').
                           $row['Round'].' / '.$row['NumRounds'],
                       'class="'.$RoundColumnStyles[$row['RailPhase']].'"'
                       );
            $tt->leaf('td', $row['TalkRules']);
            $tt->leaf( 'td',
                       $ColourNames[$row['UsersColour']],
                       'bgcolor="#'.
                           $PlayerColours[$row['UsersColour']].
                           '"'
                       );
            $tt->leaf( 'td',
                       $row['Inherited'] ?
                           transtext('^Yes') :
                           transtext('^No')
                       );
            $tt->closenode();
        }
    }
    $tt->closenode(2); // tbody, table
}

?>