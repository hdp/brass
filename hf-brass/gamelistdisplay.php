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

function gamelistdisplayp (tagtree &$tt, $All) {
    if ( $All ) {
        $Query = 'CALL "ListGamesInProgress_All"(:me:)';
        $me = $_SESSION['MyUserID'];
    } else if ( $_SESSION['LoggedIn'] ) {
        $Query = 'CALL "ListGamesInProgress_Restricted_LoggedIn"(:me:)';
        $me = $_SESSION['MyUserID'];
    } else {
        $Query = 'CALL "ListGamesInProgress_Restricted_LoggedOut"()';
        $me = 0;
    }
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            $Query,
                            'me' , $me
                            );
    if ( $QueryResult === 'NONE' ) {
        $tt->leaf('p', 'None');
        return;
    }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 270px;"');
    $tt->leaf('th', 'Creator'  );
    $tt->leaf('th', 'Friendly?');
    $tt->leaf('th', 'Original', 'title="The number of players the game had when it started."');
    $tt->leaf('th', 'Current' , 'title="The number of players the game has at present."'     );
    $tt->leaf('th', 'Round', 'style="width: 46px;"');
    $tt->leaf( 'th',
               'Last Move <span style="font-weight: normal;">(GMT)</span>',
               'style="min-width: 125px;"'
               );
    $tt->leaf('th', 'Next To Move');
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $PlayerColours = array('FFC18A', 'FFFFAF', '9FFF9F', 'FFC6FF', 'C4C4C4');
    $RoundColumnStyles = array('gamelist_roundcol_c', 'gamelist_roundcol_r');
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GTitleDeletedByAdmin'] ) {
            $row['GameName'] = 'The title of this game has been cleared by an Administrator';
        }
        $RowTagAttributes = null;
        if ( $_SESSION['LoggedIn'] ) {
            $GameCreatorColumn  = '<a href="userdetails.php?UserID='.
                                  $row['GameCreator'].
                                  '">'.
                                  $row['GameCreatorName'].
                                  '</a>';
            $PlayerToMoveColumn = '<a href="userdetails.php?UserID='.
                                  $row['PlayerToMoveID'].
                                  '">'.
                                  $row['PlayerToMoveName'].
                                  '</a>';
            if ( $row['PlayerToMoveID'] == $_SESSION['MyUserID'] ) {
                $RowTagAttributes = 'class="mymove"';
            } else if ( !is_null($row['User']) ) {
                if ( $row['AbortVote'] == '00000' and
                     $row['KickVote'] == '00000'
                     ) {
                    $RowTagAttributes = 'class="mygame"';
                } else {
                    $RowTagAttributes = 'class="myattn"';
                }
            }
        } else {
            $GameCreatorColumn  = $row['GameCreatorName'];
            $PlayerToMoveColumn = $row['PlayerToMoveName'];
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
        $tt->leaf('td', $GameCreatorColumn);
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
                   $PlayerToMoveColumn,
                   'bgcolor="#'.$PlayerColours[$row['PlayerToMove']].'"'
                   );
        $tt->closenode();
    }
    $tt->closenode(2); // tbody, table
}

