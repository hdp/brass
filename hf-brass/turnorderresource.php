<?php

function DoTurnOrder($dodebtcheck) {
    global $GAME;
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        if ( !$GAME['PlayerExists'][$i] ) {
            $GAME['AmountSpent'][$i] = 1000;
        }
    }
    $FlippedTurnOrder = array_flip(str_split($GAME['TurnOrder'],1));
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        $ReindexedFlippedTurnOrder[$i] = $FlippedTurnOrder[$i];
            // Without this step, $FlippedTurnOrder has the correct key-value pairs, but they may
            // appear in the wrong order. This results in the call to array_multisort going wrong.
    }
    $whoarray = array(0,1,2,3,4);
    array_multisort($GAME['AmountSpent'],       SORT_ASC,
                    $ReindexedFlippedTurnOrder, SORT_ASC,
                    $whoarray
                    );
    $GAME['AmountSpent'] = array(0,0,0,0,0);
    $GAME['TurnOrder'] = implode('',$whoarray);
    if ( $dodebtcheck ) {
        $GAME['Round']++;
        for ($i=0;$i<MAX_PLAYERS;$i++) {
           if ( $GAME['PlayerExists'][$i] ) {
                $GAME['Money'][$i] += $GAME['incomeamounts'][$GAME['IncomeSpace'][$i]];
            }
        }
        $TheDebtors = array();
        for ($i=0;$i<MAX_PLAYERS;$i++) {
            if ( $GAME['PlayerExists'][$i] and $GAME['Money'][$i] < 0 ) {
                $mytilesarray = array();
                for ($j=0;$j<$GAME['NumIndustrySpaces'];$j++) {
                    if ( $GAME['SpaceStatus'][$j] == $i ) {
                        $repayamount = $GAME['TileCosts'][$GAME['SpaceTile'][$j]][$GAME['TechLevels'][$j]-1] / 2;
                        $repayamount = (int)$repayamount;
                        $mytilesarray[] = array($j,$repayamount,0);
                    }
                }
                $numberoftiles = count($mytilesarray);
                if ( $numberoftiles < 5 ) {
                    $bitsarray = array(array(1,0,0,0),array(0,1,0,0),array(1,1,0,0),
                                       array(0,0,1,0),array(1,0,1,0),array(0,1,1,0),
                                       array(1,1,1,0),array(0,0,0,1),array(1,0,0,1),
                                       array(0,1,0,1),array(1,1,0,1),array(0,0,1,1),
                                       array(1,0,1,1),array(0,1,1,1),array(1,1,1,1)
                                       );
                    for ($j=0;$j<pow(2,$numberoftiles)-1;$j++) {
                        $resultmoney = $GAME['Money'][$i];
                        for ($k=0;$k<$numberoftiles;$k++) {
                            $resultmoney += $bitsarray[$j][$k]*$mytilesarray[$k][1];
                        }
                        if ( $resultmoney >= 0 ) {
                            for ($k=0;$k<$numberoftiles;$k++) {
                                if ( !$bitsarray[$j][$k] ) { $mytilesarray[$k][2] = 1; }
                            }
                        }
                    }
                    for ($j=0;$j<$numberoftiles;$j++) {
                        if ( !$mytilesarray[$j][2] ) {
                            destroytile($mytilesarray[$j][0],0,1);
                            $GAME['Money'][$i] += $mytilesarray[$j][1];
                            $GAME['AltGameTicker'] .= letter_end_number($mytilesarray[$j][0]);
                            $numberoftiles--;
                        }
                    }
                }
                if ( $GAME['Money'][$i] < 0 and $numberoftiles > 0 ) {
                    $TheDebtors[] = $i;
                }
            }
        }
        if ( count($TheDebtors) ) {
            $GAME['DebtMode'] = 1;
            for ($i=MAX_PLAYERS-1;$i>=0;$i--) {
                if ( in_array($GAME['TurnOrder'][$i],$TheDebtors) ) {
                    $GAME['PlayerToMove'] = $GAME['TurnOrder'][$i];
                }
            }
        } else {
            $GAME['PlayerToMove'] = $GAME['TurnOrder'][0];
        }
        $GAME['AltGameTicker'] .= '9G';
    }
}

?>