<?php

function flip_UOCMs_evaltile ($TileNumber) {
    // This function assigns to a given industry tile a numerical measure
    // of how desirable it is for the "orphan player" to flip that tile.
    // The more points it would earn the "orphan player", the more desirable
    // it is for the tile to be flipped. The more points it would earn
    // the "real players", considered collectively, the less desirable it is
    // for the tile to be flipped. The "real players" are not distinguished
    // from one another for this purpose - the player who is "losing" is not
    // any more likely to be given points. However, maximisation of points
    // for the "orphan player" is dominant over minimisation of points for
    // the "real players".
    global $GAME;
    if ( $GAME['RailPhase'] ) {
        $NumLinks   = $GAME['NumRailLinks'];
        $LinkStarts = $GAME['RailStarts'];
        $LinkEnds   = $GAME['RailEnds'];
    } else {
        $NumLinks   = $GAME['NumCanalLinks'];
        $LinkStarts = $GAME['CanalStarts'];
        $LinkEnds   = $GAME['CanalEnds'];
    }
    if ( $GAME['SpaceStatus'][$TileNumber] == 8 ) {
        $pv  = $GAME['TileVPValue'][$GAME['SpaceTile'][$TileNumber]][$GAME['TechLevels'][$TileNumber]-1];
        $opv = 0;
    } else if ( $GAME['SpaceStatus'][$TileNumber] < 8 ) {
        $pv  = 0;
        $opv = $GAME['TileVPValue'][$GAME['SpaceTile'][$TileNumber]][$GAME['TechLevels'][$TileNumber]-1];
    } else {
        die('Programmer error: Bad argument to flip_UOCMs_evaltile()');
    }
    $TileLocation = $GAME['spacetowns'][$TileNumber];
    for ($i=0; $i<$NumLinks; $i++) {
        if ( $LinkStarts[$i] == $TileLocation or
             $LinkEnds[$i] == $TileLocation
             ) {
            if ( $GAME['LinkStatus'][$i] == 8 ) {
                $pv++;
            } else if ( $GAME['LinkStatus'][$i] != 9 ) {
                $opv++;
            }
        }
    }
    $rtnval = 1000 * $pv - $opv;
    return $rtnval;
}

