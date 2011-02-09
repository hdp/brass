<?php

function CheckNoMoves ($card) {
    global $GAME;
    if ( $GAME['DebtMode'] ) {
        return false;
    }
    if ( $GAME['RemainingTiles'][5][$GAME['PlayerToMove']] and
         $GAME['Money'][$GAME['PlayerToMove']] > 4
         ) {
        for ($i=0;$i<$GAME['NumRailLinks'];$i++) {
            if ( ( $GAME['HasPort'][$GAME['RailEnds'][$i]] or
                   $GAME['HasPort'][$GAME['RailStarts'][$i]]
                   ) and
                 $GAME['Money'][$GAME['PlayerToMove']] > 4 + $GAME['cubeprice'][$GAME['CoalDemand']] and
                 $GAME['LinkStatus'][$i] == 9 and
                 ( $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$i]] or
                   $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$i]] or
                   $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$i]] or
                   $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$i]]
                   ) and
                 ( $GAME['RailAlwaysExists'][$i] or
                   ( $GAME['ModularBoardParts'] & $GAME['RailExistenceArray'][$i] )
                   )
                 ) {
                return false;
            }
            for ($j=0;$j<$GAME['NumIndustrySpaces'];$j++) {
                if ( $GAME['SpaceTile'][$j] == 1 and
                     $GAME['SpaceCubes'][$j] and
                     ( $GAME['CoalNet'][$GAME['spacetowns'][$j]] == $GAME['CoalNet'][$GAME['RailStarts'][$i]] or
                       $GAME['CoalNet'][$GAME['spacetowns'][$j]] == $GAME['CoalNet'][$GAME['RailEnds'][$i]]
                       ) and
                     ( $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$i]] or
                       $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$i]] or
                       $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$i]] or
                       $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$i]]
                       ) and
                     $GAME['LinkStatus'][$i] == 9 and
                     ( $GAME['RailAlwaysExists'][$i] or
                       ( $GAME['ModularBoardParts'] & $GAME['RailExistenceArray'][$i] )
                       )
                     ) {
                    return false;
                }
            }
        }
    }
    if ( $GAME['RemainingTiles'][0][$GAME['PlayerToMove']] or
         $GAME['RemainingTiles'][1][$GAME['PlayerToMove']] or
         $GAME['RemainingTiles'][2][$GAME['PlayerToMove']] or
         $GAME['RemainingTiles'][3][$GAME['PlayerToMove']] or
         $GAME['RemainingTiles'][4][$GAME['PlayerToMove']]
         ) {
        $CanGetIronFree = false;
        for ($i=0;$i<$GAME['NumIndustrySpaces'];$i++) {
            if ( $GAME['SpaceTile'][$i] == 2 and $GAME['SpaceCubes'][$i] ) {
                $CanGetIronFree = true;
            }
        }
        if ( $CanGetIronFree or
             $GAME['Money'][$GAME['PlayerToMove']] >= $GAME['cubeprice'][$GAME['IronDemand']]
             ) {
            return false;
        }
    }
    $MillsArray = array();
    $PortsArray = array();
    for ($i=0;$i<$GAME['NumIndustrySpaces'];$i++) {
        if ( $GAME['SpaceStatus'][$i] == $GAME['PlayerToMove'] and
             !$GAME['SpaceTile'][$i] and
             $GAME['SpaceCubes'][$i]
             ) {
            $MillsArray[] = $i;
        }
        if ( $GAME['SpaceStatus'][$i] != 9 and
             $GAME['SpaceTile'][$i] == 3 and
             $GAME['SpaceCubes'][$i]
             ) {
            $PortsArray[] = $i;
        }
    }
    for ($i=0;$i<count($MillsArray);$i++) {
        if ( $GAME['HasPort'][$GAME['spacetowns'][$MillsArray[$i]]] and
             $GAME['CottonDemand'] < 8
             ) {
            return false;
        }
        for ($j=0;$j<count($PortsArray);$j++) {
            if ( $GAME['CoalNet'][$GAME['spacetowns'][$MillsArray[$i]]] == $GAME['CoalNet'][$GAME['spacetowns'][$PortsArray[$j]]] ) {
                return false;
            }
        }
    }
    if ( $card != 99 ) { $carddetail = $GAME['carddetailarray'][$card]; }
    else               { $carddetail = 0; }
    for ($i=0;$i<5;$i++) {
        if ( $card <= $GAME['TopLocationCard'] or
             $card == 99 or
             $carddetail == $i
             ) {
            $TheTechLevel = $GAME['TechLevelArray'][$i][$GAME['RemainingTiles'][$i][$GAME['PlayerToMove']]];
            if ( $TheTechLevel != 9 and $TheTechLevel > 1 ) {
                for ($j=0;$j<$GAME['NumIndustrySpaces'];$j++) {
                    $canbuildhereusingVCs = false;
                    for ($k=0;$k<count($GAME['GeneralisedVCs']);$k++) {
                        if ( ( $GAME['spacetowns'][$j] == $GAME['GeneralisedVCs'][$k][0] and
                               ( $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$k][1]] or
                                 $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$k][1]]
                                 )
                               ) or
                             ( ( ( ( $GAME['SpecialRules'] & 1 ) and
                                   $GAME['GeneralisedVCs'][$k][2]
                                   ) or
                                 $GAME['GeneralisedVCs'][$k][2] == 2
                               ) and
                               $GAME['spacetowns'][$j] == $GAME['GeneralisedVCs'][$k][1] and
                               ( $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$k][0]] or
                                 $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['GeneralisedVCs'][$k][0]]
                                 )
                               )
                             ) {
                            $canbuildhereusingVCs = true;
                        }
                    }
                    if ( ( $i == $GAME['tileindustries'][$j] or                                            // the man from the
                           ( $GAME['tileindustries'][$j] == 5 and                                          // dochy of cornwall
                             ( !$i or                                                                      // he say
                               $i == 1                                                                     //
                               )                                                                           //
                             ) or                                                                          //
                           ( $GAME['tileindustries'][$j] == 6 and                                          //
                             ( !$i or                                                                      //
                               $i == 3                                                                     //
                               )                                                                           //
                             ) or                                                                          //
                           ( $GAME['tileindustries'][$j] == 7 and                                          //
                             ( $i == 2 or                                                                  //
                               $i == 3                                                                     //
                               )                                                                           //
                             )                                                                             //
                           ) and                                                                           //
                         ( ( $card <= $GAME['TopLocationCard'] and                                         //
                             $carddetail == $GAME['spacetowns'][$j]                                        //
                             ) or                                                                          //
                           ( $card > $GAME['TopLocationCard'] and                                          //
                             ( $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['spacetowns'][$j]] or //
                               $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['spacetowns'][$j]] or  //
                               $canbuildhereusingVCs                                                       //
                               )                                                                           //
                             ) or                                                                          //
                           $card == 99 or                                                                  //
                           !$GAME['HasBuilt'][$GAME['PlayerToMove']]                                       //
                           ) and                                                                           //
                         ( $GAME['SpaceStatus'][$j] == 9 or                                                //
                           ( $GAME['SpaceTile'][$j] == $i and                                              //
                             $GAME['TechLevels'][$j] < $TheTechLevel and                                   //
                             ( $GAME['SpaceStatus'][$j] == 8 or                                            //
                               $GAME['SpaceStatus'][$j] == $GAME['PlayerToMove'] or                        //
                               ( $i == 1 and                                                               //
                                 !$GAME['CoalInLancs'] and                                                 //
                                 ( ( $GAME['CurrentPlayers'] == 2 and                                      //
                                     $GAME['CoalDemand'] == 6                                              //
                                     ) or                                                                  //
                                   ( $GAME['CurrentPlayers'] > 2 and                                       //
                                     $GAME['CoalDemand'] == 8                                              //
                                     )                                                                     //
                                   )                                                                       //
                                 ) or                                                                      //
                               ( $i == 2 and                                                               //
                                 !$GAME['IronInLancs'] and                                                 //
                                 ( ( $GAME['CurrentPlayers'] == 2 and                                      //
                                     $GAME['IronDemand'] == 6                                              //
                                     ) or                                                                  //
                                   ( $GAME['CurrentPlayers'] > 2 and                                       //
                                     $GAME['IronDemand'] == 8                                              //
                                     )                                                                     //
                                   )                                                                       //
                                 )                                                                         //
                               )                                                                           //
                             )                                                                             //
                           ) and                                                                           //
                         ( $GAME['SpaceAlwaysExists'][$j] or                                               //
                           ( $GAME['ModularBoardParts'] & $GAME['SpaceExistenceArray'][$j] )               //
                           )                                                                               //
                         ) {                                                                               // GET ORFF MY LAND
                        if ( $GAME['TileRequireIron'][$i][$TheTechLevel] and !$GAME['IronInLancs'] ) {
                            $MustPayForIron = 1;
                        } else {
                            $MustPayForIron = 0;
                        }
                        if ( $GAME['TileRequireCoal'][$i][$TheTechLevel] ) {
                            $MustPayForCoal = 1;
                        } else {
                            $MustPayForCoal = 0;
                        }
                        if ( $GAME['HasPort'][$GAME['spacetowns'][$j]] ) {
                            $CanGetCoal = true;
                        } else {
                            $CanGetCoal = false;
                        }
                        for ($k=0;$k<$GAME['NumIndustrySpaces'];$k++) {
                            if ( $GAME['CoalNet'][$GAME['spacetowns'][$k]] == $GAME['CoalNet'][$GAME['spacetowns'][$j]] and
                                 $GAME['SpaceCubes'][$k] and $GAME['SpaceTile'][$k] == 1 and
                                 $GAME['SpaceStatus'][$k] != 9
                                 ) {
                                $CanGetCoal = true;
                                $MustPayForCoal = 0;
                            }
                        }
                        if ( !$GAME['TileRequireCoal'][$i][$TheTechLevel] or $CanGetCoal ) {
                            if ( $GAME['Money'][$GAME['PlayerToMove']] >=
                                     $TileCosts[$i][$TheTechLevel] +
                                     $MustPayForCoal * $GAME['cubeprice'][$GAME['CoalDemand']] +
                                     $MustPayForIron * $GAME['cubeprice'][$GAME['IronDemand']]
                                     ) {
                                return false;
                            }
                        }
                    }
                }
            }
        }
    }
    return true;
}

