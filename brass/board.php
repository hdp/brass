<?php
require('_std-include.php');

if ( !isset($_GET['GameID']) ) {
    echo '<html><head><title>'.
         transtext('gbBadURL'). // Missing Game ID
         '</title><body><p>'.
         transtext('gbBadURL'). // Missing Game ID
         '</p><p>'.
         transtext('^ClickHereMainPg'). // Click _here_ to return to the Main Page.
         '</p></body></html>';
    die('');
}

get_translation_module(3);

if ( isset($_GET['CompactBoard']) ) {
    if ( $_GET['CompactBoard'] == 1 ) { $CompactBoard = 1; }
    else                              { $CompactBoard = 0; }
}

/////////////////////////////
/////////////////////////////
/////////////////////////////

$cubeprice = array(1,1,2,2,3,3,4,4);
$playercolours = array('FFC18A','FFFFAF','9FFF9F','FFC6FF','C4C4C4');
$incomecellcodes = array(5,4,5,4,5,4,5,4,5,4,3,1,1,2,2,1,1,2,2,1,1,2,2,1,1,2,2,1,1,2,2,1,1,1,2,2,2,1,1,1,2,2,2,1,1,1,2,2,2,
                         1,1,1,2,2,2,1,1,1,2,2,2,1,1,1,1,2,2,2,2,1,1,1,1,2,2,2,2,1,1,1,1,2,2,2,2,1,1,1,1,2,2,2,2,1,1,1,1,2,2,2);
$tilegroups = array(4,4,4,4,3);
$tilegroupsizes = array( array(3,3,3,3),
                         array(2,2,2,1),
                         array(1,1,1,1),
                         array(2,2,2,2),
                         array(2,2,2)
                         );
$tilegroupminimums = array( array(0,3,6,9),
                            array(0,2,4,6),
                            array(0,1,2,3),
                            array(0,2,4,6),
                            array(0,2,4)
                            );
$dotXboosts = array( array(15,7,2,4),
                     array(23,15,26),
                     array(28,4),
                     array(26)
                     );
$dotYboosts = array( array(15,23,28,4),
                     array(7,15,4),
                     array(2,26),
                     array(26)
                     );
$divtechlevels = array( array(4,3,2,1),
                        array(4,3,2,1),
                        array(4,3,2,1),
                        array(4,3,2,1),
                        array(2,1,0)
                        );

$industrynames = array( transtext('_indCotnMill'),
                        transtext('_indCoalMine'),
                        transtext('_indIronWorks'),
                        transtext('_indPort'),
                        transtext('_indShipyard')
                        );
$pluralindustrynames = array( transtext('_indCotnMillPl'),
                              transtext('_indCoalMinePl'),
                              transtext('_indIronWorksPl'),
                              transtext('_indPortPl'),
                              transtext('_indShipyardPl')
                              );
$industrynames_indefarticle = array( transtext('_indCotnMillArt'),
                                     transtext('_indCoalMineArt'),
                                     transtext('_indIronWorksArt'),
                                     transtext('_indPortArt'),
                                     transtext('_indShipyardArt')
                                     );

/////////////////////////////
/////////////////////////////
/////////////////////////////

$incomeamountarray = array(10,9,8,7,6,5,4,3,2,1,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,
                           8,8,9,9,10,10,11,11,11,12,12,12,13,13,13,14,14,14,15,15,15,16,16,16,17,
                           17,17,18,18,18,19,19,19,20,20,20,21,21,21,21,22,22,22,22,23,23,23,23,24,24,
                           24,24,25,25,25,25,26,26,26,26,27,27,27,27,28,28,28,28,29,29,29,29,30,30,30
                           );

function moneyformat($x) {
    global $CurrencySymbol,$CurrencySymbolAfter;
    if ( $CurrencySymbolAfter ) {
        return $x.$CurrencySymbol;
    } else if ( $x < 0 ) {
        $x = -$x;
        return '-'.$CurrencySymbol.$x;
    } else {
        return $CurrencySymbol.$x;
    }
}

function incomeamounts($x) {
    global $incomeamountarray;
    if ( $x < 10 ) { return moneyformat(-$incomeamountarray[$x]); }
    else           { return moneyformat($incomeamountarray[$x]); }
}

function DecipherCardSymbol($x) {
    global $carddetailarray,$industrynames,$locationnames,$TopLocationCard;
    if ( $x > $TopLocationCard ) {
        return $industrynames[$carddetailarray[$x]];
    } else {
        return $locationnames[$carddetailarray[$x]];
    }
}

function playerstatusdiva($y,$p) {
    global $AllFlipVPs,$CanalProj,$divtechlevels,$EmptyStackEmptySet,$GameStatus,$GVersion,$IncomeSpace,$industrynames,$Money,$MyColour,$playercolours,$PlayerExists,$PlayerFullName,$PlayerMissing,$PlayerToMove,$PlayerUserID,$pluralindustrynames,$RailPhase,$RemainingTiles,$ShowAsBlue,$SnapEndVPs,$tilegroupminimums,$tilegroups,$tilegroupsizes,$VictoryPoints;
    if ( $MyColour == $p and $ShowAsBlue ) { $q = 7;  $DivBGCol = 'BFDFFF'; }
    else                                   { $q = $p; $DivBGCol = $playercolours[$p]; }
    $statusdivtileX = array( array(10,73,136,199),
                             array(10,73,136,199),
                             array(10,73,136,199),
                             array(10,73,136,199),
                             array(292,355,418)
                             );
    $statusdivtileY = array(50,113,176,239,50);
    $statusdivtileEX = array(10,10,10,10,292);
    echo '<div style="position: absolute; top: '.
         $y.
         'px; left: 461px; background-color: #'.
         $DivBGCol.
         '; width: 481px; height: 298px; border: 1px solid black"><div style="position: absolute; top: 9px; left: 0px; width: 481px; height: 30px; text-align: center"><b>'.
         transtext('gbBoxHeading').
         ' ';
    if ( $PlayerMissing[$p] or !$_SESSION['LoggedIn'] ) {
        echo $PlayerFullName[$p];
    } else {
        echo '<a href="userdetails.php?UserID='.$PlayerUserID[$p].'">'.$PlayerFullName[$p].'</a>';
    }
    echo ':</b></div>';
    for ($i=0;$i<5;$i++) {
        if ( $RemainingTiles[$i][$p] ) {
            for ($j=0;$j<$tilegroups[$i];$j++) {
                if ( $RemainingTiles[$i][$p] > $tilegroupminimums[$i][$j] ) {
                    if ( $RemainingTiles[$i][$p] - $tilegroupminimums[$i][$j] > $tilegroupsizes[$i][$j] ) { $tilesinstack = $tilegroupsizes[$i][$j]; }
                    else                                                                                  { $tilesinstack = $RemainingTiles[$i][$p] - $tilegroupminimums[$i][$j]; }
                    for ($k=0;$k<$tilesinstack;$k++) {
                        $tposnY = $statusdivtileY[$i] - 5*$k;
                        $tposnX = $statusdivtileX[$i][$j] + 5*$k;
                        $XAdjust = 47*$j-188;
                        if ( $i == 4 ) { $XAdjust += 94; }
                        $YAdjust = -47*$i;
                        echo '<div style="background: transparent url(gfx/t1'.
                             $q.
                             '.png) '.
                             $XAdjust.
                             'px '.
                             $YAdjust.
                             'px no-repeat; position: absolute; top: '.
                             $tposnY.
                             'px; left: '.
                             $tposnX.
                             'px; z-index: '.
                             $k.
                             '; width: 48px; height: 48px"></div>';
                    }
                    if ( $tilesinstack > 1 ) { $noun = $pluralindustrynames[$i]; }
                    else                     { $noun = $industrynames[$i]; }
                    echo '<a href="tilesbehind'.
                         $q.
                         '.htm" onClick="return popup(this,\'tilespopup\',482,218,\'no\')"><img src="gfx/trans'.
                         $tilesinstack.
                         '.png" alt="';
                    /*if ( $tilesinstack > 1 ) { // Commented out at present because I worry it will be a lot of work for the web server. Alt text to be made a preference setting, off by default.
                        echo str_replace( array('\playername'      , '\techlevel'          , '\industrytype'         ),
                                          array($PlayerFullName[$p], $divtechlevels[$i][$j], $pluralindustrynames[$i]),
                                          transtext('gbXTilesLeft')
                                          );
                    } else {
                        echo str_replace( array('\playername'      , '\techlevel'          , '\industrytype'   , '\number'    ),
                                          array($PlayerFullName[$p], $divtechlevels[$i][$j], $industrynames[$i], $tilesinstack),
                                          transtext('gb1TileLeft')
                                          );
                    }*/
                    echo '" border=\'0\' style="position: absolute; top: '.
                         $tposnY.
                         'px; left: '.
                         $statusdivtileX[$i][$j].
                         'px; z-index: 4"></a>';
                }
            }
        } else {
            if ( $EmptyStackEmptySet ) { echo '<img src="gfx/emp'.$q; }
            else                       { echo '<img src="gfx/s'.$i; }
            echo '.png" alt="'.
                 str_replace( array('\playername'      , '\industrytype'         ),
                              array($PlayerFullName[$p], $pluralindustrynames[$i]),
                              transtext('gbNoTilesLeft')
                              ).
                 '" style="position: absolute; top: '.$statusdivtileY[$i].'px; left: '.$statusdivtileEX[$i].'px">';
        }
    }
    if ( $RailPhase and $GameStatus != 'Finished' ) { $TableTop = 110; }
    else if ( $RailPhase )                          { $TableTop = 136; }
    else { $TableTop = 149; }
    echo '<table border=0 style="position: absolute; top: '.
         $TableTop.
         'px; left: 245px"><tr><td align=right width=190>'.
         transtext('gbBoxMoney').
         '</td><td width=40>'.
         moneyformat($Money[$p]).
         '</td></tr><tr><td align=right>'.
         transtext('gbBoxIncomeSpace').
         '</td><td>'.
         $IncomeSpace[$p].
         '</td></tr><tr><td align=right>'.
         transtext('gbBoxIncome/Turn').
         '</td><td>'.
         incomeamounts($IncomeSpace[$p]).
         '</td></tr><tr><td align=right>';
    if ( $RailPhase ) { echo transtext('gbBoxRails');  }
    else              { echo transtext('gbBoxCanals'); }
    echo '</td><td>'.
         $RemainingTiles[5][$p].
         '</td></tr>';
    if ( $RailPhase or $GameStatus == 'Finished' ) {
        echo '<tr><td align=right>'.
             transtext('gbBoxVPs').
             '</td><td>'.
             $VictoryPoints[$p].
             '</td></tr>';
    }
    if ( ( $RailPhase or $CanalProj ) and $GameStatus != 'Finished' ) {
        echo '<tr><td align=right>'.
             transtext('gbBoxProjNoFlip').
             '</td><td>'.
             $SnapEndVPs[$p].
             '</td></tr><tr><td align=right>'.
             transtext('gbBoxProjAllFlip').
             '</td><td>'.
             $AllFlipVPs[$p].
             '</td></tr>';
    }
    echo '</table></div>';
}