function flip_UOCMs () {
    // This function runs at the end of each phase if the game has been downsized. It simulates
    // attempting to flip orphan Cotton Mills, in order to avoid unfairness to players who have
    // attempted a Port strategy. It first makes a list of unflipped orphan Cotton Mills,
    // then it makes lists of "good" Ports (those that generate points for the "orphan player")
    // and of "bad" Ports (those that generate points only for the "real" players). Then
    // it goes through four steps to try and flip these Cotton Mills and good Ports.
    // "Desirability" of flipping a tile is evaluated first on how many points it would earn for
    // the "orphan player" (more points is good), then on how many points it would earn for the
    // the "real players" collectively (more points is bad). If two tiles of the same type are
    // tied for desirability, then their relative order is randomised.
    global $GAME;
    $UOCMs_numbers          = array();
    $UOCMs_desirability     = array();
    $UOCMs_networkcomponent = array();
    $UOCMs_random           = array();
    $good_UPs_numbers          = array();
    $good_UPs_desirability     = array();
    $good_UPs_networkcomponent = array();
    $good_UPs_random           = array();
    $bad_UPs_numbers          = array();
    $bad_UPs_desirability     = array();
    $bad_UPs_networkcomponent = array();
    $bad_UPs_random           = array();
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] == 8 and
             $GAME['SpaceCubes'][$i] and
             !$GAME['SpaceTile'][$i]
             ) {
            $desirability = flip_UOCMs_evaltile($i);
            if ( $desirability > 0 ) {
                $UOCMs_numbers[]          = $i;
                $UOCMs_desirability[]     = $desirability;
                $UOCMs_networkcomponent[] = $GAME['CoalNet'][$GAME['spacetowns'][$i]];
                $UOCMs_random[]           = $i;
            }
        }
        if ( $GAME['SpaceStatus'][$i] != 9 and
             $GAME['SpaceCubes'][$i] and
             $GAME['SpaceTile'][$i] == 3
             ) {
            $desirability = flip_UOCMs_evaltile($i);
            if ( $desirability > 0 ) {
                $good_UPs_numbers[]          = $i;
                $good_UPs_desirability[]     = flip_UOCMs_evaltile($i);
                $good_UPs_networkcomponent[] = $GAME['CoalNet'][$GAME['spacetowns'][$i]];
                $good_UPs_random[]           = $i;
            } else {
                $bad_UPs_numbers[]          = $i;
                $bad_UPs_desirability[]     = flip_UOCMs_evaltile($i);
                $bad_UPs_networkcomponent[] = $GAME['CoalNet'][$GAME['spacetowns'][$i]];
                $bad_UPs_random[]           = $i;
            }
        }
    }
    if ( !count($UOCMs_numbers) ) { return; }
    shuffle($UOCMs_random);
    shuffle($good_UPs_random);
    shuffle($bad_UPs_random);
    array_multisort( $UOCMs_desirability,SORT_DESC,
                     $UOCMs_random,
                     $UOCMs_numbers,
                     $UOCMs_networkcomponent
                     );
    array_multisort( $good_UPs_desirability,SORT_DESC,
                     $good_UPs_random,
                     $good_UPs_numbers,
                     $good_UPs_networkcomponent
                     );
    array_multisort( $bad_UPs_desirability,SORT_DESC,
                     $bad_UPs_random,
                     $bad_UPs_numbers,
                     $bad_UPs_networkcomponent
                     );
    for ($i=0; $i<count($good_UPs_numbers); $i++) {
        // First, for each "good" Port, I try to find the "best"
        // Cotton Mill that's connected to it, and flip them both.
        for ($j=0; $j<count($UOCMs_numbers); $j++) {
            if ( $UOCMs_networkcomponent[$j] == $good_UPs_networkcomponent[$i] ) {
                $GAME['AltGameTicker'] .= '7F'.
                                          letter_end_number($UOCMs_numbers[$j]).
                                          letter_end_number($good_UPs_numbers[$i]);
                fliptile($UOCMs_numbers[$j]);
                fliptile($good_UPs_numbers[$i]);
                array_splice($UOCMs_numbers,$j,1);
                array_splice($UOCMs_networkcomponent,$j,1);
                array_splice($good_UPs_numbers,$i,1);
                array_splice($good_UPs_networkcomponent,$i,1);
                $i--;
                break;
            }
        }
    }
    if ( !count($UOCMs_numbers) ) { return; }
    $NetworkComponents = array_unique($GAME['CoalNet']);
    $NetworkComponentsRenumbered = array();
    foreach ( $NetworkComponents as $key => $value ) {
        $NetworkComponentsRenumbered[] = $value;
            // array_unique() preserves keys, which I don't want.
    }
    for ($i=0; $i<count($NetworkComponentsRenumbered); $i++) {
        $PortCMBalance[$NetworkComponentsRenumbered[$i]] = 0;
    }
    for ($i=0; $i<count($UOCMs_numbers); $i++) {
        $PortCMBalance[$UOCMs_networkcomponent[$i]]++;
    }
    for ($i=0; $i<count($bad_UPs_numbers); $i++) {
        $PortCMBalance[$bad_UPs_networkcomponent[$i]]--;
    }
    while ( DMSaleSuccessProbability() == 1 ) {
        // Second, for as long as success in selling to the Distant Market is assured,
        // I look for Cotton Mills that are in a network component with too many Cotton
        // Mills compared to Ports, and I flip them via the Distant Market. I only need
        // to look for "bad" Ports from this point on, since following the previous step,
        // all unflipped "good" Ports are disconnected from the remaining Cotton Mills.
        for ($i=0; $i<count($UOCMs_numbers); $i++) {
            if ( $GAME['HasPort'][$UOCMs_networkcomponent[$i]] and
                 $PortCMBalance[$UOCMs_networkcomponent[$i]] > 0
                 ) {
                $GAME['AltGameTicker'] .= '7F'.letter_end_number($UOCMs_numbers[$i]).'9J';
                drawDMtile();
                fliptile($UOCMs_numbers[$i]);
                array_splice($UOCMs_numbers,$i,1);
                array_splice($UOCMs_networkcomponent,$i,1);
                continue 2;
            }
        }
        break;
    }
    while ( DMSaleSuccessProbability() == 1 ) {
        // Third, for as long as success in selling to the Distant Market is assured,
        // I try to find the "worst" Port and flip, via the Distant Market, any Cotton Mill
        // that's connected to it. The Port concerned is deleted from the array of
        // "bad" Ports when I do this - it can no longer be required, so I eliminate it
        // the better to identify the "worst" Port that I can still be compelled to use.
        for ($i=count($bad_UPs_numbers)-1; $i>=0; $i--) {
            for ($j=0; $j<count($UOCMs_numbers); $j++) {
                if ( $UOCMs_networkcomponent[$j] == $bad_UPs_networkcomponent[$i] ) {
                    $GAME['AltGameTicker'] .= '7F'.
                                              letter_end_number($UOCMs_numbers[$j]).
                                              '9J';
                    drawDMtile();
                    fliptile($UOCMs_numbers[$j]);
                    array_splice($UOCMs_numbers,$j,1);
                    array_splice($UOCMs_networkcomponent,$j,1);
                    array_splice($bad_UPs_numbers,$i,1);
                    array_splice($bad_UPs_networkcomponent,$i,1);
                    continue 3;
                }
            }
        }
        break;
    }
    for ($i=0; $i<count($UOCMs_numbers); $i++) {
        // Fourth, for each Cotton Mill, I try to find the "best" Port
        // that's connected to it, and flip them both.
        for ($j=0; $j<count($bad_UPs_numbers); $j++) {
            if ( $bad_UPs_networkcomponent[$j] == $UOCMs_networkcomponent[$i] ) {
                $GAME['AltGameTicker'] .= '7F'.
                                          letter_end_number($UOCMs_numbers[$i]).
                                          letter_end_number($bad_UPs_numbers[$j]);
                fliptile($UOCMs_numbers[$i]);
                fliptile($bad_UPs_numbers[$j]);
                array_splice($UOCMs_numbers,$i,1);
                array_splice($UOCMs_networkcomponent,$i,1);
                array_splice($bad_UPs_numbers,$j,1);
                array_splice($bad_UPs_networkcomponent,$j,1);
                $i--;
                break;
            }
        }
    }
    while ( $GAME['CottonDemand'] < 8 ) {
        // Finally, I try to flip the remaining Cotton Mills using the Distant Market,
        // until the demand for cotton runs out.
        for ($i=0; $i<count($UOCMs_numbers); $i++) {
            if ( $GAME['HasPort'][$UOCMs_networkcomponent[$i]] ) {
                $GAME['AltGameTicker'] .= '7F'.
                                          letter_end_number($UOCMs_numbers[$i]).
                                          '9J';
                if ( drawDMtile() ) {
                    fliptile($UOCMs_numbers[$i]);
                    array_splice($UOCMs_numbers,$i,1);
                    array_splice($UOCMs_networkcomponent,$i,1);
                    if ( !DMSaleSuccessProbability() ) {
                        $GAME['CottonDemand'] = 8;
                        $GAME['AltGameTicker'] .= '9J';
                    }
                    continue 2;
                } else {
                    break 2;
                }
            }
        }
        break;
    }
}