function gamelistdisplayr (tagtree &$tt, $GamesCancelled) {
    global $Rating;
    if ( $GamesCancelled ) {
        $Query = 'CALL "ListGamesCancelled"(:me:)';
        $me = $_SESSION['MyUserID'];
    } else if ( $_SESSION['LoggedIn'] ) {
        $Query = 'CALL "ListGamesRecruiting_LoggedIn"(:me:)';
        $me = $_SESSION['MyUserID'];
    } else {
        $Query = 'CALL "ListGamesRecruiting_LoggedOut"()';
        $me = 0;
    }
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            $Query,
                            'me' , $me
                            );
    if ( $QueryResult === 'NONE' ) {
        $tt->leaf('p', 'None');
        return;
    }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 270px;"');
    $tt->leaf('th', 'Creator'  );
    $tt->leaf('th', 'Friendly?');
    $tt->leaf('th', 'Min', 'title="The minimum number of players for this game."');
    $tt->leaf('th', 'Max', 'title="The maximum number of players for this game."');
    $tt->leaf('th', 'Cur', 'title="The number of players who have joined the game so far."');
    $tt->leaf( 'th',
               'Priv/Rating',
               'title="Whether the game is private (requires a password to join), and if not, what the minimum and maximum permitted ratings are."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLA</a>',
               'style="min-width: 36px;" title="&quot;Time Limit A&quot; - click the link for more information."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLB</a>',
               'style="min-width: 36px;" title="&quot;Time Limit B&quot; - click the link for more information."'
               );
    $tt->leaf('th', 'Talk Rules', 'style="min-width: 100px;"');
    $tt->leaf( 'th',
               'Created <span style="font-weight: normal;">(GMT)</span>',
               'style="min-width: 105px;"'
               );
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GTitleDeletedByAdmin'] ) {
            $row['GameName'] = 'The title of this game has been cleared by an Administrator';
        }
        $RowTagAttributes = null;
        if ( $_SESSION['LoggedIn'] ) {
            $GameCreatorColumn = '<a href="userdetails.php?UserID='.
                                 $row['GameCreator'].
                                 '">'.
                                 $row['GameCreatorName'].
                                 '</a>';
            if ( !is_null($row['User']) ) {
                if ( $row['CurrentPlayers'] == $row['MaximumPlayers'] and
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
            }
        } else {
            $GameCreatorColumn = $row['GameCreatorName'];
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
            $RatingCellContent = $row['MinimumRating'].'-'.$ReportedMaxRating;
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
                   '<a href="lobby.php?GameID='.
                       $row['GameID'].
                       '">'.
                       $row['GameName'].
                       '</a>',
                   'style="border-left: none; padding-left: 0px; text-align: left;"'
                   );
        $tt->leaf('td', $GameCreatorColumn);
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
        $tt->closenode();
    }
    $tt->closenode(2); // tbody, table
}

function gamelistdisplayx (tagtree &$tt) {
    if ( $_SESSION['LoggedIn'] ) {
        $Query = 'CALL "ListGamesNeedingReplacements_LoggedIn"(:me:)';
        $me = $_SESSION['MyUserID'];
    } else {
        $Query = 'CALL "ListGamesNeedingReplacements_LoggedOut"()';
        $me = 0;
    }
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            $Query,
                            'me' , $me
                            );
    if ( $QueryResult === 'NONE' ) {
        $tt->leaf('p', 'None');
        return;
    }
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 270px;"');
    $tt->leaf('th', 'Creator'  );
    $tt->leaf('th', 'Friendly?');
    $tt->leaf( 'th',
               'Found?',
               'title="Has at least one candidate replacement been found for the seat that is unfilled?"'
               );
    $tt->leaf( 'th',
               'Original',
               'title="The number of players the game had when it started."'
               );
    $tt->leaf( 'th',
               'Current',
               'title="The number of players (as in seats, whether filled or unfilled) the game has at present."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLA</a>',
               'style="min-width: 36px;" title="&quot;Time Limit A&quot; - click the link for more information."'
               );
    $tt->leaf( 'th',
               '<a href="http://orderofthehammer.com/credits.htm#acronyms">TLB</a>',
               'style="min-width: 36px;" title="&quot;Time Limit B&quot; - click the link for more information."'
               );
    $tt->leaf('th', 'Round', 'style="width: 46px;"');
    $tt->leaf('th', 'Talk Rules', 'style="min-width: 100px;"');
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $RoundColumnStyles = array('gamelist_roundcol_c', 'gamelist_roundcol_r');
    $OldGameID = 0;
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GameID'] != $OldGameID ) {
            $OldGameID = $row['GameID'];
            if ( $row['GTitleDeletedByAdmin'] ) {
                $row['GameName'] = 'The title of this game has been cleared by an Administrator';
            }
            $RowTagAttributes = null;
            if ( $_SESSION['LoggedIn'] ) {
                $GameCreatorColumn = '<a href="userdetails.php?UserID='.
                                     $row['GameCreator'].
                                     '">'.
                                     $row['GameCreatorName'].
                                     '</a>';
                if ( !is_null($row['User']) ) {
                    if ( !is_null($row['Colour']) ) {
                        $RowTagAttributes = 'class="mymove"';
                    } else if ( $row['AbortVote'] == '00000' and
                                $row['KickVote'] == '00000'
                                ) {
                        $RowTagAttributes = 'class="mygame"';
                    } else {
                        $RowTagAttributes = 'class="myattn"';
                    }
                }
            } else {
                $GameCreatorColumn = $row['GameCreatorName'];
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
            $tt->leaf('td', $GameCreatorColumn);
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
            $tt->closenode();
        }
    }
    $tt->closenode(2); // tbody, table
}

?>