function playerstatusdivb($y,$p) {
    global $playercolours;
    echo '<div style="position: absolute; top: '.
         $y.
         'px; left: 461px; background-color: #'.
         $playercolours[$p].
         '; width: 481px; height: 40px; border: 1px solid black"><div style="position: absolute; top: 9px; left: 0px; width: 481px; height: 30px; text-align: center"><i>'.
         transtext('gbBoxNoPlayer').
         '</i></div></div>';
}

function turnorderbar($x,$y,$legendtoleft) {
    global $AmountSpent,$playercolours,$CurrentPlayers,$MyColour,$PlayerExists,$PlayerFullName,$ShowAsBlue,$TurnOrder;
    $rtnvar = '';
    $x -= 48*$CurrentPlayers;
    if ( $legendtoleft ) {
        $x -= 100;
    } else {
        $z = $y - 28;
        $width = 98*$CurrentPlayers + 1;
        if ( $CurrentPlayers == 1 ) {
            $x -= 48;
            $width += 96;
        }
        $rtnvar .= '<div style="position: absolute; top: '.
                   $z.
                   'px; left: '.
                   $x.
                   'px; width: '.
                   $width.
                   'px; text-align: center">'.
                   transtext('gbTO/AS').
                   '</div>';
        if ( $CurrentPlayers == 1 ) { $x += 48; }
    }
    $rtnvar .= '<table title="'.
               transtext('gbTO/AS').
               '" cellpadding=0 cellspacing=0 style="position: absolute; top: '.
               $y.
               'px; left: '.
               $x.
               'px; text-align: center; vertical-align: middle"><tr height=48>';
    if ( $legendtoleft ) { $rtnvar .= '<td>'.transtext('gbTO/AS').'&nbsp;&nbsp;&nbsp;</td>'; }
    $j = 1;
    for ($i=0;$i<5;$i++) {
        if ( $PlayerExists[$TurnOrder[$i]] ) {
            switch ( $i ) {
                case 0: $sayposn = transtext('^ordinal1st'); break;
                case 1: $sayposn = transtext('^ordinal2nd'); break;
                case 2: $sayposn = transtext('^ordinal3rd'); break;
                case 3: $sayposn = transtext('^ordinal4th'); break;
                case 4: $sayposn = '5th'; break;
            }
            if ( $MyColour == $TurnOrder[$i] and $ShowAsBlue ) {
                $colourcode = 'BFDFFF';
                $Whatcol = 7;
            } else {
                $colourcode = $playercolours[$TurnOrder[$i]];
                $Whatcol = $TurnOrder[$i];
            }
            $rtnvar .= '<td width=48 bgcolor="#'.$colourcode.'" ';
            if ( $j ) {
                $rtnvar .= 'style="border: 1px solid black';
                $j = 0;
            } else {
                $rtnvar .= 'class="allbutleft';
            }
            $rtnvar .= '"><img src="gfx/i'.
                       $Whatcol.
                       '.png" alt="'.
                       str_replace( array('\playername'                  , '\ordinal'),
                                    array($PlayerFullName[$TurnOrder[$i]], $sayposn  ),
                                    transtext('gbPlayerNthInTO')
                                    ).
                       '"></td><td width=48 align=center bgcolor="#'.
                       $colourcode.
                       '" class="allbutleft">'.
                       moneyformat($AmountSpent[$TurnOrder[$i]]).
                       '</td>';
        }
    }
    $rtnvar .= '</tr></table>';
    return $rtnvar;
}

function cottontrack($x,$y,$orienthorizontal) {
    global $CottonDemand;
    $labels = array(3,3,2,2,1,1,0,0);
    if ( $CottonDemand < 8 ) {
        if ( $CottonDemand % 2 ) {
            $dmdmsg = str_replace('\x',$labels[$CottonDemand],transtext('gbCottonAltText2'));
        } else {
            $dmdmsg = str_replace('\x',$labels[$CottonDemand],transtext('gbCottonAltText1'));
        }
    }
    $rtnvar = '<table title="'.
              transtext('gbCottonTrack')
              .'" cellpadding=0 cellspacing=0 style="position: absolute; top: '.$y.'px; left: '.$x.'px; vertical-align: middle">';
    if ( $orienthorizontal ) {
        $rtnvar .= '<tr height=48 style="background-color: #FFCB97; text-align: center">';
        for ($i=0;$i<8;$i++) {
            $rtnvar .= '<td width=48';
            if ( $i ) { $rtnvar .= ' class="allbutleft"'; }
            else      { $rtnvar .= ' style="border: 1px solid black"'; }
            if ( $CottonDemand == $i ) { $rtnvar .= '><img src="gfx/dmdisc.png" alt="'.$dmdmsg.$labels[$i].'."></td>'; }
            else                       { $rtnvar .= '>&nbsp;</td>'; }
        }
        $rtnvar .= '<td width=48 bgcolor="#FF5050" class="allbutleft">';
        if ( $CottonDemand == 8 ) { $rtnvar .= '<img src="gfx/dmdisc.png" alt="'.transtext('gbNMDLong').'">'; }
        else                      { $rtnvar .= '&nbsp;'; }
        $rtnvar .= '</td></tr><tr height=48 style="text-align: center"><td>+3</td><td>+3</td><td>+2</td><td>+2</td><td>+1</td><td>+1</td><td>+0</td><td>+0</td><td>'.
                   transtext('NMD').
                   '</td></tr>';
    } else {
        $leftcells = array('+3','+3','+2','+2','+1','+1','+0','+0');
        for ($i=0;$i<8;$i++) {
            $rtnvar .= '<tr height=48><td align=right>'.$leftcells[$i].'&nbsp;&nbsp;</td><td style="border';
            if ( $i ) { $rtnvar .= '-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black'; }
            else      { $rtnvar .= ': 1px solid black'; }
            $rtnvar .= '; background-color: #FFCB97; text-align: center">';
            if ( $CottonDemand == $i ) { $rtnvar .= '<img src="gfx/dmdisc.png" alt="'.$dmdmsg.$labels[$i].'.">'; }
            else                       { $rtnvar .= '&nbsp;'; }
            $rtnvar .= '</td></tr>';
        }
        $rtnvar .= '<tr height=48><td width=48 align=right>'.
                   transtext('NMD').
                   '&nbsp;&nbsp;</td><td width=48 style="border-left: 1pt solid black; border-right: 1px solid black; border-bottom: 1px solid black; background-color: #FF5050; text-align: center">';
        if ( $CottonDemand == 8 ) { $rtnvar .= '<img src="gfx/dmdisc.png" alt="'.transtext('gbNMDLong').'">'; }
        else                      { $rtnvar .= '&nbsp;'; }
        $rtnvar .= '</td></tr>';
    }
    $rtnvar .= '</table>';
    return $rtnvar;
}

function coalirontrack($x,$y,$displaylegends) {
    global $CoalDemand,$CurrentPlayers,$cubeprice,$IronDemand;
    if ( $CurrentPlayers < 3 ) { $TwoPlayers = 1; }
    else                       { $TwoPlayers = 0; }
    if ( $TwoPlayers and $displaylegends ) { $x += 48; }
    $rtnvar = '<table title="The coal and iron demand tracks" cellpadding=0 cellspacing=0 style="position: absolute; top: '.
              $y.
              'px; left: '.
              $x.
              'px; text-align: center; vertical-align: middle"><tr height=48>';
    if ( $displaylegends ) {
        $rtnvar .= '<td>'.
                   transtext('Coal').
                   ':&nbsp;&nbsp;&nbsp;</td>';
    }
    for ($i=0;$i<8-2*$TwoPlayers;$i++) {
        $rtnvar .= '<td width=48 bgcolor="#FFCB97" ';
        if ( $i ) { $rtnvar .= 'class="allbutleft'; }
        else      { $rtnvar .= 'style="border: 1px solid black'; }
        $rtnvar .= '">';
        if ( $CoalDemand <= $i ) {
            $rtnvar .= '<img src="gfx/coalcube.png" alt="'.
                       str_replace('\x',moneyformat($cubeprice[$i+2*$TwoPlayers]),transtext('gbCoalAltText')).
                       '">';
        } else {
            $rtnvar .= '&nbsp;';
        }
        $rtnvar .= '</td>';
    }
    $rtnvar .= '</tr><tr height=48>';
    if ( $displaylegends ) {
        $rtnvar .= '<td>'.
                   transtext('Iron').
                   ':&nbsp;&nbsp;&nbsp;</td>';
    }
    for ($i=0;$i<8-2*$TwoPlayers;$i++) {
        $rtnvar .= '<td width=48 bgcolor="#FFCB97" style="border';
        if ( $i ) { $rtnvar .= '-right: 1px solid black; border-bottom: 1px solid black'; }
        else      { $rtnvar .= '-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black'; }
        $rtnvar .= '">';
        if ( $IronDemand <= $i ) {
            $rtnvar .= '<img src="gfx/ironcube.png" alt="'.
                       str_replace('\x',moneyformat($cubeprice[$i+2*$TwoPlayers]),transtext('gbIronAltText')).
                       '">';
        } else {
            $rtnvar .= '&nbsp;';
        }
        $rtnvar .= '</td>';
    }
    $rtnvar .= '</tr><tr height=';
    if ( $displaylegends ) { $rtnvar .= '48><td></td>'; }
    else                   { $rtnvar .= '32 style="font-size: small">'; }
    if ( !$TwoPlayers ) { $rtnvar .= '<td>'.moneyformat(1).'</td><td>'.moneyformat(1).'</td>'; }
    $rtnvar .= '<td>'.
               moneyformat(2).
               '</td><td>'.
               moneyformat(2).
               '</td><td>'.
               moneyformat(3).
               '</td><td>'.
               moneyformat(3).
               '</td><td>'.
               moneyformat(4).
               '</td><td>'.
               moneyformat(4).
               '</td></tr></table>';
    return $rtnvar;
}

function cubecount($x,$y,$noicons) {
    global $CoalInLancs,$IronInLancs;
    if ( $noicons ) {
        return '<div style="position: absolute; top: '.
               $y.
               'px; left: '.
               $x.
               'px; width: 432px; height: 48px; text-align: center; vertical-align: middle">'.
               transtext('gbCoalOnBoard').
               ' <b>'.
               $CoalInLancs.
               '</b>. '.
               transtext('gbIronOnBoard').
               ' <b>'.
               $IronInLancs.
               '</b>. '.
               transtext('gbDemandTracks').
               ':</div>';
    } else {
        $a = $x + 26;
        $b = $x + 74;
        $c = $x + 100;
        $z = $y + 2;
        return '<img src="gfx/coalcube.png" alt="'.
               transtext('gbCoalOnBoard').
               ':" style="position: absolute; top: '.
               $y.
               'px; left: '.
               $x.
               'px"><div style="position: absolute; top: '.
               $z.
               'px; left: '.
               $a.
               'px; width: 40px; height: 27px; vertical-align: middle"><b>'.
               $CoalInLancs.
               '</b></div><img src="gfx/ironcube.png" alt="'.
               transtext('gbIronOnBoard').
               ':" style="position: absolute; top: '.
               $y.
               'px; left: '.
               $b.
               'px"><div style="position: absolute; top: '.
               $z.
               'px; left: '.
               $c.
               'px; width: 40px; height: 27px; vertical-align: middle"><b>'.
               $IronInLancs.
               '</b></div>';
    }
}

