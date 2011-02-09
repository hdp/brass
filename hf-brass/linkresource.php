<?php

function buildlink($linktobuild,$coalsource,$buildnum,$thecard) {
    global $GAME,$SystemActing;
    $errorlist = '';
    if ( $GAME['LinkStatus'][$linktobuild] != 9 ) {
        if ( $GAME['RailPhase'] ) {
            $errorlist .= transtext('memRailBuilt').'<br>';
        } else {
            $errorlist .= transtext('memCanalBuilt').'<br>';
        }
        // "The chosen rail/canal link has already been built."
    }
    if ( $GAME['RailPhase'] ) {
        if ( !$GAME['RailAlwaysExists'][$linktobuild] and
             ( $GAME['ModularBoardParts'] & $GAME['RailExistenceArray'][$linktobuild] ) != $GAME['RailExistenceArray'][$linktobuild]
             ) {
            $errorlist .= transtext('memRailNotThere').'<br>';
                // "That rail link does not exist (or it no longer exists) in this game."
        }
        if ( !$GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$linktobuild]] and
             !$GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$linktobuild]] and
             !$GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$linktobuild]] and
             !$GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$linktobuild]]
             ) {
            $errorlist .= transtext('memRailNotConn').'<br>';
                // "You cannot build that rail link, as you have neither connected to nor built in either location."
        }
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
    } else {
        if ( !$GAME['CanalAlwaysExists'][$linktobuild] and
             ( ( $GAME['ModularBoardParts'] & $GAME['CanalExistenceArray'][$linktobuild] ) != $GAME['CanalExistenceArray'][$linktobuild] )
             ) {
            $errorlist .= transtext('memCanalNotThere').'<br>';
                // That canal link does not exist (or it no longer exists) in this game.
        }
        if ( !$GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['CanalStarts'][$linktobuild]] and
             !$GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['CanalEnds'][$linktobuild]] and
             !$GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['CanalStarts'][$linktobuild]] and
             !$GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['CanalEnds'][$linktobuild]]
             ) {
            $errorlist .= transtext('memCanalNotConn').'<br>';
                // "You cannot build that canal link, as you have neither connected to nor built in either location."
        }
    }
    $MoneyWarning = true;
    if ( !$GAME['RailPhase'] and
         $GAME['Money'][$GAME['PlayerToMove']] < 3
         ) {
        $errorlist .= transtext('memCanalPoor').'<br>';
            // "You cannot afford to build a canal link."
    }
    if ( $GAME['RailPhase'] and
         $GAME['Money'][$GAME['PlayerToMove']] < 5
         ) {
        $MoneyWarning = false;
        $errorlist .= transtext('memRailPoor').'<br>';
            // "You cannot afford to build a rail link."
    }
    if ( $GAME['RailPhase'] and
         $MoneyWarning and
         $coalsource == 50 and
         $GAME['Money'][$GAME['PlayerToMove']] < 5 * $buildnum +
                                                 $GAME['cubeprice'][$GAME['CoalDemand']]
         ) {
        $errorlist .= transtext('memRailPoorCoal').'<br>';
            // "Although you can afford to build a rail link, you cannot afford
            // to buy from the Demand Track the coal cube needed to build it."
    }
    if ( !$GAME['RemainingTiles'][5][$GAME['PlayerToMove']] ) {
        if ( $GAME['RailPhase'] ) {
            $errorlist .= transtext('memOutOfRails').'<br>';
        } else {
            $errorlist .= transtext('memOutOfCanals').'<br>';
        }
        // "You do not have any rail/canal markers left."
    }
    if ( $coalsource == 50 ) { $altcoalsource = 99;          }
    else                     { $altcoalsource = $coalsource; }
    if ( $errorlist == '' ) {
        if ( $SystemActing ) {
            $AdminTakingMove = 0;
        } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
            $AdminTakingMove = 0;
        } else {
            $AdminTakingMove = 1;
        }
        $GAME['RemainingTiles'][5][$GAME['PlayerToMove']]--;
        $GAME['LinkStatus'][$linktobuild] = $GAME['PlayerToMove'];
        $GAME['Money'][$GAME['PlayerToMove']]       -= ( 3 + 2 * $GAME['RailPhase'] ) * $buildnum;
        $GAME['AmountSpent'][$GAME['PlayerToMove']] += ( 3 + 2 * $GAME['RailPhase'] ) * $buildnum;
        if ( $GAME['RailPhase'] ) {
            if ( $GAME['CoalNet'][$GAME['RailStarts'][$linktobuild]] <
                     $GAME['CoalNet'][$GAME['RailEnds'][$linktobuild]] ) {
                $LowCoalNet  = $GAME['CoalNet'][$GAME['RailStarts'][$linktobuild]];
                $HighCoalNet = $GAME['CoalNet'][$GAME['RailEnds'][$linktobuild]];  
            } else {
                $LowCoalNet  = $GAME['CoalNet'][$GAME['RailEnds'][$linktobuild]];
                $HighCoalNet = $GAME['CoalNet'][$GAME['RailStarts'][$linktobuild]];
            }
            if ( $GAME['HasPort'][$GAME['RailStarts'][$linktobuild]] or
                 $GAME['HasPort'][$GAME['RailEnds'][$linktobuild]]
                 ) {
                $AssignHasPort = 1;
            } else {
                $AssignHasPort = 0;
            }
            for ($i=0;$i<$GAME['NumTowns'];$i++) {
                if ( $GAME['CoalNet'][$i] == $HighCoalNet ) {
                    $GAME['CoalNet'][$i] = $LowCoalNet;
                }
                if ( $GAME['CoalNet'][$i] == $LowCoalNet ) {
                    $GAME['HasPort'][$i] = $AssignHasPort;
                }
            }
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
            $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$linktobuild]] = 1;
            $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$linktobuild]]   = 1;
            if ( $buildnum == 2 ) {
                if ( $AdminTakingMove ) {
                    $altgoodoutput = '2D'.
                                     callmovetimediff().
                                     letter_end_number($_SESSION['MyUserID']).
                                     letter_end_number($_SESSION['MyGenderCode']).
                                     letter_end_number($linktobuild).
                                     letter_end_number($altcoalsource);
                    $altgoodoutputName = '|'.$_SESSION['MyUserName'];
                } else {
                    $altgoodoutput = 'I'.
                                     callmovetimediff().
                                     letter_end_number($linktobuild).
                                     letter_end_number($altcoalsource);
                    $altgoodoutputName = '';
                }
                $GAME['SecondRailMode'] = 0;
            } else {
                if ( $AdminTakingMove ) {
                    $altgoodoutput = '1I'.
                                     callmovetimediff().
                                     letter_end_number($_SESSION['MyUserID']).
                                     letter_end_number($_SESSION['MyGenderCode']).
                                     letter_end_number($thecard).
                                     letter_end_number($linktobuild).
                                     letter_end_number($altcoalsource);
                    $altgoodoutputName = '|'.$_SESSION['MyUserName'];
                } else {
                    $altgoodoutput = 'D'.
                                     callmovetimediff().
                                     letter_end_number($thecard).
                                     letter_end_number($linktobuild).
                                     letter_end_number($altcoalsource);
                    $altgoodoutputName = '';
                }
                if ( !$GAME['RemainingTiles'][5][$GAME['PlayerToMove']] ) {
                    $altgoodoutput .= '9IB';
                } else if ( $GAME['Money'][$GAME['PlayerToMove']] < 10 ) {
                    $altgoodoutput .= '9IC';
                } else {
                    if ( $GAME['CoalInLancs'] or
                         $GAME['Money'][$GAME['PlayerToMove']] > 9 + $GAME['cubeprice'][$GAME['CoalDemand']]
                         ) {
                        for ($i=0;$i<$GAME['NumRailLinks'];$i++) {
                            if ( ( $GAME['HasPort'][$GAME['RailEnds'][$i]] or
                                   $GAME['HasPort'][$GAME['RailStarts'][$i]]
                                   ) and
                                 $GAME['Money'][$GAME['PlayerToMove']] > 9 + $GAME['cubeprice'][$GAME['CoalDemand']] and
                                 $GAME['LinkStatus'][$i] == 9 and
                                 ( $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$i]] or
                                   $GAME['HasBuiltInTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$i]] or
                                   $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailStarts'][$i]] or
                                   $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['RailEnds'][$i]]
                                   )
                                 ) {
                                $GAME['SecondRailMode'] = 1;
                                break;
                            }
                            for ($j=0;$j<$GAME['NumIndustrySpaces'];$j++) {
                                if ( $GAME['SpaceStatus'][$j] != 9 and
                                     $GAME['SpaceTile'][$j] == 1 and
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
                                    $GAME['SecondRailMode'] = 1;
                                    break 2;
                                }
                            }
                        }
                    }
                    if ( !$GAME['SecondRailMode'] ) {
                        $altgoodoutput .= '9IA';
                    }
                }
            }
        } else {
            if ( $GAME['CoalNet'][$GAME['CanalStarts'][$linktobuild]] <
                     $GAME['CoalNet'][$GAME['CanalEnds'][$linktobuild]]
                 ) {
                $LowCoalNet  = $GAME['CoalNet'][$GAME['CanalStarts'][$linktobuild]];
                $HighCoalNet = $GAME['CoalNet'][$GAME['CanalEnds'][$linktobuild]];
            } else {
                $LowCoalNet  = $GAME['CoalNet'][$GAME['CanalEnds'][$linktobuild]];
                $HighCoalNet = $GAME['CoalNet'][$GAME['CanalStarts'][$linktobuild]];
            }
            if ( $GAME['HasPort'][$GAME['CanalStarts'][$linktobuild]] or
                 $GAME['HasPort'][$GAME['CanalEnds'][$linktobuild]]
                 ) {
                $AssignHasPort = 1;
            } else {
                $AssignHasPort = 0;
            }
            for ($i=0;$i<$GAME['NumTowns'];$i++) {
                if ( $GAME['CoalNet'][$i] == $HighCoalNet ) {
                    $GAME['CoalNet'][$i] = $LowCoalNet;
                }
                if ( $GAME['CoalNet'][$i] == $LowCoalNet ) {
                    $GAME['HasPort'][$i] = $AssignHasPort;
                }
            }
            $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['CanalStarts'][$linktobuild]] = 1;
            $GAME['HasLinkedToTown'][$GAME['PlayerToMove']][$GAME['CanalEnds'][$linktobuild]]   = 1;
            if ( $AdminTakingMove ) {
                $altgoodoutput = '1I'.
                                 callmovetimediff().
                                 letter_end_number($_SESSION['MyUserID']).
                                 letter_end_number($_SESSION['MyGenderCode']).
                                 letter_end_number($thecard).
                                 letter_end_number($linktobuild);
                $altgoodoutputName = '|'.$_SESSION['MyUserName'];
            } else {
                $altgoodoutput = 'D'.
                                 callmovetimediff().
                                 letter_end_number($thecard).
                                 letter_end_number($linktobuild);
                $altgoodoutputName = '';
            }
        }
    } else {
        $altgoodoutput = '';
        $altgoodoutputName = '';
    }
    return array($errorlist,'',$altgoodoutput,$altgoodoutputName);
}

?>