function TileDescription ($spacenum, $activeplayer, $ownrequired, $flipsrequired, $sentencestart, $sayspace) {
    // This function produces descriptions of industry tiles for the email, like
    // "Red (Arthur Dent)'s flipped Tech 4 Cotton Mill on the 3rd industry space in Manchester".
    global $GAME;
    if ( $activeplayer == $GAME['SpaceStatus'][$spacenum] ) {
        $thestring = $GAME['PossessivePronounLC_Eng'][$activeplayer].' ';
        if ( $ownrequired ) { $thestring .= 'own '; }
    } else if ( $GAME['SpaceStatus'][$spacenum] == 8 and $sentencestart ) {
        $thestring = 'The orphan ';
    } else if ( $GAME['SpaceStatus'][$spacenum] == 8 ) {
        $thestring = 'the orphan ';
    } else {
        $thestring = $GAME['PlayerFullName_Eng'][$GAME['SpaceStatus'][$spacenum]].'\'s ';
    }
    if ( !$GAME['SpaceCubes'][$spacenum] and $flipsrequired ) {
        $thestring .= 'flipped ';
    } else if ( $flipsrequired ) {
        $thestring .= 'unflipped ';
    }
    $thestring .= 'Tech '.$GAME['TechLevels'][$spacenum].' ';
    switch ( $GAME['SpaceTile'][$spacenum] ) {
        case 0: $thestring .= 'Cotton Mill'; break;
        case 1: $thestring .= 'Coal Mine';   break;
        case 2: $thestring .= 'Iron Works';  break;
        case 3: $thestring .= 'Port';        break;
        case 4: $thestring .= 'Shipyard';    break;
    }
    if ( $sayspace ) {
        $thestring .= ' on the'.
                      $GAME['spacenumbers'][$spacenum].
                      ' space in '.
                      $GAME['locationnames'][$GAME['spacetowns'][$spacenum]];
    }
    return $thestring;
}