function cottondemandlegend($x,$y,$multiline) {
    global $CottonDemand,$GameIsFinished,$NumberOfTilesDrawn,$ShuffledTiles,$TilesDrawn;
    if ( $GameIsFinished ) { return ''; }
    if ( $NumberOfTilesDrawn ) {
        $TilesDrawnString = str_split($TilesDrawn);
        $TilesDrawnString = implode(', -',$TilesDrawnString);
        $TilesDrawnString = '-'.$TilesDrawnString;
    } else {
        $TilesDrawnString = transtext('_None');
    }
    $ShuffledTiles = str_split($ShuffledTiles,1);
    sort($ShuffledTiles);
    $TilesRemainingString = implode(', -',$ShuffledTiles);
    $TilesRemainingString = '-'.$TilesRemainingString;
    if ( $multiline ) {
        $rtnvar = '<div style="position: absolute; top: '.
                  $y.
                  'px; left: '.
                  $x.
                  'px; width: 432px; text-align: center">'.
                  transtext('gbCottonTrack').
                  '</div>';
        if ( $CottonDemand == 8 ) {
            $y += 40;
            $rtnvar .= '<div style="position: absolute; top: '.
                       $y.
                       'px; left: '.
                       $x.
                       'px; width: 432px; height: 96px; text-align: center; vertical-align: middle">'.
                       transtext('gbNMDShort').
                       '</div>';
        } else {
            $y += 26;
            $z = $y + 26;
            $rtnvar .= '<div style="position: absolute; top: '.
                       $y.
                       'px; left: '.
                       $x.
                       'px; width: 432px; text-align: center">'.
                       transtext('gbTilesDrawn').
                       ' '.
                       $TilesDrawnString.
                       '</div><div style="position: absolute; top: '.
                       $z.
                       'px; left: '.
                       $x.
                       'px; width: 432px; text-align: center">'.
                       transtext('gbRemainingTiles').
                       ' '.
                       $TilesRemainingString.
                       '</div>';
        }
        return $rtnvar;
    } else if ( $CottonDemand == 8 ) {
        return '';
    } else {
        $rtnvar = '<div style="position: absolute; top: '.
                  $y.
                  'px; left: '.
                  $x.
                  'px; height: 48px; width: 928px; text-align: center; vertical-align: middle">'.
                  transtext('gbTilesDrawn').
                  ' '.
                  $TilesDrawnString.
                  ', and remaining: '.
                  $TilesRemainingString.
                  '</div>';
        return $rtnvar;
    }
}

function pricelegend($x,$y) {
    return '<div style="position: absolute; top: '.
           $y.
           'px; left: '.
           $x.
           'px; width: 160px; border: 1px solid black"><center><table cellpadding=0 cellspacing=0 border=0><tr><td align=right><font size=2>Canal:&nbsp;</font></td><td><font size=2>'.moneyformat(3).'</font></td></tr><tr><td align=right><font size=2>Rail:&nbsp;</font></td><td><font size=2>'.
           moneyformat(5).
           ' + '.
           transtext('Coal').
           '</font></td></tr><tr><td align=right><font size=2>2 &times; Rail:&nbsp</font></td><td><font size=2>'.
           moneyformat(15).
           ' + 2 &times; '.
           transtext('Coal').
           '</font></td></tr></table></center></div>';
}

/////////////////////////////
/////////////////////////////
/////////////////////////////

$EscapedGameID = (int)$_GET['GameID'];
require(HIDDEN_FILES_PATH.'gamegetdata_board_see.php');
$GAME = gamegetdata_board_see($EscapedGameID);
if ( $GAME === false ) {
    echo '<html><head><title>Game doesn\'t exist</title><body><p>There is no game with that ID number. Click <a href="index.php">here</a> to return to the Main Page.</p></body></html>';
    die();
} else if ( $GAME == 'WRONG PAGE' ) {
    echo '<html><head><title>Game has not started</title><body><p>That game has not yet started. Click <a href="lobby.php?GameID='.$EscapedGameID.'">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.</p></body></html>';
    die();
}
extract($GAME); // To be removed
if ( is_null($TileXPositionsPretty) ) { $CompactBoard = 1; }
if ( $PreferredCurrencySymbol ) { $CurrencySymbol = $PreferredCurrencySymbol; }
$CurrencySymbolAfter = $CurrencySymbolAfterNumber[$CurrencySymbol];
$CurrencySymbol = $Currencies[$CurrencySymbol];
if ( $CurrencySymbolLocation == 1 )      { $CurrencySymbolAfter = false; }
else if ( $CurrencySymbolLocation == 2 ) { $CurrencySymbolAfter = true; }

if ( $GTitleDeletedByAdmin ) { $CensoredTitle = '('.transtext('_GameTitleHidden').'.)'; }
else                         { $CensoredTitle = $GameName; }
if ( $GameStatus != 'In Progress' and $GameStatus != 'Recruiting Replacement' ) { $CheckForMoves = 0; }
$ArrowInclusion = array('','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','');
if ( ( $Administrator or $MyColour != 50 ) and
     ( $GameStatus == 'In Progress' or $GameStatus == 'Recruiting Replacement' )
     ) {
    require(HIDDEN_FILES_PATH.'boardresource.php');
} else {
    $PlayControls = 0;
    $HTBox = '';
    $HTScript = '';
}

/////////////////////////////
/////////////////////////////
/////////////////////////////

if ( $AlwaysHideMsgs ) { $i = 1; }
else                   { $i = 0; }

echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>Brass - #'.$GameID.' '.$GameName_Title.'</title>
<style type="text/css">
<!--
.allbutleft {border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black}
div.modularbox {
    border: 1px solid black;
    min-width: 946px;
    padding: 0px 7px;
    margin: 10px 5px;
}

div.internalmodularbox {
    border: 1px solid black;
    min-width: 920px;
    padding: 0px 7px;
    margin: 10px 5px;
}

#loginbox p {
    margin-top: 9px;
    margin-bottom: 9px;
}

p.postheading {
    margin-top: 9px;
    margin-bottom: 9px;
    text-align: right;
    font-family: Helvetica,Tahoma,Verdana,Arial,sans-serif;
    font-size: 90%;
}

.mygame {
    background-color: #BFDFFF;
}

.mymove {
    background-color: #9FFF9F;
}

.myattn {
    background-color: #FFC18A;
}

