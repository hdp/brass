<?php

$PlayControls = 1;
get_translation_module(23);
$CubeSourceArray = array('Red\'s','Yellow\'s','Green\'s','Purple\'s','Grey\'s','','','','Orphan');
$BuildSiteArray = array('Overbuild Red','Overbuild Yellow',
                        'Overbuild Green','Overbuild Purple',
                        'Overbuild Grey','',
                        '','',
                        'Overbuild Orphan','Unoccupied');
$CubeSourceArray[$PlayerToMove] = 'Yours';
$BuildSiteArray[$PlayerToMove] = 'Overbuild Self';
if ( $GameStatus == 'In Progress' and ( $MyColour == $PlayerToMove or $Administrator ) ) {
    get_translation_module(22);
    $AnyMoveForm = 1;
    if ( $Administrator and $MyColour != $PlayerToMove ) {
        $MoveForm = '<p><b><i>The controls in this box allow control over the player who is currently to move - <font size=4 color="#FF0000">THIS PLAYER IS NOT YOU</font>.</i></b>';
    } else if ( $MyColour == $PlayerToMove and $KickVoteActive and ( $GameStatus == 'In Progress' or $GameStatus == 'Recruiting Replacement' ) ) {
        $MoveForm = '<p><font color="#FF0000"><b>'.
        transtext('miKickVoteNotice').
        '</b></font>';
    } else {
        $MoveForm = '';
    }
    if ( $MyColour == $PlayerToMove and $ShowAsBlue ) { $DivBGCol = 'BFDFFF'; }
    else                                              { $DivBGCol = $playercolours[$PlayerToMove]; }
    if ( !$DebtMode and !$SecondRailMode and !$SecondDevelopMode and !$ContinueSellingMode ) {
        $NormalMoveForm = 1;
        $Height = 310;
        $TierTwoDependentsDepth = 140;
        $TierThreeDependentsDepth = 180;
        $TierFourDependentsDepth = 220;
        $TierFiveDependentsDepth = 260;
        $EvenTest = $HandSize[$PlayerToMove] % 2;
        if ( !$RailPhase and $Round == 1 ) { $EvenTest = 1; }
        if ( $HandSize[$PlayerToMove] > 2 and ( !$NoSwapCards or !$EvenTest ) ) {
            $Height += 40;
            $TierTwoDependentsDepth += 40;
            $TierThreeDependentsDepth += 40;
            $TierFourDependentsDepth += 40;
            $TierFiveDependentsDepth += 40;
        }
        if ( $Administrator and $MyColour != $PlayerToMove ) { $Height += 40; }
        if ( $RailPhase and $NumRounds - $Round == 4 ) {
            $Height += 42;
            $TierTwoDependentsDepth += 42;
            $TierThreeDependentsDepth += 42;
            $TierFourDependentsDepth += 42;
            $TierFiveDependentsDepth += 42;
            $CurrentDepth = 97;
        } else {
            $CurrentDepth = 55;
        }
        if ( $AlertDisplay ) { $Height += 60; }
        $MsgLocation = $Height / 2;
        $MsgLocation = (int)$MsgLocation;
        $MsgLocation -= 20;
        $MoveForm .= '<p><div style="position: relative; border: 1px solid black; width: 650px; height: '.
                     $Height.
                     'px; background-color: #'.
                     $DivBGCol.
                     '"><div id="dNoJavaScriptMsg" style="position: absolute; top: '.
                     $MsgLocation.
                     'px; left: 15px; text-align: center; width: 620px">'.
                     transtext('miMustEnableJS').
                     '</div><div id="dCardX" style="position: absolute; top: 15px; left: 15px; width: 620px; display: none">';
        if ( $RailPhase and $NumRounds - $Round == 4 ) {
            $MoveForm .= '<font size=5 color="#FF0000"><i><b>'.
                         transtext('miLoansAlert').
                         '</b></i></font><p>';
        }
        if ( $HandSize[$PlayerToMove] == 1 ) {
            $MoveForm .= transtext('bpOnlyOneCard').
                         ' <b>'.
                         DecipherCardSymbol($Cards[$PlayerToMove][0]).
                         '</b>.<input type="hidden" id="FirstCard" name="FirstCard" value="0">';
        } else {
            if ( $HandSize[$PlayerToMove] > 2 ) {
                $MoveForm .= transtext('miCardUseSwap1st');
                $SelectOneText = '<option value="NoCardSelected">'.
                                 transtext('miListSelectOne');
            } else {
                $MoveForm .= transtext('miCardUseProviso');
                $SelectOneText = '';
            }
            $MoveForm .= ' <select id="FirstCard" name="FirstCard" onChange="AlterForm()">'.
                         $SelectOneText;
            for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
                $MoveForm .= '<option value="'.
                             $i.
                             '">'.
                             DecipherCardSymbol($Cards[$PlayerToMove][$i]);
            }
            $MoveForm .= '</select>';
        }
        $MoveForm .= '</div>';
        if ( $HandSize[$PlayerToMove] > 2 and ( !$NoSwapCards or !$EvenTest ) ) {
            $MoveForm .= '<div id="dCardY" style="position: absolute; top: '.
                         $CurrentDepth.
                         'px; left: 15px; width: 620px; display: none">';
            if ( !$EvenTest ) { $MoveForm .= transtext('miCardUseSwap2nd'); }
            else              { $MoveForm .= transtext('bpCardSwap2nd');    }
            $MoveForm .= ' <select name="SecondCard"><option value="NoCardSelected">'.
                         transtext('miListSelectOne');
            for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
                $MoveForm .= '<option value="'.
                             $i.
                             '">'.
                             DecipherCardSymbol($Cards[$PlayerToMove][$i]);
            }
            $MoveForm .= '</select></div>';
            $CurrentDepth += 40;
        }
        $MoveForm .= '<div id="dMoveType" style="position: absolute; top: '.
                     $CurrentDepth.
                     'px; left: 15px; width: 620px; display: none">'.
                     transtext('miTypeOfMove').
                     ' <select id="MoveType" name="MoveType" onChange="AlterForm()"><option value="9">'.
                     transtext('miListSelectOne').
                     '<option value="0">'.
                     transtext('miBuildIndustry');
        if ( $HandSize[$PlayerToMove] % 2 == 0 and ( $RailPhase or $Round > 1 ) ) {
            $MoveForm .=  '<option value="1">'.
                          transtext('miDoubleBuild');
        }
        if ( $RemainingTiles[5][$PlayerToMove] ) {
            $MoveForm .= '<option value="2">';
            if ( $RailPhase ) {
                $MoveForm .= transtext('miBuildRail(s)');
            } else {
                $MoveForm .= transtext('miBuildCanal');
            }
        }
        $MoveForm .= '<option value="3">'.
                     transtext('miDevelop');
        if ( $CanTakeLoans ) {
            $MoveForm .=  '<option value="4">'.
                          transtext('miTakeLoan');
        }
        $CurrentDepth += 40;
        $MoveForm .= '<option value="5">'.
                     transtext('miSellCotton').
                     '<option value="6">'.
                     transtext('miPass').
                     '</select></div><div id="dPassSure" style="position: absolute; top: '.
                     $CurrentDepth.
                     'px; left: 15px; width: 620px; display: none"><input type="checkbox" name="PassSure" value="1">'.
                     transtext('miPassSure');
        if ( $AlertDisplay ) {
            $MoveForm .= ' <font size=\'1\'>('.
                         transtext('miPassBoxExpln'). // "This tick box is here to stop you passing accidentally" etc
                         ')</font>';
        }
        $IsOdd = $HandSize[$PlayerToMove] % 2;
        if ( !$IsOdd and ( $RailPhase or $Round > 1 ) ) {
            $MoveForm .= '<p><font color="#FF0000">'.
                         transtext('miDblPassWarning'). // "Please note that if you choose to pass at this time then" blah blah blah
                         '</font>';
        }
        $MoveForm .= '</div><div id="dTileType" style="position: absolute; top: '.
                     $CurrentDepth.
                     'px; left: 15px; width: 620px; display: none">'.
                     transtext('miTileToDevelop').
                     ' <select id="TileType" name="TileType">';
        for ($i=0;$i<5;$i++) {
            if ( $RemainingTiles[$i][$PlayerToMove] ) {
                $MoveForm .= '<option value="'.$i.'">'.$industrynames[$i];
            }
        }
        $MoveForm .= '</select></div><div id="dIndustrySpaceX" style="position: absolute; top: '.
                     $CurrentDepth.
                     'px; left: 15px; width: 620px; display: none">'.
                     transtext('miSCCMLocation'). // "Location of Cotton Mill" (for selling cotton)
                     ' <select name="IndustrySpace">';
        for ($i=0;$i<$NumIndustrySpaces;$i++) {
            if ( $SpaceStatus[$i] == $PlayerToMove and !$SpaceTile[$i] and $SpaceCubes[$i] ) {
                $MoveForm .= '<option value="'.$i.'">'.$locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i];
            }
        }
        $MoveForm .= '</select><p>'.
                     transtext('miSCPortLocation'). // "Location of Port" (for selling cotton)
                     ' <select name="PortSpace">';
        $havenotprintedselected = 1;
        $numopts = 0;
        for ($i=0;$i<$NumIndustrySpaces;$i++) {
            if ( $SpaceStatus[$i] != 9 and $SpaceTile[$i] == 3 and $SpaceCubes[$i] ) {
                $numopts++;
                if ( $havenotprintedselected and $SpaceStatus[$i] == $PlayerToMove ) { $havenotprintedselected = 0; $selectedtext = ' selected'; }
                else { $selectedtext = ''; }
                $MoveForm .= '<option value="'.$i.'"'.$selectedtext.'>'.$locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i].' ('.$CubeSourceArray[$SpaceStatus[$i]].')';
            }
        }
        if ( $CottonDemand < 8 ) {
            $MoveForm .= '<option value="DistantMarket">'.
                         transtext('miDistantMarket');
            $numopts++;
        }
        $MoveForm .= '</select>';
        if ( $AlertDisplay and $numopts > 1 ) {
            $MoveForm .= '<p><font color="#FF0000">'.
                         transtext('miSCWarning'). // "Please note that you MUST select a legal Port to sell from" blah blah blah
                         '</font>';
        }
        $MoveForm .= '</div><div id="dDoubleBuildOpts" style="position: absolute; top: '.
                     $CurrentDepth.'px; left: 15px; width: 620px; display: none">'.
                     transtext('miTileToBuild').
                     ' <select id="TileTypeY" name="TileTypeY" onChange="AlterForm()">';
        for ($i=0;$i<5;$i++) {
            if ( $RemainingTiles[$i][$PlayerToMove] and ( $i < 4 or $RemainingTiles[4][$PlayerToMove] < 5 ) ) {
                $MoveForm .= '<option value="'.
                             $i.
                             '">'.
                             $industrynames[$i];
            }
        }
        $MoveForm .= '</select><p>'.
                     transtext('miSpaceToBuild').
                     ' <select name="IndustrySpaceY">';
        for ($i=0;$i<$NumIndustrySpaces;$i++) {
            if ( ( $SpaceExistenceArray[$i] & $ModularBoardParts ) or $SpaceAlwaysExists[$i] ) {
                $MoveForm .= '<option value="'.
                             $i.
                             '">'.
                             $locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i].
                             ' ('.
                             $BuildSiteArray[$SpaceStatus[$i]].
                             ')';
            }
        }
        $MoveForm .= '</select></div><div id="dLink" style="position: absolute; top: '.
                     $CurrentDepth.
                     'px; left: 15px; width: 620px; display: none">';
        if ( $RailPhase ) {
            $MoveForm .= transtext('miRailToBuild').
                         ' <select name="LinkToBuild">';
            for ($i=0;$i<$NumRailLinks;$i++) {
                if ( $LinkStatus[$i] == 9 and ( $RailAlwaysExists[$i] or ( $ModularBoardParts & $RailExistenceArray[$i] ) ) ) { $MoveForm .= '<option value="'.$i.'">'.$locationnames[$RailStarts[$i]].' - '.$locationnames[$RailEnds[$i]]; }
            }
        } else {
            $MoveForm .= transtext('miCanalToBuild').
                         ' <select name="LinkToBuild">';
            for ($i=0;$i<$NumCanalLinks;$i++) {
                if ( $LinkStatus[$i] == 9 and ( $CanalAlwaysExists[$i] or ( $ModularBoardParts & $CanalExistenceArray[$i] ) ) ) { $MoveForm .= '<option value="'.$i.'">'.$locationnames[$CanalStarts[$i]].' - '.$locationnames[$CanalEnds[$i]]; }
            }
        }
        $MoveForm .= '</select>';
        if ( $RemainingTiles[5][$PlayerToMove] == 1 ) {
            $MoveForm .= ' <b>Warning: You have only one link marker left!</b>';
        } else if ( $RemainingTiles[5][$PlayerToMove] < 7 ) {
            $MoveForm .= ' <b>Warning: You have only '.$RemainingTiles[5][$PlayerToMove].' link markers left!</b>';
        }
        $MoveForm .= '</div><div id="dLoan" style="position: absolute; top: '.
                     $CurrentDepth.
                     'px; left: 15px; width: 620px; display: none">'.
                     transtext('miLoanAmount').
                     ' <select name="LoanAmount"><option value="1">'.
                     moneyformat(10).
                     '<option value="2">'.
                     moneyformat(20).
                     '<option value="3" selected>'.
                     moneyformat(30).
                     '</select></div>';
        for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
            $MoveForm .= '<div id="dBuildOpts'.
                         $i.
                         '" style="position: absolute; top: '.
                         $CurrentDepth.
                         'px; left: 15px; width: 620px; display: none">'.
                         transtext('miTileToBuild').
                         ' ';
            if ( $Cards[$PlayerToMove][$i] > $TopLocationCard ) {
                $MoveForm .= '<b>'.
                             $industrynames[$carddetailarray[$Cards[$PlayerToMove][$i]]].
                             '</b><input type="hidden" id="TileType'.
                             $i.
                             '" name="TileType'.
                             $i.
                             '" value="'.
                             $carddetailarray[$Cards[$PlayerToMove][$i]].
                             '">';
            } else {
                $MoveForm .= '<select id="TileType'.
                             $i.
                             '" name="TileType'
                             .$i.
                             '" onChange="AlterForm()">';
                $NothingAdded = 1;
                for ($j=0;$j<5;$j++) {
                    $CanBuildThis = 0;
                    for ($k=0;$k<$NumIndustrySpaces;$k++) {
                        if ( $spacetowns[$k] == $carddetailarray[$Cards[$PlayerToMove][$i]] and
                             ( ( $SpaceExistenceArray[$k] & $ModularBoardParts ) or $SpaceAlwaysExists[$k] ) and
                             ( ( $SpaceStatus[$k] != 9 and $SpaceTile[$k] == $j ) or ( $SpaceStatus[$k] == 9 and
                             ( $tileindustries[$k] == $j or
                               ( $j < 2 and $tileindustries[$k] == 5 ) or
                               ( ( !$j or $j == 3 ) and $tileindustries[$k] == 6 ) or
                               ( ( $j == 2 or $j == 3 ) and $tileindustries[$k] == 7 ) ) ) ) ) {
                            $CanBuildThis = 1;
                        }
                    }
                    if ( $RemainingTiles[$j][$PlayerToMove] and
                         ( $j < 4 or $RemainingTiles[4][$PlayerToMove] < 5 ) and
                         $CanBuildThis ) {
                        $MoveForm .= '<option value="'.
                                     $j.
                                     '">'.
                                     $industrynames[$j];
                        $NothingAdded = 0;
                    }
                }
                if ( $NothingAdded ) {
                    $MoveForm .= '<option value="0">'.
                                 transtext('miCardUnusable');
                }
                $MoveForm .= '</select>';
            }
            $MoveForm .= '<p>'.
                         transtext('miSpaceToBuild').
                         ' <select name="IndustrySpace'.
                         $i.
                         '">';
            if ( $Cards[$PlayerToMove][$i] > $TopLocationCard ) {
                for ($j=0;$j<$NumIndustrySpaces;$j++) {
                    if ( ( ( $SpaceStatus[$j] != 9 and
                             $SpaceTile[$j] == $carddetailarray[$Cards[$PlayerToMove][$i]] ) or
                           ( $SpaceStatus[$j] == 9 and
                             ( $tileindustries[$j] == $carddetailarray[$Cards[$PlayerToMove][$i]] or
                               ( $carddetailarray[$Cards[$PlayerToMove][$i]] < 2 and $tileindustries[$j] == 5 ) or
                               ( ( !$carddetailarray[$Cards[$PlayerToMove][$i]] or
                                   $carddetailarray[$Cards[$PlayerToMove][$i]] == 3 ) and
                                 $tileindustries[$j] == 6 ) or
                               ( ( $carddetailarray[$Cards[$PlayerToMove][$i]] == 2 or
                                   $carddetailarray[$Cards[$PlayerToMove][$i]] == 3 ) and
                                 $tileindustries[$j] == 7 ) ) ) ) and
                         ( ( $SpaceExistenceArray[$j] & $ModularBoardParts ) or $SpaceAlwaysExists[$j] ) ) {
                        $MoveForm .= '<option value="'.
                                     $j.
                                     '">'.
                                     $locationnames[$spacetowns[$j]].$SpaceNumbersNotOrdinal[$j].
                                     ' ('.
                                     $BuildSiteArray[$SpaceStatus[$j]].
                                     ')';
                    }
                }
            } else {
                for ($j=0;$j<$NumIndustrySpaces;$j++) {
                    if ( $spacetowns[$j] == $carddetailarray[$Cards[$PlayerToMove][$i]] ) {
                        $MoveForm .= '<option value="'.
                                     $j.
                                     '">'.
                                     $locationnames[$spacetowns[$j]].$SpaceNumbersNotOrdinal[$j].
                                     ' ('.
                                     $BuildSiteArray[$SpaceStatus[$j]].
                                     ')';
                    }
                }
            }
            $MoveForm .= '</select></div>';
        }
        $MoveForm .= '<div id="dCoal" style="position: absolute; top: 0px; left: 15px; width: 620px; display: none">'.
                     transtext('miCoalSource').
                     ' <select name="CoalSource">';
        $havenotprintedselected = 1;
        $numCoalopts = 1;
        for ($i=0;$i<$NumIndustrySpaces;$i++) {
            if ( $SpaceStatus[$i] != 9 and $SpaceTile[$i] == 1 and $SpaceCubes[$i] ) {
                $numCoalopts++;
                if ( $havenotprintedselected and $SpaceStatus[$i] == $PlayerToMove ) {
                    $havenotprintedselected = 0;
                    $selectedtext = ' selected';
                }
                else { $selectedtext = ''; }
                $MoveForm .= '<option value="'.
                             $i.
                             '"'.
                             $selectedtext.
                             '>'.
                             $locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i].
                             ' ('.
                             $CubeSourceArray[$SpaceStatus[$i]].
                             ')';
            }
        }
        $MoveForm .= '<option value="DemandTrack">'.
                     transtext('miDemandTrack').
                     '</select></div><div id="dIron" style="position: absolute; top: 0px; left: 15px; width: 620px; display: none">'.
                     transtext('miIronSource').
                     ' <select name="IronSource">';
        $ShowDT = 1;
        $havenotprintedselected = 1;
        for ($i=0;$i<$NumIndustrySpaces;$i++) {
            if ( $SpaceStatus[$i] != 9 and $SpaceTile[$i] == 2 and $SpaceCubes[$i] ) {
                $ShowDT = 0;
                if ( $havenotprintedselected and $SpaceStatus[$i] == $PlayerToMove ) { $havenotprintedselected = 0; $selectedtext = ' selected'; }
                else { $selectedtext = ''; }
                $MoveForm .= '<option value="'.
                             $i.
                             '"'.
                             $selectedtext.
                             '>'.
                             $locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i].
                             ' ('.
                             $CubeSourceArray[$SpaceStatus[$i]].
                             ')';
            }
        }
        if ( $ShowDT ) {
            $MoveForm .= '<option value="DemandTrack">'.
                         transtext('miDemandTrack');
        }
        $MoveForm .= '</select></div>';
        if ( $AlertDisplay and $numCoalopts > 1 ) {
            $DisplayCAD = 1;
            $MoveForm .= '<div id="dMustSupplyC" style="position: absolute; top: 0px; left: 15px; width: 620px; display: none"><font color="#FF0000">'.
                         transtext('miCoalWarning'). // "Please note that you MUST select a legal source of coal" blah blah blah
                         '</font></div>';
        } else {
            $DisplayCAD = 0;
            $MoveForm .= '<div id="dMustSupplyC" style="position: absolute; top: 0px; left: 0px; width: 1px; height: 1px; z-index: -10; display: none"></div>';
        }
        $AMDepth = $Height - 75;
        $ButtonDepth = $Height - 40;
        if ( $MyColour != $PlayerToMove ) {
            $MoveForm .= '<div id="dAdminMove" style="position: absolute; top: '.
                         $AMDepth.
                         'px; left: 15px; width: 620px; display: none"><input type="checkbox" name="AdminMoveYes" value="1"> Yes, I do intend to make '.
                         $PlayerFullName[$PlayerToMove].
                         '\'s move for '.
                         $OtherPronounLC[$PlayerToMove].
                         '.</div>';
        }
        $MoveForm .= '<div id="dButtons" style="position: absolute; top: '.
                     $ButtonDepth.
                     'px; left: 15px; width: 620px; display: none"><input type="submit" name="FormSubmit" value="Submit Move" onSubmit="clearInterval(IntervalID);"></div>';
        if ( $HandSize[$PlayerToMove] > 2 ) {
            $MoveForm .= '<div id="dSwapCards" style="position: absolute; top: '.
                         $ButtonDepth.
                         'px; left: 235px; width: 400px; text-align: right; display: none">';
            if ( !$NoSwapCards ) {
                $MoveForm .= '<input type="submit" name="FormSubmit" value="Swap Cards">';
            }
            if ( $MyColour == $PlayerToMove ) {
                $MoveForm .= '<input type="submit" name="FormSubmit" value="Sort Cards">';
            }
            $MoveForm .= '</div>';
        }
        $TileReqC = array(0,0,0,0);
        $TileReqI = array(0,0,0,0);
        for ($i=0;$i<4;$i++) {
            if ( ( !$i and $RemainingTiles[0][$PlayerToMove] < 10 ) or $i == 2 ) { $TileReqC[$i] = 1; }
            if ( ( !$i and $RemainingTiles[0][$PlayerToMove] < 7 ) or ( $i == 1 and $RemainingTiles[1][$PlayerToMove] < 5 ) ) { $TileReqI[$i] = 1; }
        }
        $MoveFormScript = 'var TierDDB = '.$TierTwoDependentsDepth.'; var TierDDC = '.$TierThreeDependentsDepth.'; var TierDDD = '.$TierFourDependentsDepth.'; var TierDDE = '.$TierFiveDependentsDepth.'; var DisplayCAD = '.$DisplayCAD.'; var HandSize = '.$HandSize[$PlayerToMove].'; var TileReqC = new Array(';
        for ($i=0;$i<4;$i++) { $MoveFormScript .= $TileReqC[$i].','; }
        $MoveFormScript .= '1); var TileReqI = new Array(';
        for ($i=0;$i<4;$i++) { $MoveFormScript .= $TileReqI[$i].','; }

