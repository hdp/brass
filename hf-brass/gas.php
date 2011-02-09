<?php

function DoTask() {
    global $Administrator, $GAME, $PersonActing, $SystemActing, $unexpectederrormessage;
    if ( $GAME['GameStatus'] != 'In Progress' ) {
        echo '<html><head><title>'.
        transtext('memCannotMoveT').
        '</title><body>'.
        str_replace('\gameid', $GAME['GameID'], transtext('memCannotMoveM')).
        '</body></html>';
        die();
    }
    if ( $GAME['PlayerToMove'] == $GAME['MyColour'] or
         ( $Administrator and @$_POST['AdminMoveYes'] )
         ) {
        if ( $_POST['ProgressDigit'] != $GAME['NumMovesMade'] ) {
            echo '<html><head><title>'.
                 transtext('memCheckIntegerT').
                 '</title></head><body>'.
                 str_replace('\gameid', $GAME['GameID'], transtext('memCheckIntegerM')).
                 '</body></html>';
            exit();
        }
        if ( !isset($_POST['LinkToBuild']) ) {
            $formdetails['LinkToBuild'] = 50;
        } else if ( $_POST['LinkToBuild'] == 'StopBuilding' ) {
            $formdetails['LinkToBuild'] = 50;
        } else {
            $formdetails['LinkToBuild'] = (int)$_POST['LinkToBuild'];
            if ( $formdetails['LinkToBuild'] < 0 or
                 ( $GAME['RailPhase'] and $formdetails['LinkToBuild'] >= $GAME['NumRailLinks'] ) or
                 ( !$GAME['RailPhase'] and
                   ( $formdetails['LinkToBuild'] >= $GAME['NumCanalLinks'] or
                     ( ( ( $GAME['ModularBoardParts'] & 8 ) == 0 ) and $formdetails['LinkToBuild'] == 12 and $GAME['GVersion'] == 1 ) ) ) ) {
                die(transtext('memBadMoveData'));
            }
        }
        if ( !isset($_POST['FirstCard']) ) {
            $formdetails['CardA'] = 9;
        } else if ( $_POST['FirstCard'] == 'NoCardSelected' ) {
            $formdetails['CardA'] = 9;
        } else {
            $formdetails['CardA'] = (int)$_POST['FirstCard'];
            if ( $formdetails['CardA'] < 0 or $formdetails['CardA'] > 7 ) { die(transtext('memBadMoveData')); }
        }
        if ( !isset($_POST['SecondCard']) ) {
            $formdetails['CardB'] = 9;
        } else if ( $_POST['SecondCard'] == 'NoCardSelected' ) {
            $formdetails['CardB'] = 9;
        } else {
            $formdetails['CardB'] = (int)$_POST['SecondCard'];
            if ( $formdetails['CardB'] < 0 or $formdetails['CardB'] > 7 ) { die(transtext('memBadMoveData')); }
        }
        if ( !isset($_POST['MoveType']) ) {
            $formdetails['MoveType'] = 0;
        } else if ( $_POST['MoveType'] == 9 ) {
            echo '<html><head><title>'.
                 transtext('memNoMoveTypeT').
                 '</title></head><body>'.
                 str_replace('\gameid', $GAME['GameID'], transtext('memNoMoveTypeM')).
                 '</body></html>';
            die();
        } else {
            $formdetails['MoveType'] = (int)$_POST['MoveType'];
            if ( $formdetails['MoveType'] < 0 or $formdetails['MoveType'] > 6 ) { die(transtext('memBadMoveData')); }
            if ( $GAME['HandSize'][$GAME['PlayerToMove']] == 2 and
                 ( $formdetails['MoveType'] == 1 or $formdetails['MoveType'] == 6 )
                 ) {
                $formdetails['CardA'] = 0;
                $formdetails['CardB'] = 1;
            }
        }
        if ( $formdetails['MoveType'] == 6 and !$_POST['PassSure'] ) {
            echo '<html><head><title>'.
                 transtext('memBoxUntickedT').
                 '</title><body>'.
                 str_replace('\gameid', $GAME['GameID'], transtext('memBoxUntickedM')).
                 '</body></html>'; die();
        }
        if ( !isset($_POST['TileType0']) ) { $TileT[0] = 9; }
        else                               { $TileT[0] = (int)$_POST['TileType0']; }
        if ( !isset($_POST['TileType1']) ) { $TileT[1] = 9; }
        else                               { $TileT[1] = (int)$_POST['TileType1']; }
        if ( !isset($_POST['TileType2']) ) { $TileT[2] = 9; }
        else                               { $TileT[2] = (int)$_POST['TileType2']; }
        if ( !isset($_POST['TileType3']) ) { $TileT[3] = 9; }
        else                               { $TileT[3] = (int)$_POST['TileType3']; }
        if ( !isset($_POST['TileType4']) ) { $TileT[4] = 9; }
        else                               { $TileT[4] = (int)$_POST['TileType4']; }
        if ( !isset($_POST['TileType5']) ) { $TileT[5] = 9; }
        else                               { $TileT[5] = (int)$_POST['TileType5']; }
        if ( !isset($_POST['TileType6']) ) { $TileT[6] = 9; }
        else                               { $TileT[6] = (int)$_POST['TileType6']; }
        if ( !isset($_POST['TileType7']) ) { $TileT[7] = 9; }
        else                               { $TileT[7] = (int)$_POST['TileType7']; }
        if ( !isset($_POST['TileTypeY']) ) { $TileTY = 9; }
        else                               { $TileTY = (int)$_POST['TileTypeY']; }
        if ( !isset($_POST['TileType']) )                  { $TileTX = 9; }
        else if ( $_POST['TileType'] == 'StopDeveloping' ) { $TileTX = 9; }
        else {
            $TileTX = (int)$_POST['TileType'];
            if ( $TileTX < 0 or $TileTX > 4 ) { die(transtext('memBadMoveData')); }
        }
        if ( $GAME['SecondDevelopMode'] ) {
            $formdetails['TileType'] = $TileTX;
        } else if ( $formdetails['MoveType'] == 3 ) {
            $formdetails['TileType'] = $TileTX;
        } else if ( $formdetails['MoveType'] == 1 ) {
            $formdetails['TileType'] = $TileTY;
            if ( $formdetails['TileType'] < 0 or $formdetails['TileType'] > 4 ) { die(transtext('memBadMoveData')); }
        } else if ( !$GAME['ContinueSellingMode'] and
                    !$GAME['SecondRailMode'] and
                    !$GAME['DebtMode']
                    ) {
            if ( $formdetails['CardA'] != 9 ) {
                $formdetails['TileType'] = $TileT[$formdetails['CardA']];
                if ( $formdetails['TileType'] < 0 or $formdetails['TileType'] > 4 ) {
                    die(transtext('memBadMoveData'));
                }
            }
        }
        if ( !isset($_POST['IndustrySpace0']) ) { $IndSp[0] = 50; }
        else                                    { $IndSp[0] = (int)$_POST['IndustrySpace0']; }
        if ( !isset($_POST['IndustrySpace1']) ) { $IndSp[1] = 50; }
        else                                    { $IndSp[1] = (int)$_POST['IndustrySpace1']; }
        if ( !isset($_POST['IndustrySpace2']) ) { $IndSp[2] = 50; }
        else                                    { $IndSp[2] = (int)$_POST['IndustrySpace2']; }
        if ( !isset($_POST['IndustrySpace3']) ) { $IndSp[3] = 50; }
        else                                    { $IndSp[3] = (int)$_POST['IndustrySpace3']; }
        if ( !isset($_POST['IndustrySpace4']) ) { $IndSp[4] = 50; }
        else                                    { $IndSp[4] = (int)$_POST['IndustrySpace4']; }
        if ( !isset($_POST['IndustrySpace5']) ) { $IndSp[5] = 50; }
        else                                    { $IndSp[5] = (int)$_POST['IndustrySpace5']; }
        if ( !isset($_POST['IndustrySpace6']) ) { $IndSp[6] = 50; }
        else                                    { $IndSp[6] = (int)$_POST['IndustrySpace6']; }
        if ( !isset($_POST['IndustrySpace7']) ) { $IndSp[7] = 50; }
        else                                    { $IndSp[7] = (int)$_POST['IndustrySpace7']; }
        if ( !isset($_POST['IndustrySpaceY']) ) { $IndSpY = 50; }
        else                                    { $IndSpY = (int)$_POST['IndustrySpaceY']; }
        if ( !isset($_POST['IndustrySpace']) ) {
            $IndSpX = 50;
            if ( $GAME['DebtMode'] ) { die(transtext('memBadMoveData')); }
        } else if ( $_POST['IndustrySpace'] == 'StopSelling' ) {
            $IndSpX = 50;
            if ( $GAME['DebtMode'] ) { die(transtext('memBadMoveData')); }
        } else {
            $IndSpX = (int)$_POST['IndustrySpace'];
            if ( $IndSpX < 0 or $IndSpX >= $GAME['NumIndustrySpaces'] ) {
                die(transtext('memBadMoveData'));
            }
        }
        if ( $GAME['DebtMode'] or $GAME['ContinueSellingMode'] ) {
            $formdetails['IndustrySpace'] = $IndSpX;
        } else if ( $formdetails['MoveType'] == 5 ) {
            $formdetails['IndustrySpace'] = $IndSpX;
        } else if ( $formdetails['MoveType'] == 1 ) {
            $formdetails['IndustrySpace'] = $IndSpY;
            if ( $formdetails['IndustrySpace'] < 0 or
                 $formdetails['IndustrySpace'] >= $GAME['NumIndustrySpaces']
                 ) {
                die(transtext('memBadMoveData'));
            }
        } else if ( !$GAME['SecondDevelopMode'] and !$GAME['SecondRailMode'] ) {
            $formdetails['IndustrySpace'] = $IndSp[$formdetails['CardA']];
            if ( $formdetails['IndustrySpace'] < 0 or
                 $formdetails['IndustrySpace'] >= $GAME['NumIndustrySpaces']
                 ) {
                die(transtext('memBadMoveData'));
            }
        }
        if ( !isset($_POST['CoalSource']) ) {
            $formdetails['CoalSource'] = 50;
        } else if ( $_POST['CoalSource'] == 'DemandTrack' ) {
            $formdetails['CoalSource'] = 50;
        } else {
            $formdetails['CoalSource'] = (int)$_POST['CoalSource'];
            if ( $formdetails['CoalSource'] < 0 or
                 $formdetails['CoalSource'] >= $GAME['NumIndustrySpaces']
                 ) {
                die(transtext('memBadMoveData'));
            }
        }
        if ( !isset($_POST['IronSource']) ) {
            $formdetails['IronSource'] = 50;
        } else if ( $_POST['IronSource'] == 'DemandTrack' ) {
            $formdetails['IronSource'] = 50;
        } else {
            $formdetails['IronSource'] = (int)$_POST['IronSource'];
            if ( $formdetails['IronSource'] < 0 or
                 $formdetails['IronSource'] >= $GAME['NumIndustrySpaces']
                 ) {
                die(transtext('memBadMoveData'));
            }
        }
        if ( !isset($_POST['PortSpace']) ) {
            $formdetails['PortSpace'] = 50;
        } else if ( $_POST['PortSpace'] == 'DistantMarket' ) {
            $formdetails['PortSpace'] = 50;
        } else {
            $formdetails['PortSpace'] = (int)$_POST['PortSpace'];
            if ( $formdetails['PortSpace'] < 0 or
                 $formdetails['PortSpace'] >= $GAME['NumIndustrySpaces']
                 ) {
                die(transtext('memBadMoveData'));
            }
        }
        if ( !isset($_POST['LoanAmount']) ) {
            $formdetails['LoanAmount'] = 3;
        } else {
            $formdetails['LoanAmount'] = (int)$_POST['LoanAmount'];
            if ( $formdetails['LoanAmount'] < 1 or
                 $formdetails['LoanAmount'] > 3 ) {
                die(transtext('memBadMoveData'));
            }
        }
        $SystemActing = false;
        $PersonActing = $_SESSION['MyUserID'];
        $ColourMoving = $GAME['PlayerToMove'];
        $errorlist = moveexecute($formdetails);
        $SystemActing = true;
        if ( $errorlist == '' ) {
            $didsomething = 1;
            while ( $didsomething ) { $didsomething = gamecheck(); }
            dbformatgamedata();
            echo "<html><head><title>Move Executed</title><script type=\"text/javascript\"><!--\nfunction delayer(){\nwindow.location =\"board.php?GameID={$GAME['GameID']}\"\n}\n//-->\n</script>\n</head><body onLoad=\"setTimeout('delayer()', 2000)\">".
                 str_replace('\gameid', $GAME['GameID'], transtext('moveSuccess'));
            if ( $GAME['GameStatus'] != 'In Progress' or $GAME['PlayerToMove'] != $ColourMoving ) {
                echo ' '.
                     str_replace('\userid', $_SESSION['MyUserID'], transtext('moveAlternateLk')); // "Alternatively, click _here_" etc
            }
            echo ')<p><font color="#';
            if ( $GAME['GameStatus'] == 'In Progress' and $GAME['PlayerToMove'] == $ColourMoving ) {
                echo 'FF0000"><b>'.
                     transtext('moveStillYou');
            } else {
                echo '00BB00"><b>';
                if ( $GAME['RailPhase'] and
                     $GAME['Round'] == $GAME['NumRounds'] and
                     !$GAME['DebtMode'] and
                     $GAME['GameStatus'] != 'Finished'
                     ) {
                    $ReferenceTurnOrder = array_flip(str_split($GAME['TurnOrder'],1));
                    if ( $ReferenceTurnOrder[$ColourMoving] > $ReferenceTurnOrder[$GAME['PlayerToMove']] ) {
                        echo transtext('moveNoLongerYou');
                    } else {
                        echo transtext('moveYouAreDone');
                    }
                } else if ( $GAME['GameStatus'] != 'Finished' ) {
                    echo transtext('moveNoLongerYou');
                } else { // This last "else" is redundant, I think, but I'll leave it as I can't be bothered to make sure of that
                    echo transtext('moveYouAreDone');
                }
            }
            echo '</b></font></body></html>';
        } else {
            echo '<html><head><title>'.
            transtext('memProblemsT'). // "There Were Problems With Your Move" (list of errors)
            '</title><body>'.
            str_replace( array('\errorlist', '\gameid'      ),
                         array($errorlist  , $GAME['GameID']),
                         transtext('memProblemsM')
                         ).
            '</body></html>';
        }
    } else if ( $Administrator ) {
        echo '<html><head><title>You did not select the tick box</title><body>You did not select the tick box to indicate that you want to intervene in the current player\'s turn. Click <a href="board.php?GameID='.$GAME['GameID'].'">here</a> to return to the game.</body></html>';
    } else if ( $GAME['MyColour'] != 50 ) {
        die($unexpectederrormessage);
    } else {
        echo '<html><head><title>'.
             transtext('memNotYourGameT').
             '</title><body>'.
             str_replace('\gameid', $GAME['GameID'], transtext('memNotYourGameM')).
             '</body></html>';
    }
}

?>