.messagebox {
    background-color: #E7E7E7;
}
-->
</style><script type="text/javascript" src="formatmessage.js"></script><SCRIPT TYPE="text/javascript">
<!--
var DThreadHidden = '.$i.';
function popup(mylink, windowname, wd, ht, scr) {
    if (! window.focus) { return true; }
    var href;
    if ( typeof(mylink) == \'string\' ) { href=mylink; }
    else                              { href=mylink.href; }
    window.open(href, windowname, "width="+wd+",height="+ht+",scrollbars="+scr);
    return false;
}
function ToggleShowThread() {
    DThreadHidden = 1 - DThreadHidden;
    if ( DThreadHidden ) { document.getElementById(\'dThread\').style.display = \'none\'; }
    else                 { document.getElementById(\'dThread\').style.display = \'block\'; }
} '.$HTScript;
if ( $CheckForMoves ) {
    $TimeSinceLastMove = time() - strtotime($LastMove);
    if ( $MyColour != 50 or $Administrator ) {
        $interval_max_progress = 4;
        if      ( $TimeSinceLastMove <  600 ) { $interval_initial_progress = 0; }
        else if ( $TimeSinceLastMove < 1200 ) { $interval_initial_progress = 1; }
        else if ( $TimeSinceLastMove < 1800 ) { $interval_initial_progress = 2; }
        else if ( $TimeSinceLastMove < 3600 ) { $interval_initial_progress = 3; }
        else                                  { $interval_initial_progress = 4; }
    } else if ( @$_SESSION['LoggedIn'] ) {
        $interval_max_progress = 5;
        if      ( $TimeSinceLastMove < 1200 ) { $interval_initial_progress = 1; }
        else if ( $TimeSinceLastMove < 1800 ) { $interval_initial_progress = 2; }
        else if ( $TimeSinceLastMove < 3600 ) { $interval_initial_progress = 3; }
        else if ( $TimeSinceLastMove < 5400 ) { $interval_initial_progress = 4; }
        else                                  { $interval_initial_progress = 5; }
    } else {
        $interval_max_progress = 7;
        if      ( $TimeSinceLastMove < 1200 ) { $interval_initial_progress = 3; }
        else if ( $TimeSinceLastMove < 1800 ) { $interval_initial_progress = 4; }
        else if ( $TimeSinceLastMove < 3600 ) { $interval_initial_progress = 5; }
        else if ( $TimeSinceLastMove < 5400 ) { $interval_initial_progress = 6; }
        else                                  { $interval_initial_progress = 7; }
    }
    echo '
var IntervalID;
var num_requests = 0;
var interval_lengths = [5000,10000,15000,20000,30000,40000,50000,60000];
var requests_limits = [120,90,80,60,50,45,36,30];
var interval_length_progress = '.$interval_initial_progress.';
var interval_max_progress = '.$interval_max_progress.';
var current_interval_length = interval_lengths[interval_length_progress];
function ajaxFunction() {
    if ( interval_length_progress < interval_max_progress ) {
        num_requests++;
        if ( num_requests > requests_limits[interval_length_progress] ) {
            num_requests = 0;
            interval_length_progress++;
            current_interval_length = interval_lengths[interval_length_progress];
            clearInterval(IntervalID);
            IntervalID = setInterval(ajaxFunction,current_interval_length);
        }
    }
    try {
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            return false;
        }
    }
    xmlHttp.onreadystatechange = function() {
        if ( xmlHttp.readyState == 4 ) {
            var p = parseInt(xmlHttp.responseText);
            if ( !p ) { return false; }
            if ( p != '.$NumMovesMade.' ) {
                document.bgColor = "#BFDFFF";
                clearInterval(IntervalID);
            }
        }
    };
    xmlHttp.open("GET","nummovesmade/g'.$GameID.'.txt",true);
    xmlHttp.send(null);
}';
}
if ( $PlayControls and @$NormalMoveForm ) { echo $MoveFormScript; }
echo "\n//-->\n</SCRIPT></head><body onLoad=\"format_all_messages(false);";
if ( $CheckForMoves or ( $PlayControls and @$NormalMoveForm ) ) {
    if ( $PlayControls and @$NormalMoveForm ) {
        echo ' SetupForm();';
    }
    if ( $CheckForMoves ) {
        echo ' IntervalID = setInterval(ajaxFunction,current_interval_length);" onUnload="clearInterval(IntervalID);"';
    } else {
        echo '"';
    }
} else {
    echo '"';
}
echo '>';

EchoLoginForm(array( 'Location' => 1       ,
                     'GameID'   => $GameID
                     ));
echo $GameName_Page;

if ( $_SESSION['LoggedIn'] ) {
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   'SELECT "User" FROM "WatchedGame" WHERE "User" = :user: AND "Game" = :game:',
                   'user' , $_SESSION['MyUserID'] ,
                   'game' , $GameID
                   );
    if ( $QR === 'NONE' ) {
        echo ' - <a href="watch.php?Add=1&ReturnWhere=2&GameID='.$GameID.'">'.
             transtext('gbClickToWatch').
             '</a>';
    } else {
        echo ' - '.
             transtext('gbYouAreWatching').
             ' <a href="watch.php?ReturnWhere=2&GameID='.$GameID.'">'.
             transtext('gbStopWatching').
             '</a>';
    }
}
echo '<p>'.$VersionName.' ('.$Creators.')'.'<p>';
if ( $RailPhase ) {
    $PhaseName = 'Rail';
    $phasename = 'rail';
} else {
    $PhaseName = 'Canal';
    $phasename = 'canal';
}

/////////////////////////////
/////////////////////////////
/////////////////////////////

if ( $GameStatus != 'Finished' and ( $RailPhase or $CanalProj ) ) {
    $NumUnflippedTiles = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
    $NumFlippedTiles   = $LocationAutoValue;
    $AllFlipVPs = $VictoryPoints;
    $SnapEndVPs = $VictoryPoints;
    $TileVPValue = array(array(3,5,9,12),array(1,2,3,4),array(3,5,7,9),array(2,4,6,9),array(10,18));
    for ($i=0;$i<$NumIndustrySpaces;$i++) {
        if ( $SpaceStatus[$i] != 9 ) {
            if ( $SpaceStatus[$i] != 8 ) { $AllFlipVPs[$SpaceStatus[$i]] += $TileVPValue[$SpaceTile[$i]][$TechLevels[$i]-1]; }
            if ( $SpaceCubes[$i] ) {
                $NumUnflippedTiles[$spacetowns[$i]]++;
            } else {
                $NumFlippedTiles[$spacetowns[$i]]++;
                if ( $SpaceStatus[$i] != 8 ) { $SnapEndVPs[$SpaceStatus[$i]] += $TileVPValue[$SpaceTile[$i]][$TechLevels[$i]-1]; }
            }
        }
    }
    if ( $RailPhase ) {
        for ($i=0;$i<$NumRailLinks;$i++) {
            if ( $LinkStatus[$i] != 9 and $LinkStatus[$i] != 8 ) {
                $AllFlipVPs[$LinkStatus[$i]] += $NumFlippedTiles[$RailStarts[$i]] + $NumUnflippedTiles[$RailStarts[$i]] + $NumFlippedTiles[$RailEnds[$i]] + $NumUnflippedTiles[$RailEnds[$i]];
                $SnapEndVPs[$LinkStatus[$i]] += $NumFlippedTiles[$RailStarts[$i]] + $NumFlippedTiles[$RailEnds[$i]];
            }
        }
    } else {
        for ($i=0;$i<$NumCanalLinks;$i++) {
            if ( $LinkStatus[$i] != 9 and $LinkStatus[$i] != 8 ) {
                $AllFlipVPs[$LinkStatus[$i]] += $NumFlippedTiles[$CanalStarts[$i]] + $NumUnflippedTiles[$CanalStarts[$i]] + $NumFlippedTiles[$CanalEnds[$i]] + $NumUnflippedTiles[$CanalEnds[$i]];
                $SnapEndVPs[$LinkStatus[$i]] += $NumFlippedTiles[$CanalStarts[$i]] + $NumFlippedTiles[$CanalEnds[$i]];
            }
        }
    }
    for ($i=0;$i<5;$i++) {
        if ( $PlayerExists[$i] == 1 and $Money[$i] >= 0 ) {
            for ($j=0;$j<5;$j++) {
                if ( $TurnOrder[$j] == $i ) { $WhereMeInTurnOrder = $j; }
                if ( $TurnOrder[$j] == $PlayerToMove ) { $WhereWeInTurnOrder = $j; }
            }
            if ( $RailPhase and ( !$ProjIncludeMoney or ( $ProjIncludeMoney == 2 and $Round == $NumRounds and !$DebtMode and $WhereMeInTurnOrder < $WhereWeInTurnOrder ) ) ) {
                $MoneyRemainder = $Money[$i] % 10;
                $MoneyPoints = ($Money[$i] - $MoneyRemainder) / 10;
                $AllFlipVPs[$i] += $MoneyPoints;
                $SnapEndVPs[$i] += $MoneyPoints;
            }
        }
    }
} else {
    $AllFlipVPs = 0;
    $SnapEndVPs = 0;
}

/////////////////////////////
/////////////////////////////
/////////////////////////////

echo '<p>';
if ( $RailPhase ) { echo transtext('gbRailPhase');  }
else              { echo transtext('gbCanalPhase'); }
echo ' - '.
     str_replace(array('\round','\numrounds'),array($Round,$NumRounds),transtext('gbRoundXOfY'));
if ( $DebtMode ) {
    echo ' - '.transtext('gbDebtMode');
}
echo '. ';
if ( $GameStatus == 'Finished' ) {
    echo transtext('gbGameFinished');
} else if ( $GameStatus == 'Aborted' ) {
    echo transtext('gbGameAborted');
} else if ( $GameStatus == 'Recruiting Replacement' ) {
    echo transtext('gbRR');
} else if ( $_SESSION['LoggedIn'] ) {
    echo str_replace( '\playername',
                      '<a href="userdetails.php?UserID='.$PlayerUserID[$PlayerToMove].'">'.$PlayerFullName[$PlayerToMove].'</a>',
                      transtext('gbPlayerToMove')
                      );
} else {
    echo str_replace('\playername',$PlayerFullName[$PlayerToMove],transtext('gbPlayerToMove'));
}
if ( $NumberOfPosts ) { echo ' (<a href="#msg">Messages: '.$NumberOfPosts.'</a>)'; }
else                  { echo ' (Messages: 0)'; }
if ( $MyColour != 50 ) {
    if ( $HandSize[$MyColour] > 1 ) {
        echo '<p>'.
             transtext('bpYourCardsAre').
             ' ';
        for ($i=0;$i<$HandSize[$MyColour]-1;$i++) {
            echo DecipherCardSymbol($Cards[$MyColour][$i]).', ';
        }
        echo DecipherCardSymbol($Cards[$MyColour][$HandSize[$MyColour]-1]).'.';
    } else if ( $HandSize[$MyColour] ) {
        echo '<p>'.
             transtext('bpOnlyOneCard').
             ' <b>'.
             DecipherCardSymbol($Cards[$MyColour][0]).
             '</b>.';
    } else {
        echo '<p>'.
             transtext('gbYouHaveNoCards');
    }
}
echo $HTBox;

/////////////////////////////
/////////////////////////////
/////////////////////////////

if ( $ModularBoardParts & 6 ) { $MidlandsExists = 1; }
else                          { $MidlandsExists = 0; }
if ( $GVersion > 2 ) { $MidlandsExists = 1; }
if ( ( $ModularBoardParts & 8 ) == 8 ) { $ScotlandCanalExists = 1; }
else                                   { $ScotlandCanalExists = 0; }
$theHeightForTable = 830 - 206*$CompactBoard + (205 - 79*$CompactBoard)*$MidlandsExists;
if ( $GVersion > 2 ) { $theHeightForTable += 20; }

if ( $CompactBoard )    {
    $xcoaliron = 16;     $ycoaliron = $theHeightForTable + 48;
    $xccount = 16;       $yccount = $theHeightForTable + 12;
    $xcotton = 496;      $ycotton = $theHeightForTable + 96;
    $xcdlegend = 496;    $ycdlegend = $theHeightForTable + 12;
    $xturnorder = 590;   $yturnorder = $theHeightForTable + 192;
    $xpricelegend = 22;  $ypricelegend = $theHeightForTable + 190;
    $statbarstyle = 1;
} else if ( $MidlandsExists ) {
    $xcoaliron = 10;     $ycoaliron = 935;
    $xccount = 20;       $yccount = 895;
    $xcotton = 747;      $ycotton = 620;
    $xcdlegend = 16;     $ycdlegend = $theHeightForTable + 33;
    $xturnorder = 660;   $yturnorder = 126;
    $xpricelegend = 583; $ypricelegend = 23;
    $statbarstyle = 0;
} else {
    $xcoaliron = 16;     $ycoaliron = $theHeightForTable + 48;
    $xccount = 16;       $yccount = $theHeightForTable + 12;
    $xcotton = 496;      $ycotton = $theHeightForTable + 96;
    $xcdlegend = 496;    $ycdlegend = $theHeightForTable + 12;
    $xturnorder = 660;   $yturnorder = 126;
    $xpricelegend = 583; $ypricelegend = 23;
    $statbarstyle = 1;
}

if ( $CottonDemand == 8 ) { $regulator = $theHeightForTable + 220*$statbarstyle + 38; } 
else                      { $regulator = $theHeightForTable + 220*$statbarstyle - 30*$CompactBoard + 68; }
if ( !$CompactBoard and !$MidlandsExists ) { $regulator -= 96; }
$thesdivheight = 272 + 258*$CurrentPlayers;
if ( $thesdivheight < 663 + 143*$BSIncomeTrack ) { $thesdivheight = 663 + 143*$BSIncomeTrack; }
$theHeight = $regulator - 16 + $thesdivheight;

if ( $GVersion < 3 ) {

    $LocationConstants = array(
        array(0,0,173,168,173,143,77,16,21,84,0,312,48,325,48,336,454,661,349,640,349,640,756,
              508,700,551,676,565,772,142,676,8,628,119,724,119,628,153,724,281,964,503),
        array(0,0,34,48,48,22,0,0,48,22,0,324,48,214,48,240,224,768,96,576,96,576,559,
              768,576,550,550,576,672,161,672,0,624,22,703,48,576,96,576,205,672,551)
        );

    echo '<div style="position: relative; background-color: #FFFACA; width: 960px; height: '.
         $theHeight.
         'px"><div style="position: absolute; width: 200px; height: 50px; top: -12px; left: 760px; text-align: right; font-size: x-small"><a href="board.php?GameID='.
         $EscapedGameID.
         '&CompactBoard=';
    if ( $CompactBoard ) {
        echo '0">'.
             transtext('gbLkPrettyBoard');
    } else {
        echo '1">'.
             transtext('gbLkCompactBoard');
    }
    echo '</a></div><img src="gfx/boardcore'.
         $CompactBoard.
         '.png" alt="" style="position: absolute; top: '.
         $LocationConstants[$CompactBoard][0].
         'px; left: '.
         $LocationConstants[$CompactBoard][1].
         'px">';

    if ( !$RailPhase or $AlwaysShowCanals ) {
        echo '<img src="gfx/canalcore'.
             $CompactBoard.'.png" alt="" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][2].
             'px; left: '.
             $LocationConstants[$CompactBoard][3].
             'px">';
    }
    if ( $RailPhase or $AlwaysShowRails ) { 
        echo '<img src="gfx/railcore'.
             $CompactBoard.
             '.png" alt="" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][4].
             'px; left: '.
             $LocationConstants[$CompactBoard][5].
             'px">';
    }

    if ( $RailPhase or $ShowInaccessiblePlaces ) {
        echo '<img src="gfx/boardinac'.
             $CompactBoard.
             '.png" alt="" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][6].
             'px; left: '.
             $LocationConstants[$CompactBoard][7].
             'px">';
        if ( $RailPhase or $AlwaysShowRails ) {
            echo '<img src="gfx/railinac'.
                 $CompactBoard.
                 '.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][8].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][9].
                 'px">';
        }
    }

    if ( $RailPhase or $ScotlandCanalExists or $ShowInaccessiblePlaces ) {
        echo '<img src="gfx/boardscot.png" alt="" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][10].
             'px; left: '.
             $LocationConstants[$CompactBoard][11].
             'px">';
        if ( $RailPhase or $AlwaysShowRails ) {
            echo '<img src="gfx/railscot'.
                 $CompactBoard.
                 '.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][12].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][13].
                 'px">';
        }
        if ( ( !$RailPhase or $AlwaysShowCanals ) and $ScotlandCanalExists ) {
            echo '<img src="gfx/canalscot'.
                 $CompactBoard.
                 '.png" alt="'.
                 transtext('gbSCPresent').
                 '" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][14].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][15].
                 'px">';
        }
    }

    if ( ( $ModularBoardParts & 1 ) == 1 ) {
        echo '<img src="gfx/boardeast'.
             $CompactBoard.
             '.png" alt="'.
             transtext('gbNEPresent').
             '" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][16].
             'px; left: '.
             $LocationConstants[$CompactBoard][17].
             'px">';
        if ( !$RailPhase or $AlwaysShowCanals ) {
            echo '<img src="gfx/canaleast'.
                 $CompactBoard.
                 '.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][18].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][19].
                 'px">';
        }
        if ( $RailPhase or $AlwaysShowRails ) {
            echo '<img src="gfx/raileast'.
                 $CompactBoard.
                 '.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][20].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][21].
                 'px">';
        }
    }

    if ( ( $ModularBoardParts & 2 ) == 2 ) {
        echo '<img src="gfx/boardsouth'.
             $CompactBoard.
             '.png" alt="'.
             transtext('gbSEPresent').
             '" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][22].
             'px; left: '.
             $LocationConstants[$CompactBoard][23].
             'px">';
        if ( !$RailPhase or $AlwaysShowCanals ) {
            echo '<img src="gfx/canalsouth'.
                 $CompactBoard.
                 '.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][24]
                 .'px; left: '.
                 $LocationConstants[$CompactBoard][25]
                 .'px">';
        }
        if ( $RailPhase or $AlwaysShowRails ) {
            echo '<img src="gfx/railsouth'.
                 $CompactBoard.
                 '.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][26]
                 .'px; left: '.
                 $LocationConstants[$CompactBoard][27]
                 .'px">';
        }
    }

    if ( ( $ModularBoardParts & 4 ) == 4 ) {
        echo '<img src="gfx/boardwest'.
             $CompactBoard.
             '.png" alt="'.
             transtext('gbSWPresent').
             '" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][28].
             'px; left: '.
             $LocationConstants[$CompactBoard][29].
             'px">';
        if ( $RailPhase or $ShowInaccessiblePlaces ) {
            echo '<img src="gfx/boardbirk'.
                 $CompactBoard.
                 '.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][30].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][31].
                 'px">';
            if ( $ShowVirtualConnection ) {
                echo '<img src="gfx/vc.png" alt="There is a &quot;Virtual Connection&quot; between Birkenhead and Liverpool." style="position: absolute; top: '.
                     $LocationConstants[$CompactBoard][32].
                     'px; left: '.
                     $LocationConstants[$CompactBoard][33].
                     'px">'; }
            if ( $RailPhase or $AlwaysShowRails ) {
                echo '<img src="gfx/railbirk'.
                     $CompactBoard.
                     '.png" alt="" style="position: absolute; top: '.
                     $LocationConstants[$CompactBoard][34].
                     'px; left: '.
                     $LocationConstants[$CompactBoard][35].
                     'px">';
            }
        }
        if ( !$RailPhase or $AlwaysShowCanals ) {
            echo '<img src="gfx/canalwest'.
                 $CompactBoard.'.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][36].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][37].
                 'px">';
        }
        if ( $RailPhase or $AlwaysShowRails ) {
            echo '<img src="gfx/railwest'.
                 $CompactBoard.'.png" alt="" style="position: absolute; top: '.
                 $LocationConstants[$CompactBoard][38].
                 'px; left: '.
                 $LocationConstants[$CompactBoard][39].
                 'px">';
        }
    }

    if ( $MidlandsExists ) {
        echo '<img src="gfx/boardmidl.png" alt="'.
             transtext('gbMidsPresent').
             '" style="position: absolute; top: '.
             $LocationConstants[$CompactBoard][40].
             'px; left: '.
             $LocationConstants[$CompactBoard][41].
             'px">';
    }

} else if ( $GVersion == 3 ) {

    echo '<div style="position: relative; background-color: #FFFACA; width: 960px; height: '.
        $theHeight.
        'px"><img src="gfx/nfr_boardcore.png" alt="" style="position: absolute; top: 0px; left: 0px">';

    if ( $RailPhase or $ShowInaccessiblePlaces ) {
        echo '<img src="gfx/nfr_boardinac.png" alt="" style="position: absolute; top: 0px; left: 0px">';
        if ( $RailPhase or $AlwaysShowRails ) {
            echo '<img src="gfx/nfr_railinac.png" alt="" style="position: absolute; top: 32px; left: 13px">';
        }
    }

    if ( $RailPhase or $AlwaysShowRails ) {
        echo '<img src="gfx/nfr_railcore.png" alt="" style="position: absolute; top: 32px; left: 22px">';
    }

    if ( !$RailPhase or $AlwaysShowCanals ) {
        echo '<img src="gfx/nfr_canalall.png" alt="" style="position: absolute; top: 12px; left: 96px">';
    }

} else {

    echo '<div style="position: relative; background-color: #FFFACA; width: 960px; height: '.
        $theHeight.
        'px"><img src="gfx/nfr2_boardcore.png" alt="" style="position: absolute; top: 0px; left: 0px">';

    if ( $RailPhase or $ShowInaccessiblePlaces ) {
        echo '<img src="gfx/nfr2_boardinac.png" alt="" style="position: absolute; top: 0px; left: 0px">';
        if ( $RailPhase or $AlwaysShowRails ) {
            echo '<img src="gfx/nfr2_railinac.png" alt="" style="position: absolute; top: 32px; left: 22px">';
        }
    }

    if ( $RailPhase or $AlwaysShowRails ) {
        echo '<img src="gfx/nfr_railcore.png" alt="" style="position: absolute; top: 32px; left: 22px">';
    }

    if ( !$RailPhase or $AlwaysShowCanals ) {
        echo '<img src="gfx/nfr_canalall.png" alt="" style="position: absolute; top: 12px; left: 96px">';
    }

}

/////////////////////////////
/////////////////////////////
/////////////////////////////

if ( $RailPhase ) { $NumLinks = $NumRailLinks; }
else              { $NumLinks = $NumCanalLinks; }
for ($i=0;$i<$NumLinks;$i++) {
    if ( $LinkStatus[$i] != 9 ) {
        if ( $MyColour == $LinkStatus[$i] and $ShowAsBlue ) { $Whatcol = 7; }
        else                                                { $Whatcol = $LinkStatus[$i]; }
        echo '<img src="gfx/i'.
             $Whatcol.
             '.png" alt="';
     // if ( $RailPhase ) {
     //     echo str_replace( array('\start'                       , '\end'                       , '\player'                       ),
     //                       array($locationnames[$RailStarts[$i]], $locationnames[$RailEnds[$i]], $PlayerFullName[$LinkStatus[$i]]),
     //                       transtext('gbRailAltText')
     //                       );
     // } else {
     //     echo str_replace( array('\start'                       , '\end'                       , '\player'                       ),
     //                       array($locationnames[$RailStarts[$i]], $locationnames[$RailEnds[$i]], $PlayerFullName[$LinkStatus[$i]]),
     //                       transtext('gbCanalAltText')
     //                       );
     // }
     //     Commented out at present because I worry it will be a lot of work for
     //     the web server. Alt text to be made a preference setting, off by default.
        echo '" style="position: absolute; top: '.
             $LinkDotYPositions[$CompactBoard][$i].
             'px; left: '.
             $LinkDotXPositions[$CompactBoard][$i].
             'px; z-index: 1">';
    }
}

$PlayerFullName[8] = 'orphan';

for ($i=0;$i<$NumIndustrySpaces;$i++) {
    if ( $SpaceStatus[$i] != 9 ) {
        if ( $MyColour == $SpaceStatus[$i] and $ShowAsBlue ) { $Whatcol = 7; }
        else                                                 { $Whatcol = $SpaceStatus[$i]; }
        if ( $SpaceCubes[$i] ) { $SpaceFlipped[$i] = 0; }
        else                   { $SpaceFlipped[$i] = 1; }
        $XAdjust = -47*$TechLevels[$i] - 188*$SpaceFlipped[$i];
        $YAdjust = -47*$SpaceTile[$i];
        echo '<div style="background: transparent url(gfx/t1'.
             $Whatcol.
             '.png) '.
             $XAdjust.
             'px '.
             $YAdjust.
             'px no-repeat; position: absolute; top: '.
             $TileYPositions[$CompactBoard][$i].
             'px; left: '.
             $TileXPositions[$CompactBoard][$i].
             'px; z-index: 1; width: 48px; height: 48px"></div>';
        if ( $SpaceCubes[$i] and ( $SpaceTile[$i] == 1 or $SpaceTile[$i] == 2 ) ) {
            if ( $SpaceTile[$i] == 1 ) { $cubetype = 'coal'; }
            else                       { $cubetype = 'iron'; }
            $CubePlcX = array($TileXPositions[$CompactBoard][$i]+14,$TileXPositions[$CompactBoard][$i]+23,
                              $TileXPositions[$CompactBoard][$i]+ 7,$TileXPositions[$CompactBoard][$i]+11,
                              $TileXPositions[$CompactBoard][$i]+23,$TileXPositions[$CompactBoard][$i]+ 7,
                              $TileXPositions[$CompactBoard][$i]+19,$TileXPositions[$CompactBoard][$i]+ 3,
                              $TileXPositions[$CompactBoard][$i]+26,$TileXPositions[$CompactBoard][$i]+10,
                              $TileXPositions[$CompactBoard][$i]+19,$TileXPositions[$CompactBoard][$i]+ 3,
                              $TileXPositions[$CompactBoard][$i]+26,$TileXPositions[$CompactBoard][$i]+10,
                              $TileXPositions[$CompactBoard][$i]+13,$TileXPositions[$CompactBoard][$i]+10
                              );
            $CubePlcY = array($TileYPositions[$CompactBoard][$i]+12,$TileYPositions[$CompactBoard][$i]+11,
                              $TileYPositions[$CompactBoard][$i]+15,$TileYPositions[$CompactBoard][$i]+ 4,
                              $TileYPositions[$CompactBoard][$i]+15,$TileYPositions[$CompactBoard][$i]+19,
                              $TileYPositions[$CompactBoard][$i]+ 4,$TileYPositions[$CompactBoard][$i]+ 8,
                              $TileYPositions[$CompactBoard][$i]+15,$TileYPositions[$CompactBoard][$i]+19,
                              $TileYPositions[$CompactBoard][$i]+ 6,$TileYPositions[$CompactBoard][$i]+10,
                              $TileYPositions[$CompactBoard][$i]+17,$TileYPositions[$CompactBoard][$i]+21,
                              $TileYPositions[$CompactBoard][$i]+ 3,$TileYPositions[$CompactBoard][$i]+ 7
                              );
            switch ( $SpaceCubes[$i] ) {
                case 1:
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[0].'px; left: '.$CubePlcX[0].'px; z-index: 2">';
                break;
                case 2:
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[1].'px; left: '.$CubePlcX[1].'px; z-index: 2">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[2].'px; left: '.$CubePlcX[2].'px; z-index: 3">';
                break;
                case 3:
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[3].'px; left: '.$CubePlcX[3].'px; z-index: 2">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[4].'px; left: '.$CubePlcX[4].'px; z-index: 3">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[5].'px; left: '.$CubePlcX[5].'px; z-index: 4">';
                break;
                case 4:
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[6].'px; left: '.$CubePlcX[6].'px; z-index: 2">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[7].'px; left: '.$CubePlcX[7].'px; z-index: 3">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[8].'px; left: '.$CubePlcX[8].'px; z-index: 4">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[9].'px; left: '.$CubePlcX[9].'px; z-index: 5">';
                break;
                case 5:
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[10].'px; left: '.$CubePlcX[10].'px; z-index: 2">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[11].'px; left: '.$CubePlcX[11].'px; z-index: 3">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[12].'px; left: '.$CubePlcX[12].'px; z-index: 4">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[13].'px; left: '.$CubePlcX[13].'px; z-index: 5">';
                    echo '<img src="gfx/'.$cubetype.'cube.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[14].'px; left: '.$CubePlcX[14].'px; z-index: 6">';
                break;
                case 6:
                    echo '<img src="gfx/ironcube6.png" alt="" style="position: absolute; top: '.
                         $CubePlcY[15].'px; left: '.$CubePlcX[15].'px; z-index: 2">';
                break;
            }
        }
        echo $ArrowInclusion[$i];
        echo '<a href="tilesbehind'.
             $Whatcol.
             '.htm" onClick="return popup(this,\'tilespopup\',482,218,\'no\')"><img src="gfx/trans1.png" border=\'0\' alt="';
             /*if ( $SpaceCubes[$i] ) { // Commented out at present because I worry it will be a lot of work for the web server. Alt text to be made a preference setting, off by default.
                  echo str_replace(array('\spacenumber','\location','\colour','\techlevel','\industry','\industry_with_indef_article'),array($spacenumbers[$i],$locationnames[$spacetowns[$i]],$PlayerFullName[$SpaceStatus[$i]],$TechLevels[$i],$industrynames[$SpaceTile[$i]],$industrynames_indefarticle[$SpaceTile[$i]]),transtext('gbSpaceAltTextUF'));
              } else {
                  echo str_replace(array('\spacenumber','\location','\colour','\techlevel','\industry','\industry_with_indef_article'),array($spacenumbers[$i],$locationnames[$spacetowns[$i]],$PlayerFullName[$SpaceStatus[$i]],$TechLevels[$i],$industrynames[$SpaceTile[$i]],$industrynames_indefarticle[$SpaceTile[$i]]),transtext('gbSpaceAltTextF'));
              }
              if ( $SpaceStatus[$i] == 1 or $SpaceStatus[$i] == 2 ) {
                  if ( $SpaceCubes[$i] == 1 ) {
                      echo transtext('gb1CubeLeft');
                  } else if ( $SpaceCubes[$i] ) {
                      echo str_replace('\x',$SpaceCubes[$i],transtext('gbXCubesLeft'));
                  }
              }*/
        echo '" style="position: absolute; top: '.
             $TileYPositions[$CompactBoard][$i].
             'px; left: '.
             $TileXPositions[$CompactBoard][$i].
             'px; z-index: 8"></a>';
    }
}

/////////////////////////////
/////////////////////////////
/////////////////////////////

echo coalirontrack($xcoaliron,$ycoaliron,$statbarstyle).cottontrack($xcotton,$ycotton,$statbarstyle).cottondemandlegend($xcdlegend,$ycdlegend,$statbarstyle).cubecount($xccount,$yccount,$statbarstyle).turnorderbar($xturnorder,$yturnorder,$CompactBoard).pricelegend($xpricelegend,$ypricelegend);
$donealreadya = 0;
$donealreadyb = 0;
if ( $MyColour != 50 ) { playerstatusdiva($regulator,$MyColour); $donealreadya = 1; }
for ($i=0;$i<5;$i++) { if ( $PlayerExists[$i] and $MyColour != $i ) { playerstatusdiva($regulator+308*$donealreadya,$i); $donealreadya++; } }
for ($i=0;$i<5;$i++) { if ( !$PlayerExists[$i] ) { playerstatusdivb($regulator+308*$donealreadya+50*$donealreadyb,$i); $donealreadyb++; } }

if ( $BSIncomeTrack ) {
    echo '<table title="'.
         transtext('gbIncomeTrack').
         '" cellpadding=0 cellspacing=0 border=0 style="position: absolute; top: '.
         $regulator.
         'px; left: 16px; vertical-align: middle; text-align: center; font-size: small">
      <tr><td height=48 width=46 bgcolor="#02B4C8" style="border: 1px solid black"><b>91</b><br>'.moneyformat(28).'</td>
          <td width=46 bgcolor="#02B4C8" class="allbutleft"><b>92</b><br>'.moneyformat(28).'</td>
          <td width=46 bgcolor="#03E3FC" class="allbutleft"><b>93</b><br>'.moneyformat(29).'</td>
          <td width=46 bgcolor="#03E3FC" class="allbutleft"><b>94</b><br>'.moneyformat(29).'</td>
          <td width=46 bgcolor="#03E3FC" class="allbutleft"><b>95</b><br>'.moneyformat(29).'</td>
          <td width=46 bgcolor="#03E3FC" class="allbutleft"><b>96</b><br>'.moneyformat(29).'</td>
          <td width=46 bgcolor="#02B4C8" class="allbutleft"><b>97</b><br>'.moneyformat(30).'</td>
          <td width=46 bgcolor="#02B4C8" class="allbutleft"><b>98</b><br>'.moneyformat(30).'</td>
          <td width=46 bgcolor="#02B4C8" class="allbutleft"><b>99</b><br>'.moneyformat(30).'</td>
          </tr><tr><td style="border-left: 1px solid black; border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>90</b><br>'.moneyformat(28).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>89</b><br>'.moneyformat(28).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>88</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>87</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>86</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>85</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>84</b><br>'.moneyformat(26).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>83</b><br>'.moneyformat(26).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>82</b><br>'.moneyformat(26).'</td>
          </tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td style="border-right: 1px solid black">&nbsp;</td><td style="border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>73</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>74</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>75</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>76</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>77</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>78</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>79</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>80</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>81</b><br>'.moneyformat(26).'</td>
          </tr><tr><td style="border-left: 1px solid black; border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>72</b><br>'.moneyformat(23).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>71</b><br>'.moneyformat(23).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>70</b><br>'.moneyformat(23).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>69</b><br>'.moneyformat(23).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>68</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>67</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>66</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>65</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>64</b><br>'.moneyformat(21).'</td>
          </tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td style="border-right: 1px solid black">&nbsp;</td><td style="border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>55</b><br>'.moneyformat(19).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>56</b><br>'.moneyformat(19).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>57</b><br>'.moneyformat(19).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>58</b><br>'.moneyformat(20).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>59</b><br>'.moneyformat(20).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>60</b><br>'.moneyformat(20).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>61</b><br>'.moneyformat(21).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>62</b><br>'.moneyformat(21).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>63</b><br>'.moneyformat(21).'</td>
          </tr><tr><td style="border-left: 1px solid black; border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>54</b><br>'.moneyformat(18).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>53</b><br>'.moneyformat(18).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>52</b><br>'.moneyformat(18).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>51</b><br>'.moneyformat(17).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>50</b><br>'.moneyformat(17).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>49</b><br>'.moneyformat(17).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>48</b><br>'.moneyformat(16).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>47</b><br>'.moneyformat(16).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>46</b><br>'.moneyformat(16).'</td>
          </tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td style="border-right: 1px solid black">&nbsp;</td><td style="border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>37</b><br>'.moneyformat(13).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>38</b><br>'.moneyformat(13).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>39</b><br>'.moneyformat(13).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>40</b><br>'.moneyformat(14).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>41</b><br>'.moneyformat(14).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>42</b><br>'.moneyformat(14).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>43</b><br>'.moneyformat(15).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>44</b><br>'.moneyformat(15).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>45</b><br>'.moneyformat(15).'</td>
          </tr><tr><td style="border-left: 1px solid black; border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>36</b><br>'.moneyformat(12).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>35</b><br>'.moneyformat(12).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>34</b><br>'.moneyformat(12).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>33</b><br>'.moneyformat(11).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>32</b><br>'.moneyformat(11).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>31</b><br>'.moneyformat(11).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>30</b><br>'.moneyformat(10).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>29</b><br>'.moneyformat(10).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>28</b><br>'.moneyformat(9).'</td>
          </tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td style="border-right: 1px solid black">&nbsp;</td><td style="border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>19</b><br>'.moneyformat(5).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>20</b><br>'.moneyformat(5).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>21</b><br>'.moneyformat(6).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>22</b><br>'.moneyformat(6).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>23</b><br>'.moneyformat(7).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>24</b><br>'.moneyformat(7).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>25</b><br>'.moneyformat(8).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>26</b><br>'.moneyformat(8).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>27</b><br>'.moneyformat(9).'</td>
          </tr><tr><td style="border-left: 1px solid black; border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>18</b><br>'.moneyformat(4).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>17</b><br>'.moneyformat(4).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>16</b><br>'.moneyformat(3).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>15</b><br>'.moneyformat(3).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>14</b><br>'.moneyformat(2).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>13</b><br>'.moneyformat(2).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>12</b><br>'.moneyformat(1).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>11</b><br>'.moneyformat(1).'</td>
          <td bgcolor="#C0C0C0" class="allbutleft"><b>10</b><br>'.moneyformat(0).'</td>
          </tr><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td style="border-right: 1px solid black">&nbsp;</td><td style="border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td></tr>
      <tr><td height=48 bgcolor="#FF9D6F" style="border: 1px solid black"><b>1</b><br>'.moneyformat(-9).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>2</b><br>'.moneyformat(-8).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>3</b><br>'.moneyformat(-7).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>4</b><br>'.moneyformat(-6).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>5</b><br>'.moneyformat(-5).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>6</b><br>'.moneyformat(-4).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>7</b><br>'.moneyformat(-3).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>8</b><br>'.moneyformat(-2).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>9</b><br>'.moneyformat(-1).'</td>
          </tr><tr><td style="border-left: 1px solid black; border-right: 1px solid black"><img src="gfx/chevron.png" alt=""></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#FFFF80" style="border: 1px solid black"><b>0</b><br>'.moneyformat(-10).'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></table>';
} else {
    echo '<table title="'.
         transtext('gbIncomeTrack').
         '" cellpadding=0 cellspacing=0 border=0 style="position: absolute; top: '.
         $regulator.
         'px; left: 16px; vertical-align: middle; text-align: center; font-size: small">
      <tr><td height=48 width=46 bgcolor="#FFFF80" style="border: 1px solid black"><b>0</b><br>'.moneyformat(-10).'</td><td width=46></td><td width=46></td><td width=46></td><td width=46></td><td width=46></td><td width=46></td><td width=46></td><td width=46></td></tr>
      <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#FF9D6F" style="border: 1px solid black"><b>1</b><br>'.moneyformat(-9).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>2</b><br>'.moneyformat(-8).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>3</b><br>'.moneyformat(-7).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>4</b><br>'.moneyformat(-6).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>5</b><br>'.moneyformat(-5).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>6</b><br>'.moneyformat(-4).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>7</b><br>'.moneyformat(-3).'</td>
          <td bgcolor="#FFFF80" class="allbutleft"><b>8</b><br>'.moneyformat(-2).'</td>
          <td bgcolor="#FF9D6F" class="allbutleft"><b>9</b><br>'.moneyformat(-1).'</td></tr>
      <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#C0C0C0" style="border: 1px solid black"><b>10</b><br>'.moneyformat(0).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>11</b><br>'.moneyformat(1).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>12</b><br>'.moneyformat(1).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>13</b><br>'.moneyformat(2).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>14</b><br>'.moneyformat(2).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>15</b><br>'.moneyformat(3).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>16</b><br>'.moneyformat(3).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>17</b><br>'.moneyformat(4).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>18</b><br>'.moneyformat(4).'</td>
      <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>19</b><br>'.moneyformat(5).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>20</b><br>'.moneyformat(5).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>21</b><br>'.moneyformat(6).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>22</b><br>'.moneyformat(6).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>23</b><br>'.moneyformat(7).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>24</b><br>'.moneyformat(7).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>25</b><br>'.moneyformat(8).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>26</b><br>'.moneyformat(8).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>27</b><br>'.moneyformat(9).'</td>
      <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>28</b><br>'.moneyformat(9).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>29</b><br>'.moneyformat(10).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>30</b><br>'.moneyformat(10).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>31</b><br>'.moneyformat(11).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>32</b><br>'.moneyformat(11).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>33</b><br>'.moneyformat(11).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>34</b><br>'.moneyformat(12).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>35</b><br>'.moneyformat(12).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>36</b><br>'.moneyformat(12).'</td>
          <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>37</b><br>'.moneyformat(13).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>38</b><br>'.moneyformat(13).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>39</b><br>'.moneyformat(13).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>40</b><br>'.moneyformat(14).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>41</b><br>'.moneyformat(14).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>42</b><br>'.moneyformat(14).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>43</b><br>'.moneyformat(15).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>44</b><br>'.moneyformat(15).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>45</b><br>'.moneyformat(15).'</td>
          <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>46</b><br>'.moneyformat(16).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>47</b><br>'.moneyformat(16).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>48</b><br>'.moneyformat(16).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>49</b><br>'.moneyformat(17).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>50</b><br>'.moneyformat(17).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>51</b><br>'.moneyformat(17).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>52</b><br>'.moneyformat(18).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>53</b><br>'.moneyformat(18).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>54</b><br>'.moneyformat(18).'</td>
          <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>55</b><br>'.moneyformat(19).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>56</b><br>'.moneyformat(19).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>57</b><br>'.moneyformat(19).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>58</b><br>'.moneyformat(20).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>59</b><br>'.moneyformat(20).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>60</b><br>'.moneyformat(20).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>61</b><br>'.moneyformat(21).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>62</b><br>'.moneyformat(21).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>63</b><br>'.moneyformat(21).'</td>
          <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#03E3FC" style="border: 1px solid black"><b>64</b><br>'.moneyformat(21).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>65</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>66</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>67</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>68</b><br>'.moneyformat(22).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>69</b><br>'.moneyformat(23).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>70</b><br>'.moneyformat(23).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>71</b><br>'.moneyformat(23).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>72</b><br>'.moneyformat(23).'</td>
          <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>73</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>74</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>75</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>76</b><br>'.moneyformat(24).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>77</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>78</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>79</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>80</b><br>'.moneyformat(25).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>81</b><br>'.moneyformat(26).'</td>
          <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>82</b><br>'.moneyformat(26).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>83</b><br>'.moneyformat(26).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>84</b><br>'.moneyformat(26).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>85</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>86</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>87</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>88</b><br>'.moneyformat(27).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>89</b><br>'.moneyformat(28).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>90</b><br>'.moneyformat(28).'</td>
          <tr><td height=5></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <tr><td height=48 bgcolor="#02B4C8" style="border: 1px solid black"><b>91</b><br>'.moneyformat(28).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>92</b><br>'.moneyformat(28).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>93</b><br>'.moneyformat(29).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>94</b><br>'.moneyformat(29).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>95</b><br>'.moneyformat(29).'</td>
          <td bgcolor="#03E3FC" class="allbutleft"><b>96</b><br>'.moneyformat(29).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>97</b><br>'.moneyformat(30).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>98</b><br>'.moneyformat(30).'</td>
          <td bgcolor="#02B4C8" class="allbutleft"><b>99</b><br>'.moneyformat(30).'</td>
      </tr></table>';
}
for ($i=0;$i<5;$i++) {
    if ( $PlayerExists[$i] ) {
        $remainder = ( $IncomeSpace[$i] - 1 ) % 9;
        $quotient = ( $IncomeSpace[$i] - $remainder - 1 ) / 9;
        if ( $IncomeSpace[$i] == 0 ) { $remainder = 8*$BSIncomeTrack; $quotient = -1; }
        if ( $quotient % 2 ) { $oddrow = 1; }
        else                 { $oddrow = 0; }
        if ( $BSIncomeTrack ) {
            $dotY = $regulator + 660 - 66*$quotient;
            $dotX = 16 + 376*$oddrow - 47*(2*$oddrow-1)*$remainder;
        } else {
            $dotY = $regulator + 53 + 53*$quotient;
            $dotX = 16 + 46*$remainder;
        }
        $numsuperiors = 0;
        $numinferiors = 0;
        for ($j=0;$j<$i;$j++) { if ( $PlayerExists[$j] and $IncomeSpace[$j] == $IncomeSpace[$i] ) { $numsuperiors++; } }
        for ($j=$i+1;$j<5;$j++) { if ( $PlayerExists[$j] and $IncomeSpace[$j] == $IncomeSpace[$i] ) { $numinferiors++; } }
        $dotX = $dotX + $dotXboosts[$numsuperiors][$numinferiors];
        $dotY = $dotY + $dotYboosts[$numsuperiors][$numinferiors];
        if ( $MyColour == $i and $ShowAsBlue ) { $Whatcol = 7; }
        else                                   { $Whatcol = $i; }
        echo '<img src="gfx/i'.
             $Whatcol.
             '.png" alt="';
        if ( $IncomeSpace[$i] > 9 ) {
            echo str_replace( array('\playername'      , '\spacenumber'  , '\amount'                                        ),
                              array($PlayerFullName[$i], $IncomeSpace[$i], moneyformat($incomeamountarray[$IncomeSpace[$i]])),
                              transtext('gbPlayerIncome+')
                              );
        } else {
            echo str_replace( array('\playername'      , '\spacenumber'  , '\amount'                                        ),
                              array($PlayerFullName[$i], $IncomeSpace[$i], moneyformat($incomeamountarray[$IncomeSpace[$i]])),
                              transtext('gbPlayerIncome-')
                              );
        }
        echo '" style="position:absolute; top: '.
             $dotY.
             'px; left: '.
             $dotX.
             'px; z-index: 1">';
    }
}
echo '</div>';

/////////////////////////////
/////////////////////////////
/////////////////////////////

if ( $PlayControls ) {
    echo '<form action="gameaction.php" method="POST">';
    if ( $AnyMoveForm ) { echo $MoveForm; }
}

if ( $MyColour == $PlayerToMove and $KickVoteActive ) {
    echo '<p><font color="#FF0000"><b>'.
         transtext('miKickVoteNotice').
         '</b></font>';
}

echo '<p>'.
     str_replace( array('\original'     , '\current'     ),
                  array($OriginalPlayers, $CurrentPlayers),
                  transtext('gbOrigCurPlayers')
                  ).
     ' '.
     str_replace( array('\kickvoteinterval'            , '\autokickinterval'            ),
                  array($TimeLimitA.' '.$TimeLimitAunit, $TimeLimitB.' '.$TimeLimitBunit),
                  transtext('gbTimeLimits')
                  ).
     '<p>';
if ( ( $ModularBoardParts & 4 ) == 4 and !is_null($VirtualConnections) ) {
    // NB. $VirtualConnections is the unaltered database column, so if there are no
    // virtual connections then it will indeed be NULL and not the empty array
    if ( $SpecialRules & 1 ) { echo transtext('gbRevVCYes'); }
    else                     { echo transtext('gbRevVCNo');  }
    echo '<br>';
}
if ( $SpecialRules & 4 ) { echo transtext('gbCMOvrMCOR').'<p>'; }
else                     { echo transtext('gbCMOvrRAW').'<p>';  }
echo 'Behaviour at Time Limit B: '.$DoWhatAtB.'.<br>The last move was at '.$LastMove.' GMT.<p>';

if ( $NarrativeTicker == 2 ) {
    echo 'This game\'s log has been deleted, as have a large number of the other logs from the site\'s first year of operation, to save on storage space. (Newer logs are stored in a different format that takes up much less space, so it is unlikely to be necessary to delete logs again.)<p>';
} else {
    if ( $NarrativeTicker ) {
        echo '<i>There is no game log for this game. This might be because something went wrong and the log had to be deleted.</i>';
    } else {
        echo '<a href="viewticker.php?GameID='.$EscapedGameID.'" onClick="return popup(this,\'gameticker\',635,470,\'yes\')">Log of game events</a>';
    }
    if ( $GameStatus == 'In Progress' or $GameStatus == 'Recruiting Replacement' ) {
        echo ' --- <a href="carddistribution.php?GameID='.$GameID.'" onClick="return popup(this,\'carddist\',380,470,\'yes\')">Card distribution';
        if ( $OriginalPlayers == 2 or $CurrentPlayers > 2 ) { echo ' and remaining cards'; }
        echo '</a>';
    }
    echo '<p>';
}

if ( $PlayControls and $Administrator ) { echo $TheAdminThings; }
if ( $PlayControls and @$PlayerThings ) { echo $ThePlayerStandardThings; }

if ( $PlayersMissingThatMatter ) { get_translation_module(24); }

if ( $GameStatus == 'Recruiting Replacement' and $MyColour != 50 ) {
    if ( in_array($PlayerToMove,$ReplacementOffers_Colours) ) {
        echo 'The following users have volunteered to join the game in the current vacant spot. You can accept one of them by clicking the corresponding button.<input type="hidden" id="whotoacceptid" name="whotoaccept" value=0><table><tr><td><b>Name</b></td><td><b>Rating</b></td><td></td></tr>';
        for ($i=0;$i<$NumReplacementOffers;$i++) {
            if ( $ReplacementOffers_Colours[$i] == $PlayerToMove ) {
                echo '<tr><td><a href="userdetails.php?UserID='.
                     $ReplacementOffers[$i][0].
                     '">'.
                     $ReplacementOffers[$i][1].
                     '</td><td>'.
                     $ReplacementOffers[$i][3].
                     '</td><td><input type="submit" name="FormSubmit" value="Accept Replacement" onClick="document.getElementById(\'whotoacceptid\').value='.
                     $ReplacementOffers[$i][0].
                     '"></td></tr>';
            }
        }
        echo '</table>';
    } else {
        echo 'There have been no offers to join the game in the current vacant spot. If your user preferences are set to allow it, you will be emailed when somebody makes an offer.<p>';
    }
    if ( ( $CurrentPlayers > $MinimumPlayersAllowed or
           $MinimumPlayersAllowed <= 2
           ) and
         $CurrentPlayers != 3
         ) {
        if ( $KickVote[$MyColour] ) {
            $YText = ' selected';
            $NText = '';
        } else {
            $YText = '';
            $NText = ' selected';
        }
        echo transtext('grrVoteDS'). // You may vote on whether to downsize the game without waiting
             '<br>'.
             transtext('grrDownsize?'). // Downsize? (label for selection list)
             ' <select name="DSVote"><option value="Yes"'.$YText.'>Yes<option value="No"'.$NText.'>No</select> <input type="submit" name="FormSubmit" value="Vote On Downsizing"><p>';
    }
} else if ( $_SESSION['LoggedIn'] and !$Banned and $MyColour == 50 and $PlayersMissingThatMatter and !$GameIsFinished ) {
    if ( in_array($_SESSION['MyUserID'],$ReplacementOffers_Users) ) {
        echo transtext('grrWaitingAccept'). // We are still waiting for one of the remaining players to accept your request
             ' In case you cannot remember, you asked to take over as ';
        for ($i=0;$i<$NumReplacementOffers;$i++) {
            if ( $ReplacementOffers[$i][0] == $_SESSION['MyUserID'] ) {
                switch ( $ReplacementOffers[$i][2] ) {
                    case 0: echo transtext('_colourRed');    break;
                    case 1: echo transtext('_colourYellow'); break;
                    case 2: echo transtext('_colourGreen');  break;
                    case 3: echo transtext('_colourPurple'); break;
                    case 4: echo transtext('_colourGrey');   break;
                }
                echo ', and your performance ';
                if ( $ReplacementOffers[$i][4] ) { echo 'WILL'; }
                else                             { echo 'WILL NOT'; }
            }
        }
        echo ' count towards your statistics. ';
        if ( !$Administrator ) { echo '<form action="gameaction.php" method="POST"><input type="hidden" name="GameID" value="'.$GameID.'">'; }
        echo '<input type="submit" name="FormSubmit" value="Withdraw Request">';
        if ( !$Administrator ) { echo '</form>'; }
        echo '<p>';
    } else if ( $PlayersMissingThatMatter > 1 ) {
        if ( dbquery( DBQUERY_READ_RESULTSET,
                      'SELECT "User" FROM "PlayerGameRcd" WHERE "Game" = :game: AND "User" = :user:',
                      'user' , $_SESSION['MyUserID'] ,
                      'game' , $GameID
                      ) === 'NONE' ) {
            echo transtext('grrIntroPl'). // This game is awaiting new players to replace ones who have left.
                 ' '.
                 transtext('grrEligiblePl'). // You are eligible to ask to be a replacement. To do so, choose a colour and click "Join as Replacement". It then only remains for one of the other players in the game to accept your request.
                 '<br>';
            if ( $Administrator ) { echo '<br>'; }
            else                  { echo '<form action="gameaction.php" method="POST"><input type="hidden" name="GameID" value="'.$GameID.'">'; }
            if ( $RailPhase ) {
                echo transtext('grrWillNotCount'); // This game will not be counted in your stats
            } else {
                if ( $Round < 3 ) { $countsboxchecked = ' checked'; }
                else              { $countsboxchecked = '';         }
                echo transtext('grrCount?'). // Count this game in my stats y/n?
                     ' <input type="checkbox" name="Counts" value="1"'.$countsboxchecked.'>';
            }
            echo 'Colour: <select name="colourtoreplace">';
            for ($i=0;$i<5;$i++) {
                if ( $PlayerMissingAndMatters[$i] ) {
                    echo '<option value='.$i;
                    if ( $i == $PlayerToMove ) { echo ' selected'; }
                    echo '>';
                    switch ( $i ) {
                        case 0: echo transtext('_colourRed');    break;
                        case 1: echo transtext('_colourYellow'); break;
                        case 2: echo transtext('_colourGreen');  break;
                        case 3: echo transtext('_colourPurple'); break;
                        case 4: echo transtext('_colourGrey');   break;
                    }
                }
            }
            echo '</select> <input type="submit" name="FormSubmit" value="Join as Replacement">';
            if ( !$Administrator ) { echo '</form>'; }
            echo '<p>';
        }
    } else {
        if ( dbquery( DBQUERY_READ_RESULTSET,
                      'SELECT "User" FROM "PlayerGameRcd" WHERE "Game" = :game: AND "User" = :user:',
                      'user' , $_SESSION['MyUserID'] ,
                      'game' , $GameID
                      ) === 'NONE' ) {
            echo transtext('grrIntro'). // This game is awaiting a new player to replace one who left.
                 ' '.
                 transtext('grrEligible'). // You are eligible to ask to be the replacement. To do so, click "Join as Replacement". It then only remains for one of the other players in the game to accept your request.
                 '<br>';
            if ( $Administrator ) { echo '<br>'; }
            else                  { echo '<form action="gameaction.php" method="POST"><input type="hidden" name="GameID" value="'.$GameID.'">'; }
            if ( $RailPhase ) {
                echo transtext('grrWillNotCount'); // This game will not be counted in your stats
            } else {
                if ( $Round < 3 ) { $countsboxchecked = ' checked'; }
                else              { $countsboxchecked = '';         }
                echo transtext('grrCount?'). // Count this game in my stats y/n?
                     ' <input type="checkbox" name="Counts" value="1"'.$countsboxchecked.'>';
            }
            echo '<input type="hidden" name="colourtoreplace" value="';
            for ($i=0;$i<5;$i++) {
                if ( $PlayerMissingAndMatters[$i] ) { echo $i; }
            }
            echo '"><input type="submit" name="FormSubmit" value="Join as Replacement">';
            if ( !$Administrator ) { echo '</form>'; }
            echo '<p>';
        }
    }
}

if ( $PlayControls and @$PlayerThings ) { echo $ThePlayerExtraThings; }
if ( $PlayControls ) { echo '<input type="hidden" name="GameID" value="'.$EscapedGameID.'"><input type="hidden" name="ProgressDigit" value="'.$NumMovesMade.'"></form>'; }

/////////////////////////////
/////////////////////////////
/////////////////////////////

require(HIDDEN_FILES_PATH.'displaythread.php');
$TranslatedTalkRules = array(
                           'No Talk During Game'              => transtext('trNTDG')   ,
                           'No Talk by Outsiders'             => transtext('trNTBO')   ,
                           'No Talk by Outsiders During Game' => transtext('trNTBODG') ,
                           'No Restrictions'                  => transtext('trNR')     ,
                           'No Talk Whatsoever'               => transtext('trNTW')
                           );
echo transtext('trTalkRulesAre:').
     ' '.
     $TranslatedTalkRules[$TalkRules].
     '.<p>';
if ( $Administrator ) {
    if ( $Closed == 'Forced Closed' ) { $StringFC = ' selected'; }
    else                              { $StringFC = ''; }
    if ( $Closed == 'Closed' ) { $StringC = ' selected'; }
    else                       { $StringC = ''; }
    if ( $Closed == 'Forced Open' ) { $StringFO = ' selected'; }
    else                            { $StringFO = ''; }
    if ( $Closed == 'Open' ) { $StringO = ' selected'; }
    else                     { $StringO = ''; }
    echo '<form action="threadview.php" method="POST">Thread is <select name="Closedness"><option value="FC"'.$StringFC.'>Forced Closed<option value="C"'.$StringC.'>Closed<option value="O"'.$StringO.'>Open<option value="FO"'.$StringFO.'>Forced Open</select> --- <input type="submit" name="FormSubmit" value="Execute"><p>';
} else {
    if ( $Closed == 'Closed' or $Closed == 'Forced Closed' ) {
        echo transtext('GameThreadClosed').'<p>';
    }
    echo '<form action="threadview.php" method="POST">';
}
if ( $TalkRules == 'No Talk Whatsoever' or
     ( $TalkRules == 'No Talk During Game' and GameStatus == 'In Progress' ) or
     ( $TalkRules == 'No Talk by Outsiders' and $MyColour == 50 ) or
     ( $TalkRules == 'No Talk by Outsiders During Game' and GameStatus == 'In Progress' and $MyColour == 50 )
     ) {
    $Postable = 0;
} else {
    $Postable = 1;
}
if ( $AlwaysHideMsgs ) { $ChkText = ' checked'; $hidetext = '; display: none'; }
else                   { $ChkText = ''; $hidetext = ''; }
if ( $NumberOfPosts ) { echo '<a name="msg">'; }
echo '<div style="position: relative"><input type="checkbox" name="showmsgs"'.
     $ChkText.
     ' onClick="ToggleShowThread();"> '.
     transtext('gbHideDiscussion').
     '</div><p><div id="dThread" style="position: relative'.
     $hidetext.
     '">';
displaythread($Thread, $Closed, $Postable, $Administrator, $Banned);
echo '</div><p>Click <a href="index.php">here</a> to return to the Main Page.</p></body><html>';

?>