/////////////////////////////
/////////////////////////////
/////////////////////////////

        $MoveFormScript .= '1); var RailPhase = '.$RailPhase.';
function AlterForm() {
    if ( document.getElementById(\'MoveType\').value == \'0\' && HandSize == 1 ) {
        document.getElementById(\'dBuildOpts0\').style.display = \'block\';
    } else if ( document.getElementById(\'MoveType\').value == \'0\' && document.getElementById(\'FirstCard\').value == \'NoCardSelected\' ) {
        for (i=0;i<HandSize;i++) {
            document.getElementById(\'dBuildOpts\'+i).style.display = \'none\';
        }
    } else if ( document.getElementById(\'MoveType\').value == \'0\' ) {
        for (i=0;i<HandSize;i++) {
            if ( Number(document.getElementById(\'FirstCard\').value) == i ) {
                document.getElementById(\'dBuildOpts\'+i).style.display = \'block\';
            } else {
                document.getElementById(\'dBuildOpts\'+i).style.display =\'none\';
            }
        }
    } else {
        for (i=0;i<HandSize;i++) {
            document.getElementById(\'dBuildOpts\'+i).style.display = \'none\';
        }
    }
    if ( document.getElementById(\'MoveType\').value == \'1\' ) {
        document.getElementById(\'dDoubleBuildOpts\').style.display = \'block\';
    } else {
        document.getElementById(\'dDoubleBuildOpts\').style.display = \'none\';
    }
    if ( document.getElementById(\'MoveType\').value == \'2\' ) {
        document.getElementById(\'dLink\').style.display = \'block\';
    } else {
        document.getElementById(\'dLink\').style.display = \'none\';
    }
    if ( document.getElementById(\'MoveType\').value == \'3\' ) {
        document.getElementById(\'dTileType\').style.display = \'block\';
    } else {
        document.getElementById(\'dTileType\').style.display = \'none\';
    }
    if ( document.getElementById(\'MoveType\').value == \'4\' ) {
        document.getElementById(\'dLoan\').style.display = \'block\';
    } else {
        document.getElementById(\'dLoan\').style.display = \'none\';
    }
    if ( document.getElementById(\'MoveType\').value == \'5\' ) {
        document.getElementById(\'dIndustrySpaceX\').style.display = \'block\';
    } else {
        document.getElementById(\'dIndustrySpaceX\').style.display = \'none\';
    }
    if ( document.getElementById(\'MoveType\').value == \'6\' ) {
        document.getElementById(\'dPassSure\').style.display = \'block\';
    } else {
        document.getElementById(\'dPassSure\').style.display = \'none\';
    }
    switch ( document.getElementById(\'MoveType\').value ) {
        case \'0\':
        case \'1\':
            var k;
            if ( document.getElementById(\'MoveType\').value == \'1\' ) {
                k = 2*TileReqC[Number(document.getElementById(\'TileTypeY\').value)] + TileReqI[Number(document.getElementById(\'TileTypeY\').value)];
            } else {
                if ( HandSize > 1 ) {
                    if ( document.getElementById(\'FirstCard\').value == \'NoCardSelected\' ) {
                        k = 0;
                    } else {
                        k = 2*TileReqC[Number(document.getElementById(\'TileType\'+document.getElementById(\'FirstCard\').value).value)] + TileReqI[Number(document.getElementById(\'TileType\'+document.getElementById(\'FirstCard\').value).value)];
                    }
                } else {
                    k = 2*TileReqC[Number(document.getElementById(\'TileType0\').value)] + TileReqI[Number(document.getElementById(\'TileType0\').value)];
                }
            }
            switch (k) {
                case 0:
                    document.getElementById(\'dCoal\').style.display = \'none\';
                    document.getElementById(\'dIron\').style.display = \'none\';
                    document.getElementById(\'dMustSupplyC\').style.display = \'none\';
                break;
                case 1:
                    document.getElementById(\'dIron\').style.top = TierDDC;
                    document.getElementById(\'dIron\').style.display = \'block\';
                    document.getElementById(\'dCoal\').style.display = \'none\';
                    document.getElementById(\'dMustSupplyC\').style.display = \'none\';
                break;
                case 2:
                    document.getElementById(\'dCoal\').style.top = TierDDC;
                    document.getElementById(\'dCoal\').style.display = \'block\';
                    document.getElementById(\'dIron\').style.display = \'none\';
                    if ( DisplayCAD ) {
                        document.getElementById(\'dMustSupplyC\').style.top = TierDDD;
                        document.getElementById(\'dMustSupplyC\').style.display = \'block\';
                    } else {
                        document.getElementById(\'dMustSupplyC\').style.display = \'none\';
                    }
                break;
                case 3:
                    document.getElementById(\'dCoal\').style.top = TierDDC;
                    document.getElementById(\'dCoal\').style.display = \'block\';
                    document.getElementById(\'dIron\').style.top = TierDDD;
                    document.getElementById(\'dIron\').style.display = \'block\';
                    if ( DisplayCAD ) {
                        document.getElementById(\'dMustSupplyC\').style.top = TierDDE;
                        document.getElementById(\'dMustSupplyC\').style.display = \'block\';
                    } else {
                        document.getElementById(\'dMustSupplyC\').style.display = \'none\';
                    }
                break;
            }
        break;
        case \'2\':
            document.getElementById(\'dIron\').style.display = \'none\';
            if ( RailPhase ) {
                document.getElementById(\'dCoal\').style.top = TierDDB;
                document.getElementById(\'dCoal\').style.display = \'block\';
                if ( DisplayCAD ) {
                    document.getElementById(\'dMustSupplyC\').style.top = TierDDC;
                    document.getElementById(\'dMustSupplyC\').style.display = \'block\';
                } else {
                    document.getElementById(\'dMustSupplyC\').style.display = \'none\';
                }
            } else {
                document.getElementById(\'dCoal\').style.display = \'none\';
                document.getElementById(\'dMustSupplyC\').style.display = \'none\';
            }
        break;
        case \'3\':
            document.getElementById(\'dIron\').style.top = TierDDB;
            document.getElementById(\'dIron\').style.display = \'block\';
            document.getElementById(\'dCoal\').style.display = \'none\';
            document.getElementById(\'dMustSupplyC\').style.display = \'none\';
        break;
        default:
            document.getElementById(\'dCoal\').style.display = \'none\';
            document.getElementById(\'dIron\').style.display = \'none\';
            document.getElementById(\'dMustSupplyC\').style.display = \'none\';
        break;
    }
}
function SetupForm() { document.getElementById(\'dNoJavaScriptMsg\').style.display = \'none\'; document.getElementById(\'dCardX\').style.display = \'block\';';

/////////////////////////////
/////////////////////////////
/////////////////////////////

        if ( $HandSize[$PlayerToMove] > 2 and ( !$NoSwapCards or !$EvenTest ) ) { $MoveFormScript .= ' document.getElementById(\'dCardY\').style.display = \'block\';'; }
        $MoveFormScript .= ' document.getElementById(\'dMoveType\').style.display = \'block\';';
        if ( $MyColour != $PlayerToMove ) { $MoveFormScript .= ' document.getElementById(\'dAdminMove\').style.display = \'block\';'; }
        $MoveFormScript .= ' document.getElementById(\'dButtons\').style.display = \'block\';';
        if ( $HandSize[$PlayerToMove] < 3 ) { $MoveFormScript .= ' AlterForm();'; }
        else                                { $MoveFormScript .= ' document.getElementById(\'dSwapCards\').style.display = \'block\';'; }
        $MoveFormScript .= ' }';
    } else {
        $NormalMoveForm = 0;
        if ( $SecondRailMode ) {
            $Height = 230 + 80*$AlertDisplay;
            if ( $Administrator and $MyColour != $PlayerToMove ) { $Height += 50; }
            $MoveForm .= '<p><div style="position: relative; border: 1px solid black; padding: 15px; width: 650px; height: '.
                         $Height.
                         'px; background-color: #'.
                         $DivBGCol.
                         '">'.
                         str_replace( array('\ten'         , '\five'       ),
                                      array(moneyformat(10), moneyformat(5)),
                                      transtext('mi2ndRailLink')
                                      ).
                         '<p><select name="LinkToBuild">';
            for ($i=0;$i<$NumRailLinks;$i++) {
                if ( $LinkStatus[$i] == 9 and ( $RailAlwaysExists[$i] or ( $ModularBoardParts & $RailExistenceArray[$i] ) ) ) {
                    $MoveForm .= '<option value="'.
                                 $i.
                                 '">'.
                                 $locationnames[$RailStarts[$i]].
                                 ' - '.
                                 $locationnames[$RailEnds[$i]];
                }
            }
            $MoveForm .= '<option value="StopBuilding">'.
                         transtext('miStopBuilding').
                         '</select>';
            if ( $RemainingTiles[5][$PlayerToMove] == 1 ) {
                $MoveForm .= ' <b>Warning: You have only one rail marker left!</b>';
            }
            else if ( $RemainingTiles[5][$PlayerToMove] < 7 ) {
                $MoveForm .= ' <b>Warning: You have only '.$RemainingTiles[5][$PlayerToMove].' rail markers left!</b>';
            }

            $MoveForm .= '<p>'.
                         transtext('miCoalSource').
                         ' <select name="CoalSource">';
            $havenotprintedselected = 1;
            for ($i=0;$i<$NumIndustrySpaces;$i++) {
                if ( $SpaceStatus[$i] != 9 and $SpaceTile[$i] == 1 and $SpaceCubes[$i] ) {
                    if ( $havenotprintedselected and $SpaceStatus[$i] == $PlayerToMove ) {
                        $havenotprintedselected = 0;
                        $selectedtext = ' selected';
                    } else {
                        $selectedtext = '';
                    }
                    $MoveForm .= '<option value="'.
                                 $i.
                                 '"'.
                                 $selectedtext.
                                 '>'.
                                 $locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i].
                                 ' ('.
                                 $CubeSourceArray[$SpaceStatus[$i]].
                                 ')';
                }
            }
            $MoveForm .= '<option value="DemandTrack">'.
                         transtext('miDemandTrack').
                         '</select>';
            if ( $AlertDisplay ) {
                $MoveForm .= '<p><font color="#FF0000">'.
                             transtext('miCoalWarning'). // "Please note that you MUST select a legal source of coal" blah blah blah
                             '</font>'; }
            if ( $HandSize[$PlayerToMove] > 1 ) {
                $MoveForm .= '<p>'.
                             transtext('bpCardSwap1st').
                             ' <select name="FirstCard"><option value="NoCardSelected">'.
                             transtext('miListSelectOne');
                for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
                    $MoveForm .= '<option value="'.
                                 $i.
                                 '">'.
                                 DecipherCardSymbol($Cards[$PlayerToMove][$i]);
                }
                $MoveForm .= "</select>";
            }
        } else if ( $SecondDevelopMode ) {
            $Height = 230 + 80*$AlertDisplay;
            if ( $Administrator and $MyColour != $PlayerToMove ) { $Height += 50; }
            $MoveForm .= '<p><div style="position: relative; border: 1px solid black; padding: 15px; width: 650px; height: '.
                         $Height.
                         'px; background-color: #'.
                         $DivBGCol.
                         '">'.
                         transtext('mi2ndDevelop').
                         '<p><select name="TileType">';
            for ($i=0;$i<5;$i++) {
                if ( $RemainingTiles[$i][$PlayerToMove] ) {
                    $MoveForm .= '<option value="'.$i.'">'.$industrynames[$i];
                }
            }
            $MoveForm .= '<option value="StopDeveloping">'.
                         transtext('miStopDeveloping').
                         '</select><p>'.
                         transtext('miIronSource').
                         ' <select name="IronSource">';
            $ShowDT = 1;
            $havenotprintedselected = 1;
            for ($i=0;$i<$NumIndustrySpaces;$i++) {
                if ( $SpaceStatus[$i] != 9 and $SpaceTile[$i] == 2 and $SpaceCubes[$i] ) {
                    $ShowDT = 0;
                    if ( $havenotprintedselected and $SpaceStatus[$i] == $PlayerToMove ) {
                        $havenotprintedselected = 0;
                        $selectedtext = ' selected';
                    }
                    else { $selectedtext = ''; }
                    $MoveForm .= '<option value="'.
                                 $i.
                                 '"'.
                                 $selectedtext.
                                 '>'.
                                 $locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i].
                                 ' ('.
                                 $CubeSourceArray[$SpaceStatus[$i]].
                                 ')';
                }
            }
            if ( $ShowDT ) {
                $MoveForm .= '<option value="DemandTrack">'.
                             transtext('miDemandTrack');
            }
            $MoveForm .= '</select>';
            if ( $HandSize[$PlayerToMove] > 1 ) {
                $MoveForm .= '<p>'.
                             transtext('bpCardSwap1st').
                             ' <select name="FirstCard"><option value="NoCardSelected">'.
                             transtext('miListSelectOne');
                for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
                    $MoveForm .= '<option value="'.
                    $i.
                    '">'.
                    DecipherCardSymbol($Cards[$PlayerToMove][$i]);
                }
                $MoveForm .= '</select>';
            }
        } else if ( $ContinueSellingMode ) {
            $Height = 230 + 80*$AlertDisplay;
            if ( $Administrator and $MyColour != $PlayerToMove ) { $Height += 50; }
            $MoveForm .= '<p><div style="position: relative; border: 1px solid black; padding: 15px; width: 650px; height: '.
                         $Height.
                         'px; background-color: #'.
                         $DivBGCol.
                         '">'.
                         transtext('miContuSelling').
                         '<p><select name="IndustrySpace">';
            for ($i=0;$i<$NumIndustrySpaces;$i++) {
                if ( $SpaceStatus[$i] == $PlayerToMove and !$SpaceTile[$i] and $SpaceCubes[$i] ) {
                    $MoveForm .= '<option value="'.
                                 $i.
                                 '">'.
                                 $locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i];
                }
            }
            $MoveForm .= '<option value="StopSelling">'.
                         transtext('miStopSelling').
                         '</select><p>'.
                         transtext('miSCPortLocation').
                         ' <select name="PortSpace">';
            $havenotprintedselected = 1;
            for ($i=0;$i<$NumIndustrySpaces;$i++) {
                if ( $SpaceStatus[$i] != 9 and $SpaceTile[$i] == 3 and $SpaceCubes[$i] ) {
                    if ( $havenotprintedselected and $SpaceStatus[$i] == $PlayerToMove ) {
                        $havenotprintedselected = 0;
                        $selectedtext = ' selected';
                    }
                    else { $selectedtext = ''; }
                    $MoveForm .= '<option value="'.
                                 $i.
                                 '"'.
                                 $selectedtext.
                                 '>'.
                                 $locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i].
                                 ' ('.
                                 $CubeSourceArray[$SpaceStatus[$i]].
                                 ')';
                }
            }
            if ( $CottonDemand < 8 ) {
                $MoveForm .= '<option value="DistantMarket">'.
                             transtext('miDistantMarket');
            }
            $MoveForm .= '</select>';
            if ( $AlertDisplay ) {
                $MoveForm .= '<p><font color="#FF0000">'.
                             transtext('miSCWarning'). // "Please note that you MUST select a legal Port to sell from" blah blah blah
                             '</font>';
            }
            if ( $HandSize[$PlayerToMove] > 1 ) {
                $MoveForm .= '<p>'.
                             transtext('bpCardSwap1st').
                             ' <select name="FirstCard"><option value="NoCardSelected">'.
                             transtext('miListSelectOne');
                for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
                    $MoveForm .= '<option value="'.
                                 $i.
                                 '">'.
                                 DecipherCardSymbol($Cards[$PlayerToMove][$i]);
                }
                $MoveForm .= '</select>';
            }
        } else if ( $DebtMode ) {
            if ( $Administrator and $MyColour != $PlayerToMove ) { $Height = 220; }
            else                                                 { $Height = 170; }
            $MoveForm .= '<p><div style="position: relative; border: 1px solid black; padding: 15px; width: 650px; height: '.
                         $Height.
                         'px; background-color: #'.
                         $DivBGCol.
                         '"><font color="#FF0000">'.
                         transtext('miYouAreInDebt').
                         '</font><p>'.
                         transtext('miTileToSell').
                         ' <select name="IndustrySpace">';
            for ($i=0;$i<$NumIndustrySpaces;$i++) {
                if ( $SpaceStatus[$i] == $PlayerToMove ) { $MoveForm .= '<option value="'.$i.'">'.$locationnames[$spacetowns[$i]].$SpaceNumbersNotOrdinal[$i]; }
            }
            $MoveForm .= '</select><p>'.
                         transtext('bpCardSwap1st').
                         ' <select name="FirstCard"><option value="NoCardSelected">'.
                         transtext('miListSelectOne');
            for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
                $MoveForm .= '<option value="'.
                             $i.
                             '">'.
                             DecipherCardSymbol($Cards[$PlayerToMove][$i]);
            }
            $MoveForm .= '</select>';
        }
        if ( $HandSize[$PlayerToMove] == 1 ) {
            $MoveForm .= '<p>'.
                         transtext('bpOnlyOneCard').
                         ' <b>'.
                         DecipherCardSymbol($Cards[$PlayerToMove][0]).
                         '</b>.';
        } else if ( $HandSize[$PlayerToMove] > 1 ) {
            $MoveForm .= '<p>'.
                         transtext('bpCardSwap2nd').
                         ' <select name="SecondCard"><option value="NoCardSelected">'.
                         transtext('miListSelectOne');
            for ($i=0;$i<$HandSize[$PlayerToMove];$i++) {
                $MoveForm .= '<option value="'.
                             $i.
                             '">'.
                             DecipherCardSymbol($Cards[$PlayerToMove][$i]);
            }
            $MoveForm .= '</select>';
        } else {
            $MoveForm .= '<p>'.
                         transtext('gbYouHaveNoCards');
        }
        if ( $MyColour != $PlayerToMove ) {
            $MoveForm .= '<p><input type="checkbox" name="AdminMoveYes" value="1"> Yes, I do intend to make '.
                         $PlayerFullName[$PlayerToMove].
                         '\'s move for '.
                         $OtherPronounLC[$PlayerToMove].
                         '.';
        }
        $MoveForm .= '<p><input type="submit" name="FormSubmit" value="Submit Move" onSubmit="clearInterval(IntervalID);">';
        if ( $HandSize[$PlayerToMove] >= 2 ) {
            $SwapCardsDepth = $Height - 40;
            $MoveForm .= '<div id="dSwapCards" style="position: absolute; top: '.
                         $SwapCardsDepth.
                         'px; left: 235px; width: 400px; text-align: right; display: none">';
            if ( !$NoSwapCards ) {
                $MoveForm .= '<input type="submit" name="FormSubmit" value="Swap Cards">';
            }
            if ( $MyColour == $PlayerToMove ) {
                $MoveForm .= '<input type="submit" name="FormSubmit" value="Sort Cards">';
            }
            $MoveForm .= "</div>";
        }
    }
    $MoveForm .= '</div>';
} else {
    $AnyMoveForm = 0;
    $NormalMoveForm = 0;
}
$TheAdminThings = 'As an Administrator, you may kick any user from the game. Select user to kick, tick the box, and then click "Admin Kick".<p><select name="AdminKickList">';
for ($i=0;$i<5;$i++) {
    if ( $PlayerExists[$i] and !$PlayerMissing[$i] and $PlayerUserID[$i] != $_SESSION['MyUserID'] ) {
        $TheAdminThings .= '<option value="'.
                           $i.
                           '">'.
                           $PlayerFullName[$i];
    }
}
if ( $GTitleDeletedByAdmin ) { $unstring = 'un'; }
else                         { $unstring = '';   }
$TheAdminThings .= '</select> <input type="checkbox" name="CheckC" value="1"> Yes, I do intend to kick this user. <input type="submit" name="FormSubmit" value="Admin Kick"><p>As an Admin, you may Abort this game at will. To do so, tick the box, and then click "Admin Abort". <input type="checkbox" name="CheckD" value="1"> Yes, I do intend to abort this game. <input type="submit" name="FormSubmit" value="Admin Abort"><p>As an Administrator, you may '.
                   $unstring.
                   'clear the title of this game. To do so, click "Admin Retitle". <input type="submit" name="FormSubmit" value="Admin Retitle"><p>As an Administrator, you may extend the game\'s clock by changing the "Last Move" time.<br>Add to <select name="whattime"><option value="now">current time<option value="lmove">current value of "Last Move"</select> <input type="text" name="thenumber" size="4" maxlength="2"> <select name="theinterval"><option value="DAY">days<option value="HOUR">hours<option value="MINUTE">minutes</select> <input type="submit" name="FormSubmit" value="Extend Clock"><p>';
if ( $MyColour != 50 ) {
    $PlayerThings = 1;
    $ThePlayerStandardThings = '';
    $ThePlayerExtraThings = '';
    if ( $HandSize[$MyColour] > 1 and ( $MyColour != $PlayerToMove or $NoSwapCards ) ) {
        $ThePlayerExtraThings = transtext('bpCardSwap1st').
                                ' <select name="FirstCardNotMyTurn">';
        for ($i=0;$i<$HandSize[$MyColour];$i++) {
            $ThePlayerExtraThings .= '<option value="'.
                                     $i.
                                     '">'.
                                     DecipherCardSymbol($Cards[$MyColour][$i]);
        }
        $ThePlayerExtraThings .= '</select><br>'.
                                 transtext('bpCardSwap2nd').
                                 ' <select name="SecondCardNotMyTurn">';
        for ($i=0;$i<$HandSize[$MyColour];$i++) {
            $ThePlayerExtraThings .= '<option value="'.
                                     $i.
                                     '">'.
                                     DecipherCardSymbol($Cards[$MyColour][$i]);
        }
        $ThePlayerExtraThings .= '</select><br><input type="submit" name="FormSubmit" value="Swap These Cards"><br><input type="submit" name="FormSubmit" value="Sort Cards"><p>';
    } else if ( $HandSize[$MyColour] == 1 and $NoSwapCards ) {
        $ThePlayerExtraThings = transtext('bpOnlyOneCard').
                                ' '.
                                DecipherCardSymbol($Cards[$MyColour][0]).
                                '.<p>';
    }
    if ( !$RailPhase or $Round != $NumRounds or $HandSize[$MyColour] > 0 ) {
        $ThePlayerExtraThings .= transtext('bpMayQuit').
                                 '<br><input type="checkbox" name="CheckA" value="1">'.
                                 transtext('bpQuitSure1').
                                 '<br><input type="checkbox" name="CheckB" value="1">'.
                                 transtext('bpQuitSure2').
                                 '!<br><input type="submit" name="FormSubmit" value="Quit"><p>';
        $ThePlayerStandardThings = transtext('bpYourNotes').
                                   '<p><textarea cols="100" rows="8" name="GameNotes">'.
                                   $Notes[$MyColour].
                                   '</textarea><p>';
        if ( $GameStatus == 'In Progress' or $GameStatus == 'Recruiting Replacement' ) {
            $ThePlayerStandardThings .= '<input type="submit" name="FormSubmit" value="Save notes"><p>';
        }
    }
    if ( $AbortVoteActive ) {
        if ( $IHaveAbortVoted ) {
            $ThePlayerStandardThings .= transtext('bpStillWaitingA'). // "We are still waiting for some of your fellow players to vote on aborting"
                                        ' <input type="submit" name="FormSubmit" value="Vote On Aborting"><input type="hidden" name="votevalue" value="0"><p>';
        } else {
            $ThePlayerStandardThings .= transtext('bpVoteA'). // "One of your fellow players has initiated a vote to abort"
                                        ' <input type="checkbox" name="votevalue" value="1">'.
                                        transtext('bpAbort?'). // "YES, I am voting to abort the game. (Leave unticked to vote NO)"
                                        ' <input type="submit" name="FormSubmit" value="Vote On Aborting"><br>'.
                                        transtext('bpIfUnanimousA').
                                        '<p>';
        }
    } else {
        $ThePlayerStandardThings .= transtext('bpMayInitVoteA'). // "You may initiate a vote to abort the game" etc
                                    ' <input type="submit" name="FormSubmit" value="Vote To Abort"><br>'.
                                    transtext('bpIfUnanimousA').
                                    '<p>';
    }
    if ( $MyColour != $PlayerToMove and $GameStatus == 'In Progress' ) {
        if ( $KickVoteActive ) {
        if ( $IHaveKickVoted ) {
                $ThePlayerStandardThings .= '<i>'.
                                            str_replace( array('\shortplayername'        , '\fullplayername'             ),
                                                         array($PlayerName[$PlayerToMove], $PlayerFullName[$PlayerToMove]),
                                                         transtext('bpStillWaitingK')
                                                         ). // "We are still waiting for some of your fellow players to vote on whether to kick"
                                            '<input type="submit" name="FormSubmit" value="Vote On Kicking"><input type="hidden" name="votevalueA" value="0"><p>';
            } else {
                $ThePlayerStandardThings .= str_replace( array('\shortplayername'        , '\fullplayername'             , '\pronoun'                    ),
                                                         array($PlayerName[$PlayerToMove], $PlayerFullName[$PlayerToMove], $OtherPronounLC[$PlayerToMove]),
                                                         transtext('bpVoteK')
                                                         ). // "Because the current player is taking a long time to move, one of your fellow players" etc
                                            '<br><input type="checkbox" name="votevalueA" value="1"> '.
                                            str_replace( '\fullplayername',
                                                         $PlayerFullName[$PlayerToMove],
                                                         transtext('bpKick?')
                                                         ). // "YES, I am voting to kick [player name] from the game."
                                            ' <input type="submit" name="FormSubmit" value="Vote On Kicking"><br>'.
                                            str_replace( array('\shortplayername'        , '\pronoun'                         ),
                                                         array($PlayerName[$PlayerToMove], $PossessivePronounLC[$PlayerToMove]),
                                                         transtext('bpIfUnanimousK')
                                                         ).
                                            '<p>';
            }
        } else if ( $CanKickVote ) {
            $ThePlayerStandardThings .= str_replace( array('\playername','\pronoun'),
                                                     array($PlayerFullName[$PlayerToMove],$OtherPronounLC[$PlayerToMove]),
                                                     transtext('bpMayInitVoteK')
                                                     ). // "Because the current player is taking a long time to move, you may initiate" etc
                                        '<br><input type="submit" name="FormSubmit" value="Vote To Kick"><br>'.
                                        str_replace( array('\shortplayername'        , '\pronoun'                         ),
                                                     array($PlayerName[$PlayerToMove], $PossessivePronounLC[$PlayerToMove]),
                                                     transtext('bpIfUnanimousK')
                                                     ).
                                        '<p>';
        }
    }
} else {
    $PlayerThings = 0;
}
if ( $MyColour != 50 or ( $Administrator and $GameStatus == 'In Progress' ) ) {
    if ( $Administrator and $MyColour != $PlayerToMove and ( $GameStatus == 'In Progress' or $GameStatus == 'Recruiting Replacement' ) ) {
        $TilesToShow = $PlayerToMove;
        $BoxText = 'Highlight the current player\'s unflipped industry tiles';
        $LuckyText = 'The current player has no unflipped industry tiles on the board.';
        $UnluckyText = 'No unflipped tiles are present on the board other than those of the current player.';
    } else {
        $TilesToShow = $MyColour;
        $BoxText = transtext('bpArrowsYou');
        $LuckyText = transtext('bpNoUnflippedYou');
        $UnluckyText = transtext('bpNoUnflippedOpp');
    }
    $NumUnflipped = 0;
    $NumUnflippedX = 0;
    for ($i=0;$i<$NumIndustrySpaces;$i++) {
        if ( $SpaceStatus[$i] != 9 and $SpaceCubes[$i] ) {
            $XLocn = $TileXPositions[$CompactBoard][$i] + 5;
            $YLocn = $TileYPositions[$CompactBoard][$i] + 24;
            if ( $ColouredArrows ) {
                if ( $SpaceStatus[$i] == $MyColour and $ShowAsBlue ) { $colouroffset = -266; }
                else                                                 { $colouroffset = -38*$SpaceStatus[$i]; }
            } else {
                if ( $SpaceStatus[$i] == $TilesToShow ) { $colouroffset = -266; }
                else if ( $SpaceStatus[$i] == 8 )       { $colouroffset = -304; }
                else                                    { $colouroffset = -152; }
            }
            if ( $SpaceStatus[$i] == $TilesToShow ) {
                $ArrowInclusion[$i] = '<div id="Pointer'.
                                      $NumUnflipped.
                                      '" style="background: transparent url(gfx/arrows'.
                                      $BlinkRate.
                                      '.gif) '.
                                      $colouroffset.
                                      'px 0px no-repeat; position: absolute; top: '.
                                      $YLocn.
                                      'px; left: '.
                                      $XLocn.
                                      'px; z-index: 7; width: 38px; height: 42px; display: none"></div>';
                $NumUnflipped++;
            } else {
                $ArrowInclusion[$i] = '<div id="PointerX'.
                                      $NumUnflippedX.
                                      '" style="background: transparent url(gfx/arrows'.
                                      $BlinkRate.
                                      '.gif) '.
                                      $colouroffset.
                                      'px 0px no-repeat; position: absolute; top: '.
                                      $YLocn.
                                      'px; left: '.
                                      $XLocn.
                                      'px; z-index: 7; width: 38px; height: 42px; display: none"></div>';
                $NumUnflippedX++;
            }
        }
    }
    if ( $NumUnflipped and !$NumUnflippedX ) {
        $HTBox = '<p><input type="checkbox" name="HighlightTiles" onClick="ToggleShowTiles();"> '.
                 $BoxText.
                 ' <i>('.
                 $UnluckyText.
                 ')</i>';
        $HTScript = 'var DisplayTiles = 0; function ToggleShowTiles() { DisplayTiles = 1 - DisplayTiles; if ( DisplayTiles ) { for (i=0;i<'.
                    $NumUnflipped.
                    ';i++) { document.getElementById(\'Pointer\'+i).style.display = \'block\'; } } else { for (i=0;i<'.
                    $NumUnflipped.
                    ';i++) { document.getElementById(\'Pointer\'+i).style.display = \'none\'; } } }';
    } else if ( $NumUnflipped ) {
        $HTBox = '<p><input type="checkbox" id="HighlightTiles" name="HighlightTiles" value="1" onClick="DisplayTiles = 1 - DisplayTiles; ToggleShowTiles();"> '.
                 $BoxText.
                 ' <input type="checkbox" id="HighlightAllTiles" name="HighlightAllTiles" value="1" onClick="DisplayAllTiles = 1 - DisplayAllTiles; ToggleShowTiles();"> '.
                 transtext('bpArrowsAll');
        $HTScript = 'var DisplayTiles = 0; var DisplayAllTiles = 0; function ToggleShowTiles() { if ( DisplayTiles || DisplayAllTiles ) { for (i=0;i<'.
                    $NumUnflipped.
                    ';i++) { document.getElementById(\'Pointer\'+i).style.display = \'block\'; } } else { for (i=0;i<'.
                    $NumUnflipped.
                    ';i++) { document.getElementById(\'Pointer\'+i).style.display = \'none\'; } } if ( DisplayAllTiles ) { for (i=0;i<'.
                    $NumUnflippedX.
                    ';i++) { document.getElementById(\'PointerX\'+i).style.display = \'block\'; } } else { for (i=0;i<'.
                    $NumUnflippedX.
                    ';i++) { document.getElementById(\'PointerX\'+i).style.display = \'none\'; } } }';
    } else if ( $NumUnflippedX ) {
        $HTBox = '<p><input type="checkbox" name="HighlightAllTiles" onClick="ToggleShowTiles();"> '.
                 transtext('bpArrowsAll').
                 ' <i>('.
                 $LuckyText.
                 ')</i>';
        $HTScript = 'var DisplayTiles = 0; function ToggleShowTiles() { DisplayTiles = 1 - DisplayTiles; if ( DisplayTiles ) { for (i=0;i<'.
                    $NumUnflippedX.
                    ';i++) { document.getElementById(\'PointerX\'+i).style.display = \'block\'; } } else { for (i=0;i<'.
                    $NumUnflippedX.
                    ';i++) { document.getElementById(\'PointerX\'+i).style.display = \'none\'; } } }';
    } else {
        $HTBox = '';
        $HTScript = '';
    }
} else {
    $HTBox = '';
    $HTScript = '';
}

?>