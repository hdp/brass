<?php

function develop($tiletype,$ironsource,$continuing,$thecard) {
    global $GAME,$SystemActing;
    $errorlist = '';
    if ( !$GAME['RemainingTiles'][$tiletype][$GAME['PlayerToMove']] ) {
        $errorlist = transtext('memOutOfTiles').'<br>';
            // "You have run out of industry tiles of the chosen type."
    }
    if ( $ironsource == 50 and
         $GAME['Money'][$GAME['PlayerToMove']] < $GAME['cubeprice'][$GAME['IronDemand']]
         ) {
        $errorlist = transtext('memDevPoor').'<br>';
            // "You cannot develop, as you cannot afford the iron cube."
    }
    switch ( $ironsource ) {
        case 90: $errorlist .= transtext('memIronNotValid').'<br>'; break;
            // "The selected source of iron is not valid."
        case 91: $errorlist .= transtext('memIronOnBoard').'<br>'; break;
            // "You cannot buy iron from the Demand Track,
            // as there is iron available on the board for you to use."
    }
    if ( $ironsource == 50 ) { $altironsource = 99;          }
    else                     { $altironsource = $ironsource; }
    if ( $errorlist == '' ) {
        $GAME['RemainingTiles'][$tiletype][$GAME['PlayerToMove']]--;
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
        if ( $SystemActing ) {
            $AdminTakingMove = 0;
        } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
            $AdminTakingMove = 0;
        } else {
            $AdminTakingMove = 1;
        }
        if ( $continuing ) {
            if ( $AdminTakingMove ) {
                $altgoodoutput = '2D'.
                                 callmovetimediff().
                                 letter_end_number($_SESSION['MyUserID']).
                                 letter_end_number($_SESSION['MyGenderCode']).
                                 letter_end_number($tiletype).
                                 letter_end_number($altironsource);
                $altgoodoutputName = '|'.$_SESSION['MyUserName'];
           } else {
                $altgoodoutput = 'I'.
                                 callmovetimediff().
                                 letter_end_number($tiletype).
                                 letter_end_number($altironsource);
                $altgoodoutputName = '';
           }
            $GAME['SecondDevelopMode'] = 0;
        } else {
            if ( $AdminTakingMove ) {
                $altgoodoutput = '1J'.
                                 callmovetimediff().
                                 letter_end_number($_SESSION['MyUserID']).
                                 letter_end_number($_SESSION['MyGenderCode']).
                                 letter_end_number($thecard).
                                 letter_end_number($tiletype).
                                 letter_end_number($altironsource);
                $altgoodoutputName = '|'.$_SESSION['MyUserName'];
            } else {
                $altgoodoutput = 'E'.
                                 callmovetimediff().
                                 letter_end_number($thecard).
                                 letter_end_number($tiletype).
                                 letter_end_number($altironsource);
                $altgoodoutputName = '';
            }
            if ( $GAME['RemainingTiles'][0][$GAME['PlayerToMove']] or
                 $GAME['RemainingTiles'][1][$GAME['PlayerToMove']] or
                 $GAME['RemainingTiles'][2][$GAME['PlayerToMove']] or
                 $GAME['RemainingTiles'][3][$GAME['PlayerToMove']] or
                 $GAME['RemainingTiles'][4][$GAME['PlayerToMove']]
                 ) {
                $CanGetIronFree = false;
                for ($i=0;$i<$GAME['NumIndustrySpaces'];$i++) {
                    if ( $GAME['SpaceStatus'] != 9 and
                         $GAME['SpaceTile'][$i] == 2 and
                         $GAME['SpaceCubes'][$i] ) {
                        $CanGetIronFree = true;
                    }
                }
                if ( !$CanGetIronFree and
                     $GAME['Money'][$GAME['PlayerToMove']] < $GAME['cubeprice'][$GAME['IronDemand']]
                     ) {
                    $altgoodoutput .= '9IA';
                } else {
                    $GAME['SecondDevelopMode'] = 1;
                }
            } else {
                $altgoodoutput .= '9IB';
            }
        }
    } else {
        $altgoodoutput = '';
        $altgoodoutputName = '';
    }
    return array($errorlist,'',$altgoodoutput,$altgoodoutputName);
}

?>