function CheckNoMovesShell() {
    global $GAME;
    while ( CheckNoMoves(99) ) {
        $GAME['AltGameTicker'] .= '9H'.
                                  letter_end_number($GAME['carddetailarrayb'][$GAME['Cards'][$GAME['PlayerToMove']][0]]).
                                  letter_end_number($GAME['carddetailarrayb'][$GAME['Cards'][$GAME['PlayerToMove']][1]]);
        $GAME['DiscardPile'][] = array_pop($GAME['Cards'][$GAME['PlayerToMove']]);
        $GAME['DiscardPile'][] = array_pop($GAME['Cards'][$GAME['PlayerToMove']]);
        $GAME['HandSize'][$GAME['PlayerToMove']] = 0;
        $GAME['AltGameTicker'] .= 'A';
        for ($i=0;$i<MAX_PLAYERS;$i++) {
            if ( $GAME['TurnOrder'][$i] == $GAME['PlayerToMove'] ) {
                $WhereInTurnOrder = $i;
            }
        }
        $EndRound = true;
        for ($i=MAX_PLAYERS-1;$i>$WhereInTurnOrder;$i--) {
            if ( $GAME['PlayerExists'][$GAME['TurnOrder'][$i]] ) {
                $GAME['PlayerToMove'] = $GAME['TurnOrder'][$i];
                $EndRound = false;
            }
        }
        if ( $EndRound ) {
            require_once(HIDDEN_FILES_PATH.'scoringresource.php');
            endgamescoring();
            break;
        }
    }
}

?>