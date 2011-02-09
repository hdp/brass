<?php

function buildtile($wheretobuild,$whattobuild,$coalsource,$ironsource,$card,$doublebuild,$thecardA,$thecardB) {
    global $GAME,$SystemActing;
    $errorlist = '';
    $TheTechLevel = $GAME['TechLevelArray'][$whattobuild][$GAME['RemainingTiles'][$whattobuild][$GAME['PlayerToMove']]];
    $carddetail = $GAME['carddetailarray'][$GAME['Cards'][$GAME['PlayerToMove']][$card]];
    if ( $coalsource == 50 ) { $altcoalsource = 99; }
    else                     { $altcoalsource = $coalsource; }
    if ( $ironsource == 50 ) { $altironsource = 99; }
    else                     { $altironsource = $ironsource; }
    if ( $TheTechLevel == 9 ) {
        $errorlist = transtext('memOutOfTiles').'<br>';
            // "You have run out of industry tiles of the chosen type."
    } else {
        if ( $GAME['HasBuilt'][$GAME['PlayerToMove']] and
             !$doublebuild and
             $GAME['Cards'][$GAME['PlayerToMove']][$card] > $GAME['TopLocationCard'] and
             !$GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['spacetowns'][$wheretobuild]] and
             !$GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['spacetowns'][$wheretobuild]]
             ) {
            $CannotBuildHere = true;
            for ($i=0;$i<count($GAME['GeneralisedVCs']);$i++) {
                if ( ( $GAME['spacetowns'][$wheretobuild] == $GAME['GeneralisedVCs'][$i][0] and
                       ( $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$i][1]] or
                         $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$i][1]]
                         )
                       ) or
                     ( ( ( ( $GAME['SpecialRules'] & 1 ) and
                           $GAME['GeneralisedVCs'][$i][2]
                           ) or
                         $GAME['GeneralisedVCs'][$i][2] == 2
                         ) and
                       $GAME['spacetowns'][$wheretobuild] == $GAME['GeneralisedVCs'][$i][1] and
                       ( $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$i][0]] or
                         $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$i][0]]
                         )
                       )
                     ) {
                    $CannotBuildHere = false;
                }
            }
            if ( $CannotBuildHere ) {
                if ( $GAME['RailPhase'] ) {
                    $errorlist .= transtext('memIndyCardBadR').'<br>';
                        // "You cannot use an industry card to build in that location,
                        // as you have neither connected to the location nor built a tile there."
                } else {
                    $errorlist .= transtext('memIndyCardBadC').'<br>';
                        // "You cannot use an industry card to build in that location,
                        // as you have not connected to the location."
                }
            }
        }
        if ( !$GAME['SpaceAlwaysExists'][$wheretobuild] and
             ( $GAME['ModularBoardParts'] & $GAME['SpaceExistenceArray'][$wheretobuild] ) != $GAME['SpaceExistenceArray'][$wheretobuild]
             ) {
            $errorlist .= transtext('memSpaceNotThere').'<br>';
                // "That industry space does not exist (or it no longer exists) in this game."
        }
        if ( $GAME['tileindustries'][$wheretobuild] != $whattobuild and
             ( $whattobuild > 1 or $GAME['tileindustries'][$wheretobuild] != 5 ) and
             ( ( $whattobuild and
                 $whattobuild != 3
                 ) or
               $GAME['tileindustries'][$wheretobuild] != 6
               ) and
             ( ( $whattobuild != 2 and
                 $whattobuild != 3
                 ) or
               $GAME['tileindustries'][$wheretobuild] != 7
               )
             ) {
            $errorlist .= transtext('memWrongTileType').'<br>';
                // "The tile that you attempted to build cannot be built in the chosen industry space."
        }
        if ( !$GAME['RailPhase'] and
             $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['spacetowns'][$wheretobuild]] and
             $GAME['SpaceStatus'][$wheretobuild] != $GAME['PlayerToMove']
             ) {
            $errorlist .= transtext('memAlreadyInTown').'<br>';
                // "You have already built an industry tile in that location."
        }
        $StrategicBlockAttempt = false;
        for ($i=0;$i<count($GAME['GeneralisedNoStratBlock']);$i++) {
            if ( $whattobuild == $GAME['GeneralisedNoStratBlock'][$i][0] and
                 $GAME['SpaceStatus'][$GAME['GeneralisedNoStratBlock'][$i][1]] == 9 and
                 $wheretobuild == $GAME['GeneralisedNoStratBlock'][$i][2]
                 ) {
                $StrategicBlockAttempt = true;
            }
        }
        if ( $StrategicBlockAttempt ) {
            $errorlist .= transtext('memTacticalBlock').'<br>';
        }
        if ( $GAME['SpaceStatus'][$wheretobuild] != 9 and
             $GAME['SpaceTile'][$wheretobuild] != $whattobuild
             ) {
            $errorlist .= transtext('memOvrSameType').'<br>';
        }
        if ( $GAME['SpaceStatus'][$wheretobuild] != 9 and
             $GAME['TechLevels'][$wheretobuild] >= $TheTechLevel
             ) {
            $errorlist .= transtext('memOvrTechLevel').'<br>';
        }
        if ( $GAME['SpaceStatus'][$wheretobuild] == 8 and
             !$GAME['SpaceTile'][$wheretobuild]
             ) {
            $errorlist .= transtext('memOvrCM').'<br>';
                // "You can never build over an opponent's Cotton Mill."
                // (Prevents building over an orphan Cotton Mill.)
        }
        if ( $GAME['SpaceStatus'][$wheretobuild] != 9 and
             $GAME['SpaceStatus'][$wheretobuild] != 8 and
             $GAME['SpaceStatus'][$wheretobuild] != $GAME['PlayerToMove']
             ) {
            switch ( $GAME['SpaceTile'][$wheretobuild] ) {
                case 0:
                    $errorlist .= transtext('memOvrCM').'<br>';
                        // "You can never build over an opponent's Cotton Mill."
                    break;
                case 1:
                    if ( ( $GAME['CurrentPlayers'] > 2 and
                           $GAME['CoalDemand'] < 8
                           ) or
                         ( $GAME['CurrentPlayers'] == 2 and
                           $GAME['CoalDemand'] < 6
                           ) or
                         $GAME['CoalInLancs']
                         ) {
                        $errorlist .= transtext('memOvrCoalCubes').'<br>';
                            // "You cannot currently build over an opponent's Coal Mine:
                            // the cube scarcity condition is not satisfied."
                    }
                    if ( $GAME['SpecialRules'] & 4 ) {
                        for ($i=0;$i<$GAME['NumIndustrySpaces'];$i++) {
                            if ( $GAME['spacetowns'][$i] == $GAME['spacetowns'][$wheretobuild] and
                                 ( $GAME['tileindustries'][$i] == 1 or
                                   $GAME['tileindustries'][$i] == 5
                                   ) and
                                 $GAME['SpaceStatus'][$i] == 9
                               ) {
                                $errorlist .= transtext('memOvrCoalMCOR').'<br>';
                                    // There is a free space in the same location for a Coal Mine.
                                    // You cannot build over your opponent's Coal Mine here.
                                break;
                            }
                        }
                    }
                    break;
                case 2:
                    if ( ( $GAME['CurrentPlayers'] > 2 and
                           $GAME['IronDemand'] < 8
                           ) or
                         ( $GAME['CurrentPlayers'] == 2 and
                           $GAME['IronDemand'] < 6
                           ) or
                         $GAME['IronInLancs']
                         ) {
                        $errorlist .= transtext('memOvrIronCubes').'<br>';
                            // "You cannot currently build over an opponent's Iron Works:
                            // the cube scarcity condition is not satisfied."
                    }
                    break;
                case 3:
                    $errorlist .= transtext('memOvrPort').'<br>';
                        // "You can never build over an opponent's Port."
                    break;
                default:
                    $errorlist .= transtext('memOvrShipyard').'<br>';
                        // "You can never build over an opponent's Shipyard."
            }
        }
        if ( !$doublebuild and
             $GAME['Cards'][$GAME['PlayerToMove']][$card] <= $GAME['TopLocationCard'] and
             $carddetail != $GAME['spacetowns'][$wheretobuild]
             ) {
            $errorlist .= transtext('memLocnCardBad').'<br>';
                // "The space where you attempted to build is not in
                // the location named on the card that you tried to use."
        }
        if ( $doublebuild and
             !$GAME['RailPhase'] and
             $GAME['Round'] == 1
             ) {
            $errorlist .= transtext('memFirstTurnDbl').'<br>';
                // "You cannot use two cards to build on the first turn of the game."
        }
        if ( $doublebuild and
             $GAME['HandSize'][$GAME['PlayerToMove']] % 2 == 1
             ) {
            $errorlist .= transtext('mem2ndActionDbl').'<br>';
                // "You cannot use two cards to build for your second action of the turn."
        }
        if ( !$doublebuild and
             $GAME['Cards'][$GAME['PlayerToMove']][$card] > $GAME['TopLocationCard'] and
             $carddetail != $whattobuild
             ) {
            $errorlist .= transtext('memIndyCardType').'<br>';
                // "The tile that you attempted to build does not match
                // the industry type of the card that you tried to use."
        }
        if ( !$GAME['RailPhase'] and
             $whattobuild == 4 and
             $TheTechLevel == 2
             ) {
            $errorlist .= transtext('memTech2Shipyard').'<br>';
                // "You cannot build a Tech Level 2 Shipyard during the Canal Phase."
        }
        if ( $whattobuild == 4 and
             $TheTechLevel == 0
             ) {
            $errorlist .= transtext('memTech0Shipyard').'<br>';
                // "You cannot build a Tech Level 0 Shipyard at any time.
                // These tiles must be developed away before you can build Shipyards."
        }
        if ( $GAME['RailPhase'] and
             $TheTechLevel == 1
             ) {
            $errorlist .= transtext('memTech1Rails').'<br>';
                // You cannot build a Tech Level 1 industry tile during the Rail Phase.
        }
        if ( $GAME['Money'][$GAME['PlayerToMove']] < $GAME['TileCosts'][$whattobuild][$TheTechLevel-1] ) {
            $errorlist .= 'You cannot afford to build that industry tile.<br>';
        } else if ( ( $GAME['TileRequireCoal'][$whattobuild][$TheTechLevel-1] and
                      $coalsource == 50 and
                      $GAME['Money'][$GAME['PlayerToMove']] < $GAME['TileCosts'][$whattobuild][$TheTechLevel-1] +
                                                              $GAME['cubeprice'][$GAME['CoalDemand']]
                      ) or
                    ( $GAME['TileRequireIron'][$whattobuild][$TheTechLevel-1] and
                      $ironsource == 50 and
                      $GAME['Money'][$GAME['PlayerToMove']] < $GAME['TileCosts'][$whattobuild][$TheTechLevel-1] +
                                                              $GAME['cubeprice'][$GAME['IronDemand']]
                      ) or
                    ( $GAME['TileRequireCoal'][$whattobuild][$TheTechLevel-1] and
                      $GAME['TileRequireIron'][$whattobuild][$TheTechLevel-1] and
                      $coalsource == 50 and
                      $ironsource == 50 and
                      $GAME['Money'][$GAME['PlayerToMove']] < $GAME['TileCosts'][$whattobuild][$TheTechLevel-1] +
                                                              $GAME['cubeprice'][$GAME['CoalDemand']] +
                                                              $GAME['cubeprice'][$GAME['IronDemand']]
                      )
                    ) {
            $errorlist .= transtext('memTilePoorCubes').'<br>';
                // Although you can afford to build the chosen industry tile,
                // you cannot afford to buy from the Demand Track(s) the cube(s) needed to build it.
        }
        if ( $GAME['TileRequireCoal'][$whattobuild][$TheTechLevel-1] ) {
            switch ( $coalsource ) {
                case 90: $errorlist .= transtext('memCoalNotValid').'<br>'; break;
                    // "The selected source of coal is not valid."
                case 91: $errorlist .= transtext('memCoalOnBoard').'<br>'; break;
                    // "You cannot buy coal from the Demand Track,
                    // as there is coal available on the board for you to use."
                case 92: $errorlist .= transtext('memCoalNearer').'<br>'; break;
                case 93: $errorlist .= 'The selected source of coal is not connected to the location where you are trying to build.<br>'; break;
                case 95: $errorlist .= transtext('memCoalNoPorts').'<br>'; break;
                    // "You cannot buy coal from the Demand Track,
                    // as the location where you want to build is not connected to a Port."
            }
        }
        if ( $GAME['TileRequireIron'][$whattobuild][$TheTechLevel-1] ) {
            switch ( $ironsource ) {
                case 90: $errorlist .= transtext('memIronNotValid').'<br>'; break;
                    // "The selected source of iron is not valid."
                case 91: $errorlist .= transtext('memIronOnBoard').'<br>'; break;
                    // "You cannot buy iron from the Demand Track,
                    // as there is iron available on the board for you to use."
            }
        }
    }
    if ( $errorlist == '' ) {
        if ( $GAME['SpaceStatus'][$wheretobuild] != 9 ) {
            destroytile($wheretobuild,0,1);
        }
        $GAME['HasBuilt'][$GAME['PlayerToMove']] = 1;
        $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['spacetowns'][$wheretobuild]] = 1;
        $GAME['SpaceStatus'][$wheretobuild] = $GAME['PlayerToMove'];
        $GAME['SpaceTile'][$wheretobuild] = $whattobuild;
        $GAME['TechLevels'][$wheretobuild] = $TheTechLevel;
        $GAME['Money'][$GAME['PlayerToMove']]       -= $GAME['TileCosts'][$whattobuild][$TheTechLevel-1];
        $GAME['AmountSpent'][$GAME['PlayerToMove']] += $GAME['TileCosts'][$whattobuild][$TheTechLevel-1];
        $GAME['RemainingTiles'][$whattobuild][$GAME['PlayerToMove']]--;
        if ( $GAME['TileRequireCoal'][$whattobuild][$TheTechLevel-1] ) {
            if ( $coalsource == 50 ) {
                $GAME['Money'][$GAME['PlayerToMove']]       -= $GAME['cubeprice'][$GAME['CoalDemand']];
                $GAME['AmountSpent'][$GAME['PlayerToMove']] += $GAME['cubeprice'][$GAME['CoalDemand']];
                if ( ( $GAME['CurrentPlayers'] == 2 and
                       $GAME['CoalDemand'] < 6
                       ) or
                     ( $GAME['CurrentPlayers'] > 2 and
                       $GAME['CoalDemand'] < 8
                       )
                     ) {
                    $GAME['CoalDemand']++;
                }
            } else {
                $GAME['CoalInLancs']--;
                $GAME['SpaceCubes'][$coalsource] = $GAME['SpaceCubes'][$coalsource] - 1;
                    // This is written in this way because SpaceCubes is a string consisting of digits
                    // (it is not legal to use increment/decrement operators on string offsets).
                if ( !$GAME['SpaceCubes'][$coalsource] ) {
                    fliptile($coalsource);
                }
            }
        } else {
            $altcoalsource = 98;
        }
        if ( $GAME['TileRequireIron'][$whattobuild][$TheTechLevel-1] ) {
            if ( $ironsource == 50 ) {
                $GAME['Money'][$GAME['PlayerToMove']]       -= $GAME['cubeprice'][$GAME['IronDemand']];
                $GAME['AmountSpent'][$GAME['PlayerToMove']] += $GAME['cubeprice'][$GAME['IronDemand']];
                if ( ( $GAME['CurrentPlayers'] == 2 and
                       $GAME['IronDemand'] < 6
                       ) or
                     ( $GAME['CurrentPlayers'] > 2 and
                       $GAME['IronDemand'] < 8
                       )
                     ) {
                    $GAME['IronDemand']++;
                }
            } else {
                $GAME['IronInLancs']--;
                $GAME['SpaceCubes'][$ironsource] = $GAME['SpaceCubes'][$ironsource] - 1;
                    // This is written in this way because SpaceCubes is a string consisting of digits
                    // (it is not legal to use increment/decrement operators on string offsets).
                if ( !$GAME['SpaceCubes'][$ironsource] ) {
                    fliptile($ironsource);
                }
            }
        } else {
            $altironsource = 98;
        }
        switch ( $whattobuild ) {
            case 0:
                $GAME['SpaceCubes'][$wheretobuild] = 1;
                break;
            case 1:
                $GAME['SpaceCubes'][$wheretobuild] = $GAME['TileInitialCubes'][0][$TheTechLevel-1];
                $GAME['CoalInLancs'] += $GAME['SpaceCubes'][$wheretobuild];
                if ( $GAME['HasPort'][$GAME['spacetowns'][$wheretobuild]] ) {
                    if ( $GAME['CoalDemand'] >= $GAME['SpaceCubes'][$wheretobuild] ) {
                        $GAME['CoalInLancs'] -= $GAME['SpaceCubes'][$wheretobuild];
                        $CoalIncome = 0;
                        for ($i=0;$i<$GAME['SpaceCubes'][$wheretobuild];$i++) {
                            $GAME['CoalDemand']--;
                            $CoalIncome += $GAME['cubeprice'][$GAME['CoalDemand']];
                        }
                        $GAME['SpaceCubes'][$wheretobuild] = 0;
                        fliptile($wheretobuild);
                    } else {
                        $GAME['CoalInLancs'] -= $GAME['CoalDemand'];
                        $GAME['SpaceCubes'][$wheretobuild] = $GAME['TileInitialCubes'][0][$TheTechLevel-1] -
                                                             $GAME['CoalDemand'];
                        $oldcoaldemand = $GAME['CoalDemand'];
                        $CoalIncome = 0;
                        for ($i=0;$i<$oldcoaldemand;$i++) {
                            $GAME['CoalDemand']--;
                            $CoalIncome += $GAME['cubeprice'][$GAME['CoalDemand']];
                        }
                    }
                    if ( $CoalIncome ) {
                        $GAME['Money'][$GAME['PlayerToMove']] += $CoalIncome;
                        $altcoalsource = 97;
                    }
                }
                break;
            case 2:
                $GAME['SpaceCubes'][$wheretobuild] = $GAME['TileInitialCubes'][1][$TheTechLevel-1];
                $GAME['IronInLancs'] += $GAME['SpaceCubes'][$wheretobuild];
                if ( $GAME['IronDemand'] >= $GAME['SpaceCubes'][$wheretobuild] ) {
                    $GAME['IronInLancs'] -= $GAME['SpaceCubes'][$wheretobuild];
                    $IronIncome = 0;
                    for ($i=0;$i<$GAME['SpaceCubes'][$wheretobuild];$i++) {
                        $GAME['IronDemand']--;
                        $IronIncome += $GAME['cubeprice'][$GAME['IronDemand']];
                    }
                    $GAME['SpaceCubes'][$wheretobuild] = 0;
                    fliptile($wheretobuild);
                } else {
                    $GAME['IronInLancs'] -= $GAME['IronDemand'];
                    $GAME['SpaceCubes'][$wheretobuild] = $GAME['TileInitialCubes'][1][$TheTechLevel-1] -
                                                         $GAME['IronDemand'];
                    $oldirondemand = $GAME['IronDemand'];
                    $IronIncome = 0;
                    for ($i=0;$i<$oldirondemand;$i++) {
                        $GAME['IronDemand']--;
                        $IronIncome += $GAME['cubeprice'][$GAME['IronDemand']];
                    }
                }
                if ( $IronIncome ) {
                    $GAME['Money'][$GAME['PlayerToMove']] += $IronIncome;
                    $altironsource = 97;
                }
                break;
            case 3:
                for ($i=0;$i<$GAME['NumTowns'];$i++) {
                    if ( $GAME['CoalNet'][$i] == $GAME['CoalNet'][$GAME['spacetowns'][$wheretobuild]] ) {
                        $GAME['HasPort'][$i] = 1;
                    }
                }
                $GAME['SpaceCubes'][$wheretobuild] = 1;
                break;
            case 4:
                fliptile($wheretobuild);
        }
        if ( $SystemActing ) {
            $AdminTakingMove = 0;
        } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
            $AdminTakingMove = 0;
        } else {
            $AdminTakingMove = 1;
        }
        if ( $doublebuild ) {
            $dbcard = letter_end_number($thecardB);
            $actionnuma = '1H';
            $actionnumb = 'C';
        } else {
            $dbcard = '';
            $actionnuma = '1G';
            $actionnumb = 'B';
        }
        if ( $AdminTakingMove ) {
            $altgoodoutput = $actionnuma.
                             callmovetimediff().
                             letter_end_number($_SESSION['MyUserID']).
                             letter_end_number($_SESSION['MyGenderCode']).
                             letter_end_number($thecardA).
                             $dbcard.
                             letter_end_number($whattobuild).
                             letter_end_number($wheretobuild).
                             letter_end_number($altcoalsource).
                             letter_end_number($altironsource);
            $altgoodoutputName = '|'.$_SESSION['MyUserName'];
        } else {
            $altgoodoutput = $actionnumb.
                             callmovetimediff().
                             letter_end_number($thecardA).
                             $dbcard.
                             letter_end_number($whattobuild).
                             letter_end_number($wheretobuild).
                             letter_end_number($altcoalsource).
                             letter_end_number($altironsource);
            $altgoodoutputName = '';
        }
    } else {
        $altgoodoutput = '';
        $altgoodoutputName = '';
    }
    return array($errorlist,'',$altgoodoutput,$altgoodoutputName);
}

?>