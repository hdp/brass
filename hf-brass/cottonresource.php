<?php

function sellcotton($milllocation,$portlocation,$continuing,$thecard) {
    global $GAME,$SystemActing;
    $errorlistA = '';
    $errorlistB = '';
    $ExclamationMark = 0;
    if ( $GAME['SpaceTile'][$milllocation] or
         $GAME['SpaceStatus'][$milllocation] == 9
         ) {
        $errorlistA .= transtext('memIsNotCM').'<br>';
            // "The industry space that you specified as the location
            // of the Cotton Mill is not occupied by a Cotton Mill."
    } else if ( $GAME['SpaceStatus'][$milllocation] != $GAME['PlayerToMove'] ) {
        $errorlistA .= transtext('memCMNotYours').'<br>';
    } else if ( !$GAME['SpaceCubes'][$milllocation] ) {
        $errorlistA .= transtext('memCMFlipped').'<br>';
            // "The Cotton Mill that you specified is already flipped."
    }
    if ( $errorlistA == '' ) {
        if ( $portlocation < $GAME['NumIndustrySpaces'] ) {
            if ( $GAME['SpaceTile'][$portlocation] != 3 ) {
                $errorlistB .= transtext('memIsNotPort').'<br>';
                    // "The industry space that you specified as the location
                    // of the Port is not occupied by a Port."
            } else if ( $GAME['CoalNet'][$GAME['spacetowns'][$milllocation]] !=
                            $GAME['CoalNet'][$GAME['spacetowns'][$portlocation]] ) {
                $errorlistB .= transtext('memPortNotConn').'<br>';
                    // "The Port location that you specified is not connected to the Cotton Mill that you specified."
            } else if ( !$GAME['SpaceCubes'][$portlocation] ) {
                $errorlistB .= transtext('memPortFlipped').'<br>';
                    // "The Port in the location that you specified is already flipped."
            }
        } else {
            if ( !$GAME['HasPort'][$GAME['spacetowns'][$milllocation]] ) {
                $errorlistB .= transtext('memCMNoPorts').'<br>';
                    // "The specified Cotton Mill cannot sell to the Distant Market, as it is not connected to any Ports."
            } else if ( $GAME['CottonDemand'] == 8 ) {
                $errorlistB .= transtext('memNoMoreDemand').'<br>';
            }
        }
    }
    $errorlist = $errorlistA.$errorlistB;
    if ( $errorlist == '' ) {
        if ( $SystemActing ) {
            $AdminTakingMove = 0;
        } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
            $AdminTakingMove = 0;
        } else {
            $AdminTakingMove = 1;
        }
        $append_action_end_note = true;
        if ( $portlocation < $GAME['NumIndustrySpaces'] ) {
            fliptile($portlocation);
            fliptile($milllocation);
            if ( $continuing ) {
                if ( $AdminTakingMove ) {
                    $altgoodoutput = '2D'.
                                     callmovetimediff().
                                     letter_end_number($_SESSION['MyUserID']).
                                     letter_end_number($_SESSION['MyGenderCode']).
                                     letter_end_number($milllocation).
                                     letter_end_number($portlocation);
                    $altgoodoutputName = '|'.$_SESSION['MyUserName'];
                } else {
                    $altgoodoutput = 'I'.
                                     callmovetimediff().
                                     letter_end_number($milllocation).
                                     letter_end_number($portlocation);
                    $altgoodoutputName = '';
                }
            } else {
                if ( $AdminTakingMove ) {
                    $altgoodoutput = '2B'.
                                     callmovetimediff().
                                     letter_end_number($_SESSION['MyUserID']).
                                     letter_end_number($_SESSION['MyGenderCode']).
                                     letter_end_number($thecard).
                                     letter_end_number($milllocation).
                                     letter_end_number($portlocation);
                    $altgoodoutputName = '|'.$_SESSION['MyUserName'];
                } else {
                    $altgoodoutput = 'G'.
                                     callmovetimediff().
                                     letter_end_number($thecard).
                                     letter_end_number($milllocation).
                                     letter_end_number($portlocation);
                    $altgoodoutputName = '';
                }
            }
            $CheckCtu = true;
        } else {
            drawDMtile();
            if ( $continuing ) {
                if ( $AdminTakingMove ) {
                    $altgoodoutput = '2D'.
                                     callmovetimediff().
                                     letter_end_number($_SESSION['MyUserID']).
                                     letter_end_number($_SESSION['MyGenderCode']).
                                     letter_end_number($milllocation).
                                     '9J';
                    $altgoodoutputName = '|'.$_SESSION['MyUserName'];
                } else {
                    $altgoodoutput = 'I'.
                                     callmovetimediff().
                                     letter_end_number($milllocation).
                                     '9J';
                    $altgoodoutputName = '';
                }
            } else {
                if ( $AdminTakingMove ) {
                    $altgoodoutput = '2B'.
                                     callmovetimediff().
                                     letter_end_number($_SESSION['MyUserID']).
                                     letter_end_number($_SESSION['MyGenderCode']).
                                     letter_end_number($thecard).
                                     letter_end_number($milllocation).
                                     '9J';
                    $altgoodoutputName = '|'.$_SESSION['MyUserName'];
                } else {
                    $altgoodoutput = 'G'.
                                     callmovetimediff().
                                     letter_end_number($thecard).
                                     letter_end_number($milllocation).
                                     '9J';
                    $altgoodoutputName = '';
                }
            }
            if ( $GAME['CottonDemand'] < 8 ) {
                $CheckCtu = true;
                $Boost = array(3,3,2,2,1,1,0,0);
                $Boost = $Boost[$GAME['CottonDemand']];
                $GAME['IncomeSpace'][$GAME['PlayerToMove']] += $Boost;
                    // If IncomeSpace[...] ends up above 99,
                    // it's taken care of by the call to fliptile()
                fliptile($milllocation);
                if ( !DMSaleSuccessProbability() ) {
                    $GAME['CottonDemand'] = 8;
                    $altgoodoutput .= '9J';
                }
            } else {
                $CheckCtu = false;
                $append_action_end_note = false;
            }
        }
        $GAME['ContinueSellingMode'] = 0;
        if ( $CheckCtu ) {
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
                     $GAME['SpaceCubes'][$i] ) {
                    $PortsArray[] = $i;
                }
            }
            for ($i=0;$i<count($MillsArray);$i++) {
                if ( $GAME['HasPort'][$GAME['spacetowns'][$MillsArray[$i]]] and
                     $GAME['CottonDemand'] < 8
                     ) {
                    $GAME['ContinueSellingMode'] = 1;
                    break;
                }
                for ($j=0;$j<count($PortsArray);$j++) {
                    if ( $GAME['CoalNet'][$GAME['spacetowns'][$MillsArray[$i]]] ==
                             $GAME['CoalNet'][$GAME['spacetowns'][$PortsArray[$j]]] ) {
                        $GAME['ContinueSellingMode'] = 1;
                        break 2;
                    }
                }
            }
        }
        if ( !$GAME['ContinueSellingMode'] and $append_action_end_note ) {
            $altgoodoutput .= '9I';
        }
    } else {
        $altgoodoutput = '';
        $altgoodoutputName = '';
    }
    return array($errorlist,'',$altgoodoutput,$altgoodoutputName);
}

?>