function endgamescoring () {
    global $GAME;
    if ( isset($GAME['EndScoringDone']) ) { return; }
    $GAME['EndScoringDone'] = true;
        // This is a precaution - I aim to avoid calling this function
        // more than once, but if I slip up I want to cover myself.
    if ( $GAME['CurrentPlayers'] < $GAME['OriginalPlayers'] ) { flip_UOCMs(); }
        // flip_UOCMs: see above
    $TheOutput = '<p>Rail Phase scoring occurred at '.
                 date('Y-m-d H:i:s').
                 '. The players\' scores beforehand are as follows:</p><ul>';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            $TheOutput .= '<li>'.
                          $GAME['PlayerFullName_Eng'][$i].
                          ': '.
                          $GAME['VictoryPoints'][$i].
                          '</li>';
        }
    }
    $TheOutput .= '</ul><p>First of all, rail links are scored.</p><ul>';
    $RailPoints = array(0, 0, 0, 0, 0);
    $TilePoints = array(0, 0, 0, 0, 0);
        // Initialise the number of points to be given at 0.
    $NumFlippedTiles = $GAME['LocationAutoValue'];
        // Blackpool, Scotland, Yorkshire et al. all have automatic value 2.
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] != 9 and
             !$GAME['SpaceCubes'][$i]
             ) {
            $NumFlippedTiles[$GAME['spacetowns'][$i]]++;
                // For each flipped industry tile, increment the value of the location of the tile.
        }
    }
    for ($i=0; $i<$GAME['NumRailLinks']; $i++) {
        if ( $GAME['LinkStatus'][$i] < 8 and
             $GAME['PlayerExists'][$GAME['LinkStatus'][$i]]
             ) {
            // I'm not sure why the PlayerExists condition is here. I think it might be a
            // hangover from earlier bad design decisions, and no longer necessary.
            $pointstoadd = $NumFlippedTiles[$GAME['RailStarts'][$i]] +
                           $NumFlippedTiles[$GAME['RailEnds'][$i]];
            if ( $pointstoadd == 1 ) { $pluraltext = '';  }
            else                     { $pluraltext = 's'; }
            $RailPoints[$GAME['LinkStatus'][$i]] += $pointstoadd;
                // For each owned rail link, award points equal to the sum
                // of the values of the start and end locations.
            $TheOutput .= '<li>'.
                          $GAME['PlayerFullName_Eng'][$GAME['LinkStatus'][$i]].
                          ' receives '.
                          $pointstoadd.
                          ' point'.
                          $pluraltext.
                          ' for '.
                          $GAME['PossessivePronounLC_Eng'][$GAME['LinkStatus'][$i]].
                          ' rail link between '.
                          $GAME['locationnames'][$GAME['RailStarts'][$i]].
                          ' and '.
                          $GAME['locationnames'][$GAME['RailEnds'][$i]].
                          '.</li>';
        }
    }
    $TheOutput .= '</ul><p>The number of points scored from rail links is as follows:</p><ul>';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            $TheOutput .= '<li>'.
                          $GAME['PlayerFullName_Eng'][$i].
                          ': '.
                          $RailPoints[$i].
                          '</li>';
        }
    }
    $TheOutput .= '</ul><p>Secondly, industry tiles are scored.</p><ul>';
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] < 8 and
             !$GAME['SpaceCubes'][$i] and
             $GAME['PlayerExists'][$GAME['SpaceStatus'][$i]]
             ) {
            // I'm not sure why the PlayerExists condition is here. I think it might be a
            // hangover from earlier bad design decisions, and no longer necessary.
            $pointstoadd = $GAME['TileVPValue'][$GAME['SpaceTile'][$i]][$GAME['TechLevels'][$i]-1];
            if ( $pointstoadd == 1 ) { $pluraltext = '';  }
            else                     { $pluraltext = 's'; }
            $TilePoints[$GAME['SpaceStatus'][$i]] += $pointstoadd;
                // For each flipped non-orphan industry tile, award points to the owner.
            $TheOutput .= '<li>'.
                          $GAME['PlayerFullName_Eng'][$GAME['SpaceStatus'][$i]].
                          ' receives '.
                          $pointstoadd.
                          ' point'.
                          $pluraltext.
                          ' for '.
                          TileDescription($i, $GAME['SpaceStatus'][$i], 0, 1, 0, 1).
                          '.</li>';
        }
    }
    $TheOutput .= '</ul><p>The number of points scored from industry tiles is as follows:</p><ul>';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            $TheOutput .= '<li>'.
                          $GAME['PlayerFullName_Eng'][$i].
                          ': '.
                          $TilePoints[$i].
                          '</li>';
        }
    }
    $TheOutput .= '</ul><p>Finally, the players receive the following numbers of points for their remaining funds:</p><ul>';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            if ( $GAME['Money'][$i] >= 0 ) {
                $MoneyRemainder = $GAME['Money'][$i] % 10;
                $MoneyPoints[$i] = ($GAME['Money'][$i] - $MoneyRemainder) / 10;
                $MoneyString[$i] = '£'.$GAME['Money'][$i];
            } else {
                $MoneyPoints[$i] = 0;
                $NegMoney = -$GAME['Money'][$i];
                $MoneyString[$i] = '-£'.$NegMoney;
            }
            if ( $MoneyPoints[$i] == 1 ) { $pluraltext = ''; }
            else                         { $pluraltext = 's'; }
            $TheOutput .= '<li>'.
                          $GAME['PlayerFullName_Eng'][$i].
                          ': '.
                          $MoneyPoints[$i].
                          ' point'.
                          $pluraltext.
                          ', for having '.
                          $MoneyString[$i].
                          ' remaining</li>';
        }
    }
    $TheOutput .= '</ul>';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            $GAME['VictoryPoints'][$i] += $RailPoints[$i] + $TilePoints[$i] + $MoneyPoints[$i];
                // Give the players their points now that we have tallied them up.
        }
    }
    $MustKnowMoney = false;
    $MustDoTurnOrder = false;
        // If the second and third tie breakers aren't necessary,
        // we don't bother mentioning them.
    for ($i=0; $i<MAX_PLAYERS-1; $i++) {
        for ($j=$i+1; $j<MAX_PLAYERS; $j++) {
            if ( $GAME['PlayerExists'][$i] and
                 $GAME['PlayerExists'][$j] and
                 $GAME['VictoryPoints'][$i] == $GAME['VictoryPoints'][$j] and
                 $GAME['IncomeSpace'][$i] == $GAME['IncomeSpace'][$j]
                 ) {
                $MustKnowMoney = true;
                if ( $GAME['Money'][$i] == $GAME['Money'][$j] ) {
                    $MustDoTurnOrder = true;
                }
            }
        }
    }
    if ( $MustDoTurnOrder ) {
        require_once(HIDDEN_FILES_PATH.'turnorderresource.php');
        DoTurnOrder(0);
            // Recalculate the turn order, but don't give out income or collect interest
        $TO_NameArray = array();
        for ($i=0;$i<$GAME['CurrentPlayers'];$i++) {
            if ( $GAME['PlayerExists'][$GAME['TurnOrder'][$i]] ) {
                $TO_NameArray[] = $GAME['PlayerFullName_Eng'][$GAME['TurnOrder'][$i]];
            }
        }
        $TheOutput .= '<p>In order to determine player positions, it is necessary to calculate the turn order for the theoretical next turn. This turn order is: '.
                      implode(', ', $TO_NameArray).
                      '.</p>';
    }
    $points = $GAME['VictoryPoints'];
    $space  = $GAME['IncomeSpace'];
    $cash   = $GAME['Money'];
        // Copies of the variables that affect positioning, for sorting purposes.
    $FlippedTurnOrder = array_flip(str_split($GAME['TurnOrder'], 1));
        // Get an array telling us where each player is in the turn order.
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        $ReindexedFlippedTurnOrder[$i] = $FlippedTurnOrder[$i];
            // Without this step, $FlippedTurnOrder has the correct key-value pairs, but they may
            // appear in the wrong order. This results in the call to array_multisort going wrong.
    }
    $whoarray = array(0, 1, 2, 3, 4);
        // This array will tell us which player is which after sorting.
    $CopyPlayerExists = str_split($GAME['PlayerExists'],1);
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $CopyPlayerExists[$i] = (int)$CopyPlayerExists[$i];
            // Necessary? Might be able to get rid of this without changing behaviour.
    }
    array_multisort( $CopyPlayerExists          , SORT_DESC ,
                     $points                    , SORT_DESC ,
                     $space                     , SORT_DESC ,
                     $cash                      , SORT_DESC ,
                     $ReindexedFlippedTurnOrder , SORT_ASC  ,
                     $whoarray
                    );
    $positionlabels = array('1st', '2nd', '3rd', '4th', '5th');
    $TheOutput .= '<p>Here are the results of the game:</p><ul>';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$whoarray[$i]] ) {
            if ( $points[$i] == 1 ) { $pluraltext = '';  }
            else                    { $pluraltext = 's'; }
            $TheOutput .= '<li>'.
                          $positionlabels[$i].
                          ': '.
                          $GAME['PlayerFullName_Eng'][$whoarray[$i]].
                          ': '.
                          $points[$i].
                          ' VP'.
                          $pluraltext.
                          '; Income Space '.
                          $space[$i];
            if ( $MustKnowMoney ) {
                $TheOutput .= '; '.$MoneyString[$whoarray[$i]].' remaining';
            }
            if ( $MustDoTurnOrder ) {
                $TheOutput .= '; '.$positionlabels[$ReferenceTurnOrder[$i]].' for turn order';
            }
            $TheOutput .= '</li>';
            $Score = 100*$points[$i] + $space[$i];
            if ( !$GAME['PlayerMissing'][$whoarray[$i]] ) {
                require_once(HIDDEN_FILES_PATH.'createpgr.php');
                CreatePGR(0, 0, $Score, $i, $whoarray[$i]);
                    // Change this player's record for this game in the database
                    // to include the result and score
            }
        }
    }
    dbquery(DBQUERY_WRITE, 'CALL "CalculateRanks_Rating"()');
    dbquery(DBQUERY_WRITE, 'CALL "CalculateRanks_GamesCompleted"()');
        // Get the database to recalculate rankings.
    if ( $GAME['CurrentPlayers'] != 1 ) {
        // Congratulating the only player left in the game would look kind of silly.
        $TheOutput .= '</ul><p>Congratulations to '.$GAME['PlayerName_Eng'][$whoarray[0]].'!</p>';
    }
    $GAME['GameStatus']    = 'Finished';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] and
             !$GAME['PlayerMissing'][$i] and
             $GAME['EmailAtEnd'][$i] and
             $GAME['Email'][$i] != ''
             ) {
            // Email all the game's players with a summary of scoring.
            $subject = 'Game number '.$GAME['GameID'].' has finished';
            $body = '<p>This is an automated message. Game number '.
                    $GAME['GameID'].
                    ' has finished. Here is the URL of the game page:</p><p><a href="'.
                    SITE_ADDRESS.
                    'board.php?GameID='.
                    $GAME['GameID'].
                    '">'.
                    SITE_ADDRESS.
                    'board.php?GameID='.
                    $GAME['GameID'].
                    '</a></p><p>Here is a summary of the end-game scoring:</p>'.
                    $TheOutput.
                    EMAIL_FOOTER;
            send_email($subject, $body, $GAME['Email'][$i], null);
        }
    }
    $WatchersQuery = 'SELECT "User"."Email", "User"."EmailAtEnd" FROM "WatchedGame" LEFT JOIN "User" ON "WatchedGame"."User" = "User"."UserID" WHERE "WatchedGame"."Game" = '.$GAME['GameID'];
        // Find out who is watching the game, so that they can be sent emails as well.
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] and !$GAME['PlayerMissing'][$i] ) {
            $WatchersQuery .= ' AND "WatchedGame"."User" <> '.$GAME['PlayerUserID'][$i];
                // If any of the game's players are watching the game,
                // there's no point in sending them two emails.
        }
    }
    $QueryResult = dbquery(DBQUERY_READ_RESULTSET, $WatchersQuery);
    while ( $row = db_fetch_assoc( $QueryResult ) ) {
        if ( $row['EmailAtEnd'] and
             $row['Email'] != ''
             ) {
            $subject = 'Game number '.$GAME['GameID'].' has finished';
            $body = '<p>This is an automated message. Game number '.
                    $GAME['GameID'].
                    ' has finished. You are receiving this message because you are watching that game. Here is the URL of the game page:</p><p><a href="'.
                    SITE_ADDRESS.
                    'board.php?GameID='.
                    $GAME['GameID'].
                    '">'.
                    SITE_ADDRESS.
                    'board.php?GameID='.
                    $GAME['GameID'].
                    '</a></p><p>Here is a summary of the end-game scoring:</p>'.
                    $TheOutput.
                    EMAIL_FOOTER;
            send_email($subject, $body, $row['Email'], null);
        }
    }
}

