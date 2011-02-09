<?php
if ( !isset($_GET['GameID']) ) { die('No game ID specified.'); }
require('_std-include.php');

$EscapedGameID = (int)$_GET['GameID'];
$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'CALL "GameGetData_GameTicker"(:game:)',
               'game' , $EscapedGameID
               );
if ( $QR === 'NONE' ) { die('Game not found.'); }
extract($QR);
if ( $VersionID == 2 ) {
    die('Incorrect game log type.');
} else if ( is_null($GameTicker) ) {
    die('There is no log for this game.');
} else {
    $LocationAutoValue = str_replace('|',',',$LocationAutoValue);
    $LocationNames = explode('|',$LocationNames);
    $SpaceNumbers = explode('|',$SpaceNumbers);
    $SpaceOrdinals = explode('|',$SpaceOrdinals);
    $SpaceTowns = explode('|',$SpaceTowns);
    $NumIndustrySpaces = count($SpaceTowns);
    $SpaceStatus = array();
    $SpaceTile = array();
    $TechLevels = array();
    $SpaceCubes = array();
    $SpaceDescriptions = array();
    $BriefSpaceDescriptions = array();
    for ($i=0;$i<$NumIndustrySpaces;$i++) {
        $SpaceStatus[] = 9;
        $SpaceTile[] = 0;
        $TechLevels[] = 0;
        $SpaceCubes[] = 1;
        $SpaceDescriptions[] = '"the'.$SpaceOrdinals[$i].' industry space in '.$LocationNames[$SpaceTowns[$i]].'"';
        $BriefSpaceDescriptions[] = '"'.$LocationNames[$SpaceTowns[$i]].$SpaceNumbers[$i].'"';
    }
    $SpaceTowns = implode(',',$SpaceTowns);
    $SpaceStatus = implode(',',$SpaceStatus);
    $SpaceTile = implode(',',$SpaceTile);
    $TechLevels = implode(',',$TechLevels);
    $SpaceCubes = implode(',',$SpaceCubes);
    $SpaceDescriptions = implode(',',$SpaceDescriptions);
    $BriefSpaceDescriptions = implode(',',$BriefSpaceDescriptions);
    $LocationNames = '"'.implode('","',$LocationNames).'"';
    $CanalStarts = explode('|',$CanalStarts);
    $NumCanalLinks = count($CanalStarts);
    $CanalStarts = implode(',',$CanalStarts);
    $CanalEnds = str_replace('|',',',$CanalEnds);
    $RailStarts = explode('|',$RailStarts);
    $NumRailLinks = count($RailStarts);
    $RailStarts = implode(',',$RailStarts);
    $RailEnds = str_replace('|',',',$RailEnds);
    $LinkStatusCanal = array();
    for ($i=0;$i<$NumCanalLinks;$i++) {
        $LinkStatusCanal[] = 9;
    }
    $LinkStatusCanal = implode(',',$LinkStatusCanal);
    $LinkStatusRail = array();
    for ($i=0;$i<$NumRailLinks;$i++) {
        $LinkStatusRail[] = 9;
    }
    $LinkStatusRail = implode(',',$LinkStatusRail);
    if ( $GameStatus == 'Finished' or $GameStatus == 'Aborted' ) {
        if ( $RailPhase ) {
            $CanalDMTiles = preg_split('/([0-7]+)/',$RandomLog,-1,PREG_SPLIT_DELIM_CAPTURE);
            $RailDMTiles = str_split($CanalDMTiles[3],1);
            $CanalDMTiles = str_split($CanalDMTiles[1],1);
        } else {
            $CanalDMTiles = preg_split('/([0-7]+)/',$RandomLog,-1,PREG_SPLIT_DELIM_CAPTURE);
            $CanalDMTiles = str_split($CanalDMTiles[1],1);
            $RailDMTiles = array();
        }
    } else if ( $RailPhase ) {
        $CanalDMTiles = preg_split('/([0-7]+)/',$RandomLog,-1,PREG_SPLIT_DELIM_CAPTURE);
        $CanalDMTiles = str_split($CanalDMTiles[1],1);
        if ( $TilesDrawn == 'None' ) { $RailDMTiles = array();                                 }
        else                         { $RailDMTiles = array_reverse(str_split($TilesDrawn,1)); }
    } else {
        if ( $TilesDrawn == 'None' ) { $CanalDMTiles = array();                                 }
        else                         { $CanalDMTiles = array_reverse(str_split($TilesDrawn,1)); }
        $RailDMTiles = array();
    }
    if ( $PreferredCurrencySymbol ) { $CurrencySymbol = $PreferredCurrencySymbol; }
    $CurrencySymbolAfter = (int)$CurrencySymbolAfterNumber[$CurrencySymbol];
    $CurrencySymbol = $Currencies[$CurrencySymbol];
    if ( $CurrencySymbolLocation == 1 )      { $CurrencySymbolAfter = 0; }
    else if ( $CurrencySymbolLocation == 2 ) { $CurrencySymbolAfter = 1; }
?><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>Log</title><script type="text/javascript" src="tickerscript.js"></script><SCRIPT TYPE="text/javascript">
<!--

var gamenum = <?php echo $EscapedGameID; ?>;
var canaldmtiles = [<?php echo implode(',',$CanalDMTiles); ?>];
var raildmtiles = [<?php echo implode(',',$RailDMTiles); ?>];
var reverseticker = <?php echo $ReverseTicker; ?>;
var rawticker = "<?php echo $GameTicker; ?>";
var rawtickernames = "<?php echo $GameTickerNames; ?>";

var commenttext;
var newtime = 0;
var thetime;
var keepgoing = 0;
var starterdetails = [];
var creatordetails = [];
var gamenum;
var thedetails;
var thedetailsnames;
var playerexists;
var playerdetails = [];
var pronouns = [];
var turnorder;
var numactionstaken;
var cubeprice;
var money;
var dmsalesmade = 0;
var round = 1;
var numrounds = 10;
var victorypoints = [0,0,0,0,0];
var incomespace = [10,10,10,10,10];
var amountspent = [0,0,0,0,0];
var debtmode = 0;
var seconddevelopmode = 0;
var secondrailmode = 0;
var continuesellingmode = 0;
var railphase = 0;
var numplayers = 0;
var twoplayers;
var secondmove = 0;
var numflippedtiles = [<?php echo $LocationAutoValue;?>];
var linkstatus = [<?php echo $LinkStatusCanal; ?>];
var spacestatus = [<?php echo $SpaceStatus; ?>];
var spacetile = [<?php echo $SpaceTile; ?>];
var techlevels = [<?php echo $TechLevels; ?>];
var spacecubes = [<?php echo $SpaceCubes; ?>];
var coaldemand = 0;
var irondemand = 0;
var cottondemand = 0;
var currencysymbol = "<?php echo $CurrencySymbol; ?>";
var currencysymbolafter = <?php echo $CurrencySymbolAfter; ?>;
var numindustryspaces = <?php echo $NumIndustrySpaces; ?>;
var numcanallinks = <?php echo $NumCanalLinks; ?>;
var numraillinks = <?php echo $NumRailLinks; ?>;
var remainingtiles = [[12,12,12,12,12],[7,7,7,7,7],[4,4,4,4,4],[8,8,8,8,8],[6,6,6,6,6]];
var techleveldiscrim = [[4,4,4,3,3,3,2,2,2,1,1,1],[4,4,3,3,2,2,1],[4,3,2,1],[4,4,3,3,2,2,1,1],[2,2,1,1,0,0]];
var costs = [[12,14,16,18],[5,7,8,10],[5,7,9,12],[6,7,8,9],[16,25,0,0]];
var incomeboost = [[5,4,3,2],[4,7,6,5],[3,3,2,1],[3,3,4,4],[2,1,0,0]];
var vpboost = [[3,5,9,12],[1,2,3,4],[3,5,7,9],[2,4,6,9],[10,18,0,0]];
var spacetowns = [<?php echo $SpaceTowns; ?>];
var linkstarts = [<?php echo $CanalStarts; ?>];
var linkends = [<?php echo $CanalEnds; ?>];
var railstarts = [<?php echo $RailStarts; ?>];
var railends = [<?php echo $RailEnds; ?>];
var linkstatusrail = [<?php echo $LinkStatusRail; ?>];
var initialcubes = [[2,3,4,5],[4,4,5,6]];
var dmincome = [3,3,2,2,1,1,0,0];
var incomefallback = [0,0,1,2,3,4,5,6,7,8,9,10,10,12,12,14,14,16,16,18,18,20,20,22,22,24,24,26,26,28,28,30,30,30,33,33,33,36,36,36,39,39,39,42,42,42,45,45,45,48,48,48,51,51,51,54,54,54,57,57,57,60,60,60,60,64,64,64,64,68,68,68,68,72,72,72,72,76,76,76,76,80,80,80,80,84,84,84,84,88,88,88,88,92,92,92,92,96,96,96];
var incomeamounts = [-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,11,12,12,12,13,13,13,14,14,14,15,15,15,16,16,16,17,17,17,18,18,18,19,19,19,20,20,20,21,21,21,21,22,22,22,22,23,23,23,23,24,24,24,24,25,25,25,25,26,26,26,26,27,27,27,27,28,28,28,28,29,29,29,29,30,30,30];

function moneyformat(x) {
    if ( currencysymbolafter ) {
        return x + currencysymbol;
    } else if ( x < 0 )  {
        x = -x;
        return "-" + currencysymbol + x;
    } else {
        return currencysymbol + x;
    }
}

var incomeamountstext = [
    moneyformat(10),moneyformat(9) ,moneyformat(8) ,moneyformat(7) ,moneyformat(6) ,
    moneyformat(5) ,moneyformat(4) ,moneyformat(3) ,moneyformat(2) ,moneyformat(1) ,
    moneyformat(0) ,moneyformat(1) ,moneyformat(1) ,moneyformat(2) ,moneyformat(2) ,
    moneyformat(3) ,moneyformat(3) ,moneyformat(4) ,moneyformat(4) ,moneyformat(5) ,
    moneyformat(5) ,moneyformat(6) ,moneyformat(6) ,moneyformat(7) ,moneyformat(7) ,
    moneyformat(8) ,moneyformat(8) ,moneyformat(9) ,moneyformat(9) ,moneyformat(10),
    moneyformat(10),moneyformat(11),moneyformat(11),moneyformat(11),moneyformat(12),
    moneyformat(12),moneyformat(12),moneyformat(13),moneyformat(13),moneyformat(13),
    moneyformat(14),moneyformat(14),moneyformat(14),moneyformat(15),moneyformat(15),
    moneyformat(15),moneyformat(16),moneyformat(16),moneyformat(16),moneyformat(17),
    moneyformat(17),moneyformat(17),moneyformat(18),moneyformat(18),moneyformat(18),
    moneyformat(19),moneyformat(19),moneyformat(19),moneyformat(20),moneyformat(20),
    moneyformat(20),moneyformat(21),moneyformat(21),moneyformat(21),moneyformat(21),
    moneyformat(22),moneyformat(22),moneyformat(22),moneyformat(22),moneyformat(23),
    moneyformat(23),moneyformat(23),moneyformat(23),moneyformat(24),moneyformat(24),
    moneyformat(24),moneyformat(24),moneyformat(25),moneyformat(25),moneyformat(25),
    moneyformat(25),moneyformat(26),moneyformat(26),moneyformat(26),moneyformat(26),
    moneyformat(27),moneyformat(27),moneyformat(27),moneyformat(27),moneyformat(28),
    moneyformat(28),moneyformat(28),moneyformat(28),moneyformat(29),moneyformat(29),
    moneyformat(29),moneyformat(29),moneyformat(30),moneyformat(30),moneyformat(30)
    ];
var phasenames = ["canal","rail"];
<?php

switch ( $VersionID ) {
    case 1:
        echo 'var carddescriptions = ["a <b>Barrow – In – Furness</b> card","a <b>Birkenhead</b> card","a <b>Blackburn</b> card","","a <b>Bolton</b> card","a <b>Burnley</b> card","a <b>Bury</b> card","a <b>Colne</b> card","an <b>Ellesmere Port</b> card","a <b>Fleetwood</b> card","a <b>Lancaster</b> card","a <b>Liverpool</b> card","a <b>Macclesfield</b> card","a <b>Manchester</b> card","","","an <b>Oldham</b> card","a <b>Preston</b> card","a <b>Rochdale</b> card","","","a <b>Stockport</b> card","a <b>Warrington & Runcorn</b> card","a <b>Wigan</b> card","","","","","","","","","","","","","","","","","a <b>Cotton Mill</b> card","a <b>Coal Mine</b> card","an <b>Iron Works</b> card","a <b>Port</b> card","a <b>Shipyard</b> card","","","","","","<i>card information missing</i>"];';
    break;
    case 3:
        echo 'var carddescriptions = ["an <b>Arras</b> card","an <b>Auchel</b> card","a <b>Béthune</b> card","a <b>Birkenhead</b> card","a <b>Boulogne</b> card","","a <b>Calais</b> card","a <b>Douai</b> card","a <b>Douvrin</b> card","a <b>Dunkerque</b> card","","a <b>Fourmies</b> card","a <b>Lens</b> card","a <b>Lewarde</b> card","a <b>Lille</b> card","","a <b>Marbaix</b> card","","","a <b>Roubaix</b> card","a <b>Sailly – sur – la – Lys</b> card","","a <b>Saint – Omer</b> card","","a <b>Valenciennes</b> card","","","","","","","","","","","","","","","","a <b>Cotton Mill</b> card","a <b>Coal Mine</b> card","an <b>Iron Works</b> card","a <b>Port</b> card","a <b>Shipyard</b> card","","","","","","<i>card information missing</i>"];';
    break;
    case 4:
        echo 'var carddescriptions = ["an <b>Arras</b> card","an <b>Auchel</b> card","a <b>Béthune</b> card","a <b>Boulogne</b> card","","a <b>Calais</b> card","a <b>Douai</b> card","a <b>Douvrin</b> card","a <b>Dunkerque</b> card","a <b>Fourmies</b> card","","a <b>Lens</b> card","a <b>Lewarde</b> card","a <b>Lille</b> card","a <b>Marbaix</b> card","","","a <b>Rotterdam</b> card","a <b>Roubaix</b> card","a <b>Sailly – sur – la – Lys</b> card","","a <b>Saint – Omer</b> card","a <b>Saint – Venant</b> card","a <b>Valenciennes</b> card","","","","","","","","","","","","","","","","","a <b>Cotton Mill</b> card","a <b>Coal Mine</b> card","an <b>Iron Works</b> card","a <b>Port</b> card","a <b>Shipyard</b> card","","","","","","<i>card information missing</i>"];';
    break;
}

?>var locationnames = [<?php echo $LocationNames; ?>];
var industrynames = ["Cotton Mill","Coal Mine","Iron Works","Port","Shipyard"];
var spacedescriptions = [<?php echo $SpaceDescriptions; ?>];
var briefspacedescriptions = [<?php echo $BriefSpaceDescriptions; ?>];
var dmdescrs = ["1st +3","2nd +3","1st +2","2nd +2","1st +1","2nd +1","1st +0","2nd +0"];
var eventbits = [2,6,7,3,4,3,4,3,3,1,
                 0,0,0,0,0,4,8,9,5,6,
                 5,6,5,5,3,0,0,0,0,0,
                 3,3,0,0,0,0,0,0,0,0,
                 1,2,2,0,0,0,0,0,0,0,
                 1,0,5,1,4,1,0,0,0,0,
                 1,3,1,0,0,0,0,0,0,0,
                 5,5,0,0,0,0,0,0,0,0,
                 3,2,2,1,4,3,0,0,0,0,
                 4,2,0,0,0,0,0,3,0,0
                 ];
var arrayprogress = 0;
var outputarray = [];
var eventtype;
var bits;
var usereventname;

//-->
</SCRIPT></head><body onLoad="producelog();"></body></html><?php } ?>