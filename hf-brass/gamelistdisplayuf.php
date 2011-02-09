<?php

function gamelistdisplayuf ( tagtree &$tt,
                             $ThePlayerID,
                             $PlayerName,
                             $PlayerPronoun,
                             $DisplayNotification,
                             $StartPoint,
                             $MaxResults,
                             $Pagenum
                             ) {
    if ( $DisplayNotification ) { $MaxResults++; }
    $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                            'SELECT "PlayerGameRcd"."Colour" AS "UsersColour", "PlayerGameRcd"."Inherited", "PlayerGameRcd"."GameCounts", "PlayerGameRcd"."GameResult", "PlayerGameRcd"."NumLongTurns", "Game"."GameID", "Game"."Friendly", "Game"."GameName", "Game"."GTitleDeletedByAdmin", "Game"."LastMove", "Game"."GameCreator", "Game"."OriginalPlayers", "Game"."CurrentPlayers", "Game"."GameStatus", "GameVersion"."ShortVersionName", "GameVersionGroup"."VersionName", "GameVersion"."VersionNameSuffix", "GameVersion"."Creators", "User"."Name" AS "GameCreatorName", "GameInProgress"."PlayerToMove", "GameInProgress"."AbortVote", "GameInProgress"."KickVote", ROUND("PGRScore"."Score"/100, 2) AS "Score", "ParticipantRcd"."CurrentOccupant" FROM "PlayerGameRcd" JOIN "Game" ON "PlayerGameRcd"."Game" = "Game"."GameID" JOIN "GameVersion" ON "Game"."GVersion" = "GameVersion"."VersionID" JOIN "GameVersionGroup" ON "GameVersion"."VersionGroup" = "GameVersionGroup"."VersionGroupID" JOIN "User" ON "Game"."GameCreator" = "User"."UserID" LEFT JOIN "GameInProgress" ON "Game"."GameID" = "GameInProgress"."Game" LEFT JOIN "PGRScore" ON "PlayerGameRcd"."Game" = "PGRScore"."Game" AND "PlayerGameRcd"."User" = "PGRScore"."User" LEFT JOIN "PlayerGameRcd" AS "ParticipantRcd" ON "ParticipantRcd"."User" = :me: AND "Game"."GameID" = "ParticipantRcd"."Game" WHERE "PlayerGameRcd"."User" = :user: AND "PlayerGameRcd"."GameResult" NOT IN (\'Playing\', \'Hide\') ORDER BY "Game"."LastMove" DESC LIMIT :startpoint:, :maxresults:',
                            'user'       , $ThePlayerID          ,
                            'me'         , $_SESSION['MyUserID'] ,
                            'startpoint' , $StartPoint           ,
                            'maxresults' , $MaxResults
                            );
    if ( $DisplayNotification ) {
        $MaxResults--;
        if ( $QueryResult === 'NONE' ) {
            $tt->leaf('p', 'None');
            return;
        }
        $fb = fragment::blank();
        $PaginationBar = array($fb, $fb);
    } else {
        require_once(HIDDEN_FILES_PATH.'paginate.php');
        $CountQueryResult = dbquery( DBQUERY_READ_INTEGER,
                                     'CALL "CountUsersGamesFinished"(:user:)',
                                     'user' , $ThePlayerID
                                     );
        if ( !$CountQueryResult ) { return false; }
        $PaginationBar = paginationbar( 'game',
                                        'games',
                                        SITE_ADDRESS.'oldgames.php',
                                        array('UserID' => $ThePlayerID),
                                        100,
                                        $Pagenum,
                                        $CountQueryResult
                                        );
        if ( $QueryResult === 'NONE' ) {
            $tt->append($PaginationBar[0]);
            return;
        }
    }
    $PlayerColours = array('FFC18A', 'FFFFAF', '9FFF9F', 'FFC6FF', 'C4C4C4');
    $ColourNames = array( transtext('_colourRed'),
                          transtext('_colourYellow'),
                          transtext('_colourGreen'),
                          transtext('_colourPurple'),
                          transtext('_colourGrey')
                          );
    $TranslatedResults = array( 'Finished 1st'     => transtext('_ugResult1st')     ,
                                'Finished 2nd'     => transtext('_ugResult2nd')     ,
                                'Finished 3rd'     => transtext('_ugResult3rd')     ,
                                'Finished 4th'     => transtext('_ugResult4th')     ,
                                'Finished 5th'     => transtext('_ugResult5th')     ,
                                'Game Aborted'     => transtext('_ugResultAborted') ,
                                'Quit'             => transtext('_ugResultQuit')    ,
                                'Kicked by Admin'  => transtext('_ugResultKickA')   ,
                                'Kicked by System' => transtext('_ugResultKickS')   ,
                                'Kicked by Vote'   => transtext('_ugResultKickV')
                                );
    $frag = fragment::blank();
    $OldGameID = 0;
    $i = 0;
    while ( $row = db_fetch_assoc($QueryResult) ) {
        if ( $row['GameID'] != $OldGameID ) {
            $i++;
            if ( $i > $MaxResults ) {
                // "This table shows only X's last 20 games" etc
                $tt->leaf( 'p',
                           str_replace( array( '\username' ,
                                               '\pronoun'  ,
                                               '\userid'
                                               ),
                                        array( $PlayerName    ,
                                               $PlayerPronoun ,
                                               $ThePlayerID
                                               ),
                                        transtext('_ugFinishedNote')
                                        )
                           );
                break;
            }
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
            $frag->opennode('tr', $RowTagAttributes);
            $version_name = vname($row['VersionName'], $row['VersionNameSuffix']);
            $frag->leaf( 'td',
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
            $frag->leaf( 'td',
                         '<a href="board.php?GameID='.
                             $row['GameID'].
                             '">'.
                             $row['GameName'].
                             '</a>',
                         'style="border-left: none; padding-left: 0px; text-align: left;"'
                         );
            $frag->leaf( 'td',
                         '<a href="userdetails.php?UserID='.
                             $row['GameCreator'].
                             '">'.
                             $row['GameCreatorName'].
                             '</a>'
                         );
            if ( $row['Friendly'] ) {
                $frag->leaf('td', transtext('^Yes'), 'bgcolor="#9FFF9F"');
            } else {
                $frag->leaf('td', transtext('^No') , 'bgcolor="#FFC18A"');
            }
            $frag->leaf( 'td',
                         $row['CurrentPlayers'].
                             ( ( $row['CurrentPlayers'] == $row['OriginalPlayers'] ) ?
                               '' :
                               '&#8239;/&#8239;'.$row['OriginalPlayers']
                               )
                       );
            $frag->leaf('td', $row['GameStatus']);
            $lmtime = strtotime($row['LastMove']);
            $frag->opennode('td');
            $frag->leaf('span', date('Y', $lmtime), 'style="font-size: 50%;"');
            $frag->text(date('M-d', $lmtime));
            $frag->closenode();
            $frag->leaf( 'td',
                         $ColourNames[$row['UsersColour']],
                         'bgcolor="#'.
                             $PlayerColours[$row['UsersColour']].
                             '"'
                         );
            $frag->leaf('td', $TranslatedResults[$row['GameResult']]);
            $frag->leaf('td', $row['NumLongTurns']);
            $frag->leaf( 'td',
                         ( is_null($row['Score']) ?
                           'n/a' :
                           $row['Score']
                           )
                         );
            $frag->leaf( 'td',
                         ( $row['GameCounts'] ) ?
                           transtext('^Yes') :
                           transtext('^No')
                         );
            $frag->leaf( 'td',
                         ( $row['Inherited'] ) ?
                           transtext('^Yes') :
                           transtext('^No')
                         );
            $frag->closenode();
        }
    }
    $tt->append($PaginationBar[0]);
    $tt->opennode('table', 'class="table_extra_horizontal_padding"');
    $tt->opennode('thead');
    $tt->opennode('tr');
    $tt->leaf('th', 'Name', 'colspan=2 style="width: 175px;"');
    $tt->leaf('th', 'Creator');
    $tt->leaf( 'th',
               'Frdly',
               'title="Whether the game was a &quot;friendly&quot; game."'
               );
    $tt->leaf( 'th',
               'P',
               'title="The number of players the game had when it finished (or currently has, if it hasn\'t finished) / the number of players it had when it started."'
               );
    $tt->leaf('th', 'Status');
    $tt->leaf('th', 'Last Move', 'style="min-width: 75px;"');
    $tt->leaf( 'th',
               transtext('_ugColColour'),
               'title="The colour '.
                   $PlayerName.
                   ' played as in this game."'
               );
    $tt->leaf( 'th',
               transtext('_ugColResult'),
               'style="min-width: 90px;" title="The result '.
                   $PlayerName.
                   ' achieved in this game. Usually this will be a rank achieved at the final scoring."'
               );
    $tt->leaf( 'th',
               transtext('_ugColLT'),
               'title="&quot;Long Turns&quot;: The number of times in this game '.
                   $PlayerName.
                   ' took longer than Time Limit A to make a decision."'
               );
    $tt->leaf( 'th',
               transtext('_ugColScore'),
               'title="The score '.
                   $PlayerName.
                   ' achieved in this game. The decimal part gives the player\'s position on the income track."'
               );
    $tt->leaf( 'th',
               'Counts',
               'title="Whether the game counts towards '.
                   $PlayerName.
                   '\'s statistics. Sometimes if you join a game in the middle, it does not contibute (either negatively or positively) toward your personal statistics."'
               );
    $tt->leaf( 'th',
               'Inhrtd',
               'title="Whether '.
                   $PlayerName.
                   ' inherited this game, that is, took over from another player who had left the game."'
               );
    $tt->closenode(2); // tr, thead
    $tt->opennode('tbody');
    $tt->append($frag);
    $tt->closenode(2); // tbody, table
    $tt->append($PaginationBar[1]);
}

?>