function canalphasescoring () {
    global $GAME;
    if ( $GAME['CurrentPlayers'] < $GAME['OriginalPlayers'] ) { flip_UOCMs(); }
        // flip_UOCMs: see above
    $CanalPoints = array(0,0,0,0,0);
    $TilePoints  = array(0,0,0,0,0);
        // Initialise the number of points to be given at 0.
    $NumFlippedTiles = $GAME['LocationAutoValue'];
        // Blackpool, Scotland, Yorkshire et al. all have automatic value 2.
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] != 9 and !$GAME['SpaceCubes'][$i] ) {
            $NumFlippedTiles[$GAME['spacetowns'][$i]]++;
                // For each flipped industry tile, increment the value of the location of the tile.
        }
    }
    for ($i=0; $i<$GAME['NumCanalLinks']; $i++) {
        if ( $GAME['LinkStatus'][$i] < 8 ) {
            $CanalPoints[$GAME['LinkStatus'][$i]] += $NumFlippedTiles[$GAME['CanalStarts'][$i]] +
                                                     $NumFlippedTiles[$GAME['CanalEnds'][$i]];
                // For each owned canal link, award points equal to the sum
                // of the values of the start and end locations.
        }
    }
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] == 8 ) {
            DestroyTile($i,0,0);
                // Remove orphan tiles from the board. For the sake of efficiency, Port
                // locations are not recalculated; this is done at the end.
        } else if ( $GAME['SpaceStatus'][$i] != 9 ) {
            if ( !$GAME['SpaceCubes'][$i] ) {
                $TilePoints[$GAME['SpaceStatus'][$i]] += $GAME['TileVPValue'][$GAME['SpaceTile'][$i]][$GAME['TechLevels'][$i]-1];
                    // For each flipped non-orphan industry tile, award points to the owner.
            }
            if ( $GAME['TechLevels'][$i] == 1 ) {
                DestroyTile($i,0,0);
                    // Remove Tech Level 1 tiles from the board. For the sake of efficiency, Port
                    // locations are not recalculated; this is done at the end.
            }
        }
    }
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $GAME['VictoryPoints'][$i] = $CanalPoints[$i] + $TilePoints[$i];
            // Give the players their points now that we have tallied them up.
        $GAME['RemainingTiles'][5][$i] = 14;
        // Return all link markers to players.
    }
    $GAME['ShuffledDeck'] = array();
    for ($i=0; $i<count($GAME['carddetailarrayb']); $i++) {
        if ( $GAME['CurrentPlayers'] > 2 or !in_array($i,$GAME['CardsToRemove']) ) {
            $GAME['ShuffledDeck'][] = $i;
                // Create the deck of cards to be used in the Rail Phase.
        }
    }
    shuffle($GAME['ShuffledDeck']);
        // Shuffle the deck of cards.
    //  $TranslationString = '';
    //  $TranslationSymbols = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXY';
    //  $TranslationSymbolsProgress = 0;
    //  $LastTranslationNumber = $GAME['carddetailarrayb'][0];
    //  for ($i=0;$i<count($GAME['carddetailarrayb']);$i++) {
    //      if ( $GAME['carddetailarrayb'][$i] == $LastTranslationNumber ) {
    //          $TranslationString .= $TranslationSymbols[$TranslationSymbolsProgress];
    //      } else {
    //          $LastTranslationNumber = $GAME['carddetailarrayb'][$i];
    //          $TranslationSymbolsProgress++;
    //          $i--;
    //      }
    //  }
    //  for ($i=0;$i<count($GAME['ShuffledDeck']);$i++) {
    //      $GAME['RandomLog'] .= $TranslationString[$GAME['ShuffledDeck'][$i]];
    //  }
    //      // The above commented-out section of code allows the ordering of the cards
    //      // in the deck to be recorded. At present, it is not in use. The character
    //      // 'Z' added to RandomLog (on the next line) stands for "card ordering not recorded".
        $GAME['RandomLog'] .= 'Z';
    for ($i=0; $i<5; $i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            for ($j=0; $j<8; $j++) {
                $GAME['Cards'][$i][$j] = array_pop($GAME['ShuffledDeck']);
            }
            sort($GAME['Cards'][$i]);
                // Deal 8 cards to each player, and sort them into the standard order.
        }
    }
    if ( $GAME['CurrentPlayers'] == 2 ) { $GAME['ShuffledTiles'] = '12222334';     }
    else                                { $GAME['ShuffledTiles'] = '001122223334'; }
        // Create the deck of Distant Market tiles to be used in the Rail Phase.
    $GAME['ShuffledTiles'] = str_shuffle($GAME['ShuffledTiles']);
    $GAME['RandomLog'] .= $GAME['ShuffledTiles'];
        // Shuffle the Distant Market tiles and record the order in RandomLog (needed for game log output).
    $GAME['RailPhase'] = 1;
    $GAME['Round']     = 0;
        // Start of the Rail Phase. Round will be incremented when we do income/debt.
    if ( $GAME['CurrentPlayers'] == 4 ) { $GAME['NumRounds'] = 8; }
    else                                { $GAME['NumRounds'] = 10; }
        // Set the number of rounds for the Rail Phase.
    $GAME['DiscardPile']  = array();
        // Empty the discard pile.
    $GAME['CottonDemand'] = 0;
    $GAME['TilesDrawn']   = 'None';
        // Put the Cotton Demand marker back to the start of the track,
        // and empty the Distant Market tiles discard pile.
    $GAME['LinkStatus']   = '';
    for ($i=0; $i<$GAME['NumRailLinks']; $i++) {
        $GAME['LinkStatus'] .= '9';
    }
        // Reset the LinkStatus variable used for recording who owns which links. Initially no links are owned.
    $GAME['CoalNet'] = array();
    $HasLinkedComponents = '';
    for ($i=0; $i<$GAME['NumTowns']; $i++) {
        $GAME['CoalNet'][] = $i;
        $HasLinkedComponents .= '0';
    }
    $GAME['HasLinkedToTown'] = array( $HasLinkedComponents,
                                      $HasLinkedComponents,
                                      $HasLinkedComponents,
                                      $HasLinkedComponents,
                                      $HasLinkedComponents
                                      );
        // Reset the coal network now that all locations are disconnected from one another again;
        // also change HasLinkedToTown since no player has linked anywhere.
    DestroyTile(100, 0, 1);
        // Recalculate which locations have Ports (for the sake of efficiency,
        // this is not done at every removal of a Port during scoring).
}

?>