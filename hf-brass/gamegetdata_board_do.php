<?php

if ( !defined('MAX_PLAYERS') ) {
    define('MAX_PLAYERS', 5);
}
if ( !defined('PERMITTED_DEPARTURES') ) {
    define('PERMITTED_DEPARTURES', 2);
}

function letter_end_number ($x) {
    $x = (int)$x;
    $letters = 'ABCDEFGHIJ';
    return substr($x,0,-1).$letters[substr($x,-1)];
}

function callmovetimediff () {
    global $GAME;
    $tempvar = $GAME['movetimediff'];
    $GAME['movetimediff'] = 0;
    $GAME['LastEventSQL'] = ', "GameInProgress"."LastEvent" = \''.
                            date(MYSQL_DATETIME_FORMAT).
                            '\'';
    return letter_end_number($tempvar);
}

function gamegetdata_board_do ($GameID) {
    global $unexpectederrormessage;
    $GAME = dbquery( DBQUERY_READ_SINGLEROW,
                     'CALL "GameGetData_Game_Modify"(:gameid:)',
                     'gameid' , $GameID
                     );
    $QRP = dbquery( DBQUERY_READ_RESULTSET,
                    'CALL "GameGetData_User_Modify"(:gameid:)',
                    'gameid' , $GameID
                    );
    if ( $GAME === 'NONE' ) { return false; }
    if ( $GAME['GameStatus'] == 'Cancelled' and
         !$Administrator
         ) {
        return false;
    }
    if ( $GAME['GameStatus'] == 'Recruiting' or
         $GAME['GameStatus'] == 'Cancelled'
         ) {
        return 'WRONG PAGE';
    }
    if ( $GAME['GameIsFinished'] ) { return 'FINISHED'; }
    $colournames = array( transtext('_colourRed'),
                          transtext('_colourYellow'),
                          transtext('_colourGreen'),
                          transtext('_colourPurple'),
                          transtext('_colourGrey')
                          );
    $englishcolournames = array('Red', 'Yellow', 'Green', 'Purple', 'Grey');
    $pronounmap_PossessiveLowercase_Eng = array( 'He'  => 'his',
                                                 'She' => 'her',
                                                 'It'  => 'its'
                                                 );
    $GAME['LastEventSQL']        = '';
    $GAME['MoveMadeByPlayer']    = array(0, 0, 0, 0, 0);
    $GAME['MoveMade']            = 0;
    $GAME['movetimediff']        = time() - strtotime($GAME['LastEvent']);
    $GAME['locationnames']       = explode('|', $GAME['LocationNames']);
    $GAME['spacetowns']          = explode('|', $GAME['SpaceTowns']);
    $GAME['tileindustries']      = explode('|', $GAME['TileIndustries']);
    $GAME['CanalStarts']         = explode('|', $GAME['CanalStarts']);
    $GAME['CanalEnds']           = explode('|', $GAME['CanalEnds']);
    $GAME['RailStarts']          = explode('|', $GAME['RailStarts']);
    $GAME['RailEnds']            = explode('|', $GAME['RailEnds']);
    $GAME['CanalAlwaysExists']   = explode('|', $GAME['CanalAlwaysExists']);
    $GAME['CanalExistenceArray'] = explode('|', $GAME['CanalExistenceArray']);
    $GAME['RailAlwaysExists']    = explode('|', $GAME['RailAlwaysExists']);
    $GAME['RailExistenceArray']  = explode('|', $GAME['RailExistenceArray']);
    $GAME['SpaceAlwaysExists']   = explode('|', $GAME['SpaceAlwaysExists']);
    $GAME['SpaceExistenceArray'] = explode('|', $GAME['SpaceExistenceArray']);
    $GAME['carddetailarray']     = explode('|', $GAME['CardDetailArray']);
    $GAME['carddetailarrayb']    = explode('|', $GAME['CardDetailArrayB']);
    $GAME['ShuffledDeck']        = explode('|', $GAME['ShuffledDeck']);
    $GAME['spacenumbers']        = explode('|', $GAME['SpaceOrdinals']);
    $GAME['ExternalLocations']   = explode('|', $GAME['ExternalLocations']);
    $GAME['LocationAutoValue']   = explode('|', $GAME['LocationAutoValue']);
    $GAME['CoalNet']             = explode('|', $GAME['CoalNet']);
    $GAME['CanalExistenceArray'] = array_map( 'intval',
                                              $GAME['CanalExistenceArray']
                                              );
    $GAME['RailExistenceArray']  = array_map( 'intval',
                                              $GAME['RailExistenceArray']
                                              );
    $GAME['SpaceExistenceArray'] = array_map( 'intval',
                                              $GAME['SpaceExistenceArray']
                                              );
    $GAME['ModularBoardParts']   = (int)$GAME['ModularBoardParts'];
    $GAME['SpecialRules']        = (int)$GAME['SpecialRules'];
    $GAME['DiscardPile']         = explode('|', $GAME['DiscardPile']);
    if ( $GAME['DiscardPile'][0] == '' ) {
        $GAME['DiscardPile'] = array();
    }
    $GAME['NumTowns']          = count($GAME['locationnames']);
    $GAME['NumIndustrySpaces'] = count($GAME['spacetowns']);
    $GAME['NumCanalLinks']     = count($GAME['CanalStarts']);
    $GAME['NumRailLinks']      = count($GAME['RailStarts']);
    if ( is_null($GAME['CardsToRemove']) ) {
        $GAME['CardsToRemove'] = array();
    } else {
        $GAME['CardsToRemove'] = explode('|', $GAME['CardsToRemove']);
    }
    if ( is_null($GAME['VirtualConnections']) ) {
        $GAME['GeneralisedVCs'] = array();
    } else {
        $GAME['GeneralisedVCs'] =
            explode(':', $GAME['VirtualConnections']);
        for ($i=0; $i<count($GAME['GeneralisedVCs']); $i++) {
            $GAME['GeneralisedVCs'][$i] =
                explode('|', $GAME['GeneralisedVCs'][$i]);
        }
    }
    if ( is_null($GAME['TileDenyStrategicBlock']) ) {
        $GAME['GeneralisedNoStratBlock'] = array();
    } else {
        $GAME['GeneralisedNoStratBlock'] =
            explode(':', $GAME['TileDenyStrategicBlock']);
        for ($i=0; $i<count($GAME['GeneralisedNoStratBlock']); $i++) {
            $GAME['GeneralisedNoStratBlock'][$i] =
                explode('|', $GAME['GeneralisedNoStratBlock'][$i]);
        }
    }
    if ( $GAME['RailPhase'] ) {
        $LinkStarts = $GAME['RailStarts'];
        $LinkEnds   = $GAME['RailEnds'];
        $NumLinks   = $GAME['NumRailLinks'];
    } else {
        $LinkStarts = $GAME['CanalStarts'];
        $LinkEnds   = $GAME['CanalEnds'];
        $NumLinks   = $GAME['NumCanalLinks'];
    }
    if ( $NumLinks % 2 ) {
        $GAME['LinkStatus'] = substr($GAME['LinkStatus'], 0, -1);
    }
    if ( $GAME['NumIndustrySpaces'] % 2 ) {
        $GAME['SpaceStatus'] = substr($GAME['SpaceStatus'], 0, -1);
        $GAME['SpaceTile']   = substr($GAME['SpaceTile']  , 0, -1);
        $GAME['TechLevels']  = substr($GAME['TechLevels'] , 0, -1);
        $GAME['SpaceCubes']  = substr($GAME['SpaceCubes'] , 0, -1);
    }
    $GAME['CoalInLancs'] = 0;
    $GAME['IronInLancs'] = 0;
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] != 9 and
             $GAME['SpaceTile'][$i] == 1
             ) {
            $GAME['CoalInLancs'] += $GAME['SpaceCubes'][$i];
        }
        if ( $GAME['SpaceStatus'][$i] != 9 and
             $GAME['SpaceTile'][$i] == 2
             ) {
            $GAME['IronInLancs'] += $GAME['SpaceCubes'][$i];
        }
    }
    if ( $GAME['CurrentPlayers'] == 2 ) {
        $GAME['cubeprice'] = array(2, 2, 3, 3, 4, 4, 5);
    } else {
        $GAME['cubeprice'] = array(1, 1, 2, 2, 3, 3, 4, 4, 5);
    }
    $GAME['Money']          = explode('|', $GAME['Money']);
    $GAME['AmountSpent']    = explode('|', $GAME['AmountSpent']);
    $GAME['VictoryPoints']  = explode('|', $GAME['VictoryPoints']);
    $GAME['IncomeSpace']    = explode('|', $GAME['IncomeSpace']);
    $GAME['Cards']          = explode(':', $GAME['Cards']);
    $GAME['RemainingTiles'] = str_split($GAME['RemainingTiles'], MAX_PLAYERS);
    for ($i=0; $i<6; $i++) {
        $GAME['RemainingTiles'][$i] =
            array_map('hexdec', str_split($GAME['RemainingTiles'][$i]));
    }
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $GAME['Cards'][$i] = explode('|', $GAME['Cards'][$i]);
        $GAME['HandSize'][$i] = count($GAME['Cards'][$i]);
        if ( $GAME['HandSize'][$i] == 1 and
             $GAME['Cards'][$i][0] == ''
             ) {
            $GAME['Cards'][$i] = array();
            $GAME['HandSize'][$i] = 0;
        }
    }
    if ( $GAME['GameStatus'] == 'In Progress' and
         time() - strtotime($GAME['LastMove']) > 60 * $GAME['TimeLimitA'] and
         !MAINTENANCE_DISABLED
         ) {
        $GAME['CanKickVote'] = 1;
    } else {
        $GAME['CanKickVote'] = 0;
    }
    switch ( $GAME['SpecialModes'] ) {
        case 1:
            $GAME['DebtMode']            = 1;
            $GAME['ContinueSellingMode'] = 0;
            $GAME['SecondRailMode']      = 0;
            $GAME['SecondDevelopMode']   = 0;
        break;
        case 2:
            $GAME['DebtMode']            = 0;
            $GAME['ContinueSellingMode'] = 1;
            $GAME['SecondRailMode']      = 0;
            $GAME['SecondDevelopMode']   = 0;
        break;
        case 3:
            $GAME['DebtMode']            = 0;
            $GAME['ContinueSellingMode'] = 0;
            $GAME['SecondRailMode']      = 1;
            $GAME['SecondDevelopMode']   = 0;
        break;
        case 4:
            $GAME['DebtMode']            = 0;
            $GAME['ContinueSellingMode'] = 0;
            $GAME['SecondRailMode']      = 0;
            $GAME['SecondDevelopMode']   = 1;
        break;
        default:
            $GAME['DebtMode']            = 0;
            $GAME['ContinueSellingMode'] = 0;
            $GAME['SecondRailMode']      = 0;
            $GAME['SecondDevelopMode']   = 0;
    }
    $HasLinkedComponents = '';
    for ($i=0; $i<$GAME['NumTowns']; $i++) {
        $HasLinkedComponents .= '0';
    }
    $GAME['HasBuilt'] = array(0, 0, 0, 0, 0);
    $GAME['HasBuiltInTown'] = array( $HasLinkedComponents,
                                     $HasLinkedComponents,
                                     $HasLinkedComponents,
                                     $HasLinkedComponents,
                                     $HasLinkedComponents
                                     );
    $GAME['HasLinkedToTown'] = array( $HasLinkedComponents,
                                      $HasLinkedComponents,
                                      $HasLinkedComponents,
                                      $HasLinkedComponents,
                                      $HasLinkedComponents
                                      );
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] < MAX_PLAYERS ) {
            $GAME['HasBuiltInTown'][$GAME['SpaceStatus'][$i]][$GAME['spacetowns'][$i]] = 1;
            $GAME['HasBuilt'][$GAME['SpaceStatus'][$i]] = 1;
        }
    }
    for ($i=0; $i<$NumLinks; $i++) {
        if ( $GAME['LinkStatus'][$i] < MAX_PLAYERS ) {
            $GAME['HasLinkedToTown'][$GAME['LinkStatus'][$i]][$LinkStarts[$i]] = 1;
            $GAME['HasLinkedToTown'][$GAME['LinkStatus'][$i]][$LinkEnds[$i]]   = 1;
            $GAME['HasBuilt'][$GAME['LinkStatus'][$i]] = 1;
        }
    }
    if ( is_null($GAME['GameTicker']) or
         is_null($GAME['GameTickerNames'])
         ) {
        $GAME['NarrativeTicker'] = true;
    } else {
        $GAME['NarrativeTicker'] = false;
    }
    $GAME['AltGameTicker']   = '';
    $GAME['GameTickerNames'] = '';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $GAME['PlayerName'][$i]     = transtext('_PlayerBoxAbsent');
        $GAME['PlayerFullName'][$i] = $colournames[$i].
                                      ' ('.$GAME['PlayerName'][$i].')';
        $GAME['PlayerName_Eng'][$i] = 'Missing';
        $GAME['PlayerFullName_Eng'][$i] = $englishcolournames[$i].
                                          ' (Missing)';
        $GAME['PossessivePronounLC_Eng'][$i] = 'its';
    }
    $GAME['MyColour'] = 50;
    $GAME['PlayersMissing'] = (int)$GAME['CurrentPlayers'];
    $GAME['PlayerMissing'] = $GAME['PlayerExists'];
    if ( $GAME['GameStatus'] == 'In Progress' and
         strpos($GAME['KickVote'], '1') !== false
         ) {
        $GAME['CanKickVote'] = 1;
    }
    while ( $row = db_fetch_assoc($QRP) ) {
        $GAME['PlayersMissing']--;
        $GAME['PlayerMissing'][$row['Colour']]           = 0;
        $GAME['PlayerUserID'][$row['Colour']]            = $row['UserID'];
        $GAME['PlayerName'][$row['Colour']]              = $row['Name'];
        $GAME['PlayerFullName'][$row['Colour']]          = $colournames[$row['Colour']].
                                                           ' ('.$row['Name'].')';
        $GAME['PlayerName_Eng'][$row['Colour']]          = $row['Name'];
        $GAME['PlayerFullName_Eng'][$row['Colour']]      = $englishcolournames[$row['Colour']].
                                                           ' ('.$row['Name'].')';
        $GAME['PossessivePronounLC_Eng'][$row['Colour']] = $pronounmap_PossessiveLowercase_Eng[$row['Pronoun']];
        $GAME['Email'][$row['Colour']]                   = $row['Email'];
        $GAME['EmailPrompt'][$row['Colour']]             = $row['EmailPrompt'];
        $GAME['EmailPromptAgain'][$row['Colour']]        = $row['EmailPromptAgain'];
        $GAME['EmailAtEnd'][$row['Colour']]              = $row['EmailAtEnd'];
        $GAME['HasBeenEmailed'][$row['Colour']]          = $row['HasBeenEmailed'];
        $GAME['AutoSort'][$row['Colour']]                = $row['AutoSort'];
        if ( isset($_SESSION) and
             $_SESSION['LoggedIn'] and
             $_SESSION['MyUserID'] == $row['UserID']
             ) {
            $GAME['MyColour'] = $row['Colour'];
            if ( $GAME['AbortVote'][$row['Colour']] ) { $GAME['IHaveAbortVoted'] = 1; }
            else                                      { $GAME['IHaveAbortVoted'] = 0; }
            if ( $GAME['KickVote'][$row['Colour']]  ) { $GAME['IHaveKickVoted'] = 1; }
            else                                      { $GAME['IHaveKickVoted'] = 0; }
        }
    }
    $GAME['PlayerMissingAndMatters']  = $GAME['PlayerMissing'];
    $GAME['PlayersMissingThatMatter'] = $GAME['PlayersMissing'];
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerMissing'][$i] and
             $GAME['RailPhase'] and
             $GAME['PlayerToMove'] != $i and
             !$GAME['HandSize'][$i]
             ) {
            $GAME['PlayersMissingThatMatter']--;
            $GAME['PlayerMissingAndMatters'][$i] = 0;
        }
    }
    $GAME['PlayersVotingToAbort']    = 0;
    $GAME['PlayersVotingToKick']     = 0;
    $GAME['AbortVoteActive']         = false;
    $GAME['KickVoteActive']          = false;
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerMissing'][$i] or $GAME['AbortVote'][$i] ) {
            $GAME['PlayersVotingToAbort']++;
        }
        if ( $GAME['PlayerMissing'][$i] or $GAME['KickVote'][$i] ) {
            $GAME['PlayersVotingToKick']++;
        }
        if ( $GAME['AbortVote'][$i] ) { $GAME['AbortVoteActive'] = true; }
        if ( $GAME['KickVote'][$i]  ) { $GAME['KickVoteActive']  = true; }
    }
    $GAME['ReplacementOffers']         = array();
    $GAME['ReplacementOffers_Users']   = array();
    $GAME['ReplacementOffers_Colours'] = array();
    if ( $GAME['PlayersMissingThatMatter'] ) {
        $QRR = dbquery( DBQUERY_READ_RESULTSET,
                        'CALL "GameGetData_GetReplacements"(:game:)',
                        'game' , $GameID
                        );
        if ( $QRR === 'NONE' ) {
            $GAME['NumReplacementOffers'] = 0;
        } else {
            $GAME['NumReplacementOffers'] = mysqli_num_rows($QRR);
            while ( $row = db_fetch_assoc($QRR) ) {
                $GAME['ReplacementOffers'][] = array( $row['User']    ,
                                                      $row['Name']    ,
                                                      $row['Colour']  ,
                                                      $row['ToCount']
                                                      );
                $GAME['ReplacementOffers_Users'][]   = $row['User'];
                $GAME['ReplacementOffers_Colours'][] = $row['Colour'];
            }
        }
    }
    if ( $GAME['CanKickVote'] ) {
        $GAME['PlayerToMoveForLongTurns'] = $GAME['PlayerUserID'][$GAME['PlayerToMove']];
    } else {
        $GAME['PlayerToMoveForLongTurns'] = 0;
    }
    $GAME['TechLevelArray'] = array( array(9, 4, 4, 4, 3, 3, 3,
                                              2, 2, 2, 1, 1, 1
                                              ),
                                     array(9, 4, 4, 3, 3, 2, 2, 1),
                                     array(9, 4, 3, 2, 1),
                                     array(9, 4, 4, 3, 3, 2, 2, 1, 1),
                                     array(9, 2, 2, 1, 1, 0, 0)
                                     );
    $GAME['incomeamounts'] = array( -10, -9, -8, -7, -6, -5, -4, -3, -2, -1,
                                      0,  1,  1,  2,  2,  3,  3,  4,  4,  5,
                                      5,  6,  6,  7,  7,  8,  8,  9,  9, 10,
                                     10, 11, 11, 11, 12, 12, 12, 13, 13, 13,
                                     14, 14, 14, 15, 15, 15, 16, 16, 16, 17,
                                     17, 17, 18, 18, 18, 19, 19, 19, 20, 20,
                                     20, 21, 21, 21, 21, 22, 22, 22, 22, 23,
                                     23, 23, 23, 24, 24, 24, 24, 25, 25, 25,
                                     25, 26, 26, 26, 26, 27, 27, 27, 27, 28,
                                     28, 28, 28, 29, 29, 29, 29, 30, 30, 30
                                     );
    $GAME['TileVPValue'] = array( array( 3,  5, 9, 12),
                                  array( 1,  2, 3,  4),
                                  array( 3,  5, 7,  9),
                                  array( 2,  4, 6,  9),
                                  array(10, 18       )
                                  );
    $GAME['TileIncomeValue'] = array( array(5, 4, 3, 2),
                                      array(4, 7, 6, 5),
                                      array(3, 3, 2, 1),
                                      array(3, 3, 4, 4),
                                      array(2, 1      )
                                      );
    $GAME['TileInitialCubes'] = array( array(2, 3, 4, 5),
                                       array(4, 4, 5, 6)
                                       );
    $GAME['TileCosts'] = array( array(12, 14, 16, 18),
                                array( 5,  7,  8, 10),
                                array( 5,  7,  9, 12),
                                array( 6,  7,  8,  9),
                                array(16, 25        )
                                );
    $GAME['TileRequireCoal'] = array( array(0, 1, 1, 1),
                                      array(0, 0, 0, 0),
                                      array(1, 1, 1, 1),
                                      array(0, 0, 0, 0),
                                      array(1, 1      )
                                      );
    $GAME['TileRequireIron'] = array( array(0, 0, 1, 1),
                                      array(0, 0, 1, 1),
                                      array(0, 0, 0, 0),
                                      array(0, 0, 0, 0),
                                      array(1, 1      )
                                      );
    dbquery(DBQUERY_START_TRANSACTION);
    return $GAME;
}

function dbformatgamedata() {
    global $GAME, $unexpectederrormessage;
    if ( strlen($GAME['LinkStatus']) % 2 ) {
        $GAME['LinkStatus'] .= '9';
    }
    if ( strlen($GAME['SpaceStatus']) % 2 ) {
        $GAME['SpaceStatus'] .= '9';
        $GAME['SpaceTile']   .= '0';
        $GAME['TechLevels']  .= '0';
        $GAME['SpaceCubes']  .= '1';
    }
    for ($i=0; $i<6; $i++) {
        $GAME['RemainingTiles'][$i] =
            implode('', array_map('dechex', $GAME['RemainingTiles'][$i]));
    }
    $GAME['RemainingTiles'] = implode('', $GAME['RemainingTiles']);
    $PlayerToMoveID = null;
    if ( $GAME['GameStatus'] == 'Finished' or
         $GAME['GameStatus'] == 'Aborted'
         ) {
        $GAME['GameIsFinished']   = 1;
        $GAME['Cards']            = 0;
        $GAME['ShuffledDeck']     = 0;
        $GAME['DiscardPile']      = 0;
        $GAME['CoalNet']          = 0;
        $GAME['PlayerToMoveName'] = 0;
        $GAME['SpecialModes']     = 0;
        dbquery( DBQUERY_WRITE,
                 'DELETE FROM "GameInProgress" WHERE "Game" = :game:',
                 'game' , $GAME['GameID']
                 );
        dbquery( DBQUERY_WRITE,
                 'DELETE FROM "ReplacementOffer" WHERE "Game" = :game:',
                 'game' , $GAME['GameID']
                 );
        $GRR = 0;
    } else {
        for ($i=0; $i<MAX_PLAYERS; $i++) {
            $GAME['Cards'][$i] = implode('|', $GAME['Cards'][$i]);
        }
        $GAME['Cards']        = implode(':', $GAME['Cards']);
        $GAME['ShuffledDeck'] = implode('|', $GAME['ShuffledDeck']);
        $GAME['DiscardPile']  = implode('|', $GAME['DiscardPile']);
        $GAME['CoalNet']      = implode('|', $GAME['CoalNet']);
        if ( $GAME['GameStatus'] == 'In Progress' ) {
            $GAME['PlayerToMoveName'] = $GAME['PlayerName'][$GAME['PlayerToMove']];
            $PlayerToMoveID = $GAME['PlayerUserID'][$GAME['PlayerToMove']];
            $GRR = 0;
        } else {
            $GAME['PlayerToMoveName'] = 'Missing';
            $GRR = 1;
        }
        if      ( $GAME['DebtMode'] )            { $GAME['SpecialModes'] = 1; }
        else if ( $GAME['ContinueSellingMode'] ) { $GAME['SpecialModes'] = 2; }
        else if ( $GAME['SecondRailMode'] )      { $GAME['SpecialModes'] = 3; }
        else if ( $GAME['SecondDevelopMode'] )   { $GAME['SpecialModes'] = 4; }
        else                                     { $GAME['SpecialModes'] = 0; }
    }
    $GAME['Money']               = implode('|', $GAME['Money']);
    $GAME['VictoryPoints']       = implode('|', $GAME['VictoryPoints']);
    $GAME['IncomeSpace']         = implode('|', $GAME['IncomeSpace']);
    $GAME['AmountSpent']         = implode('|', $GAME['AmountSpent']);
    if ( $GAME['MoveMade'] ) {
        if ( $GAME['GameIsFinished'] ) {
            $LastMoveSQL = ', "LastMove" = UTC_TIMESTAMP()';
        } else {
            $LastMoveSQL = ', "Game"."LastMove" = UTC_TIMESTAMP(), "GameInProgress"."GIPLastMove" = UTC_TIMESTAMP()';
        }
        $GAME['NumMovesMade']++;
        $GAME['KickVote'] = '00000';
        if ( $GAME['PlayerToMoveForLongTurns'] ) {
            dbquery( DBQUERY_WRITE,
                     'UPDATE "PlayerGameRcd" SET "NumLongTurns" = "NumLongTurns" + 1 WHERE "Game" = :game: AND "User" = :user:',
                     'game' , $GAME['GameID']                   ,
                     'user' , $GAME['PlayerToMoveForLongTurns']
                     );
        }
    } else {
        $LastMoveSQL = '';
    }
    if ( $GAME['NarrativeTicker'] ) {
        $GameTickerSQL      = 'NULL';
        $GameTickerNamesSQL = 'NULL';
    } else {
        $GameTickerSQL      = 'CONCAT("Game"."GameTicker", :gameticker:)';
        $GameTickerNamesSQL = 'CONCAT("Game"."GameTickerNames", :gametickernames:)';
    }
    if ( $GAME['GameIsFinished'] ) {
        $query = 'UPDATE "Game" SET "EffectiveNumPlayers" = :effectivenumplayers:, "ModularBoardParts" = :modularboardparts:, "GameTicker" = '.$GameTickerSQL.', "GameTickerNames" = '.$GameTickerNamesSQL.', "Round" = :round:, "NumRounds" = :numrounds:, "RailPhase" = :railphase:, "GameIsFinished" = :gameisfinished:, "CurrentPlayers" = :currentplayers:, "CottonDemand" = :cottondemand:, "CoalDemand" = :coaldemand:, "IronDemand" = :irondemand:, "RemainingTiles" = :remainingtiles:, "LinkStatus" = UNHEX(:linkstatus:), "SpaceStatus" = UNHEX(:spacestatus:), "TechLevels" = UNHEX(:techlevels:), "SpaceCubes" = UNHEX(:spacecubes:), "SpaceTile" = UNHEX(:spacetile:), "Money" = :money:, "VictoryPoints" = :victorypoints:, "IncomeSpace" = :incomespace:, "AmountSpent" = :amountspent:, "TurnOrder" = :turnorder:, "PlayerExists" = :playerexists:, "GameStatus" = :gamestatus:, "RandomLog" = :randomlog:'.$LastMoveSQL.' WHERE "GameID" = :gameid:';
    } else {
        $query = 'UPDATE "Game" JOIN "GameInProgress" ON "Game"."GameID" = "GameInProgress"."Game" SET "Game"."EffectiveNumPlayers" = :effectivenumplayers:, "Game"."ModularBoardParts" = :modularboardparts:, "Game"."GameTicker" = '.$GameTickerSQL.', "Game"."GameTickerNames" = '.$GameTickerNamesSQL.', "Game"."Round" = :round:, "Game"."NumRounds" = :numrounds:, "Game"."RailPhase" = :railphase:, "Game"."GameIsFinished" = :gameisfinished:, "Game"."CurrentPlayers" = :currentplayers:, "Game"."CottonDemand" = :cottondemand:, "Game"."CoalDemand" = :coaldemand:, "Game"."IronDemand" = :irondemand:, "Game"."RemainingTiles" = :remainingtiles:, "Game"."LinkStatus" = UNHEX(:linkstatus:), "Game"."SpaceStatus" = UNHEX(:spacestatus:), "Game"."TechLevels" = UNHEX(:techlevels:), "Game"."SpaceCubes" = UNHEX(:spacecubes:), "Game"."SpaceTile" = UNHEX(:spacetile:), "Game"."Money" = :money:, "Game"."VictoryPoints" = :victorypoints:, "Game"."IncomeSpace" = :incomespace:, "Game"."AmountSpent" = :amountspent:, "Game"."TurnOrder" = :turnorder:, "Game"."PlayerExists" = :playerexists:, "Game"."GameStatus" = :gamestatus:, "Game"."RandomLog" = :randomlog:'.$LastMoveSQL.$GAME['LastEventSQL'].', "GameInProgress"."NumMovesMade" = :nummovesmade:, "GameInProgress"."NumDepartures" = :numdepartures:, "GameInProgress"."AbortVote" = :abortvote:, "GameInProgress"."KickVote" = :kickvote:, "GameInProgress"."SpecialModes" = :specialmodes:, "GameInProgress"."PlayerToMove" = :playertomove:, "GameInProgress"."PlayerToMoveName" = :playertomovename:, "GameInProgress"."PlayerToMoveID" = :playertomoveid:, "GameInProgress"."CoalNet" = :coalnet:, "GameInProgress"."HasPort" = :hasport:, "GameInProgress"."ShuffledDeck" = :shuffleddeck:, "GameInProgress"."Cards" = :cards:, "GameInProgress"."DiscardPile" = :discardpile:, "GameInProgress"."ShuffledTiles" = :shuffledtiles:, "GameInProgress"."TilesDrawn" = :tilesdrawn:, "GameInProgress"."GameRecruitingReplacement" = :grr: WHERE "Game"."GameID" = :gameid:';
    }
    dbquery( DBQUERY_WRITE,
             $query,
             'effectivenumplayers' , $GAME['EffectiveNumPlayers'] ,
             'modularboardparts'   , $GAME['ModularBoardParts']   ,
             'gameticker'          , $GAME['AltGameTicker']       ,
             'gametickernames'     , $GAME['GameTickerNames']     ,
             'round'               , $GAME['Round']               ,
             'numrounds'           , $GAME['NumRounds']           ,
             'railphase'           , $GAME['RailPhase']           ,
             'gameisfinished'      , $GAME['GameIsFinished']      ,
             'currentplayers'      , $GAME['CurrentPlayers']      ,
             'cottondemand'        , $GAME['CottonDemand']        ,
             'coaldemand'          , $GAME['CoalDemand']          ,
             'irondemand'          , $GAME['IronDemand']          ,
             'remainingtiles'      , $GAME['RemainingTiles']      ,
             'linkstatus'          , $GAME['LinkStatus']          ,
             'spacestatus'         , $GAME['SpaceStatus']         ,
             'techlevels'          , $GAME['TechLevels']          ,
             'spacecubes'          , $GAME['SpaceCubes']          ,
             'spacetile'           , $GAME['SpaceTile']           ,
             'money'               , $GAME['Money']               ,
             'victorypoints'       , $GAME['VictoryPoints']       ,
             'incomespace'         , $GAME['IncomeSpace']         ,
             'amountspent'         , $GAME['AmountSpent']         ,
             'turnorder'           , $GAME['TurnOrder']           ,
             'playerexists'        , $GAME['PlayerExists']        ,
             'gamestatus'          , $GAME['GameStatus']          ,
             'randomlog'           , $GAME['RandomLog']           ,
             'nummovesmade'        , $GAME['NumMovesMade']        ,
             'numdepartures'       , $GAME['NumDepartures']       ,
             'abortvote'           , $GAME['AbortVote']           ,
             'kickvote'            , $GAME['KickVote']            ,
             'specialmodes'        , $GAME['SpecialModes']        ,
             'playertomove'        , $GAME['PlayerToMove']        ,
             'playertomovename'    , $GAME['PlayerToMoveName']    ,
             'playertomoveid'      , $PlayerToMoveID              ,
             'coalnet'             , $GAME['CoalNet']             ,
             'hasport'             , $GAME['HasPort']             ,
             'shuffleddeck'        , $GAME['ShuffledDeck']        ,
             'cards'               , $GAME['Cards']               ,
             'discardpile'         , $GAME['DiscardPile']         ,
             'shuffledtiles'       , $GAME['ShuffledTiles']       ,
             'tilesdrawn'          , $GAME['TilesDrawn']          ,
             'gameid'              , $GAME['GameID']              ,
             'grr'                 , $GRR
             );
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['MoveMadeByPlayer'][$i] ) {
            dbquery( DBQUERY_WRITE,
                     'UPDATE "User" SET "LastMove" = UTC_TIMESTAMP() WHERE "UserID" = :userid:',
                     'userid' , $GAME['PlayerUserID'][$i]
                     );
        }
    }
    dbquery(DBQUERY_COMMIT);
    if ( $GAME['MoveMade'] ) {
        file_put_contents( NUM_MOVES_MADE_DIR.
                               'g'.
                               $GAME['GameID'].
                               '.txt',
                           $GAME['NumMovesMade']
                           );
    }
    if ( $GAME['GameIsFinished'] ) {
        dbquery( DBQUERY_WRITE,
                 'UPDATE "Metadatum" SET "MetadatumValue" = "MetadatumValue" + 1 WHERE "MetadatumName" = \'Games-Finished\''
                 );
        dbquery( DBQUERY_WRITE,
                 'UPDATE "Metadatum" SET "MetadatumValue" = "MetadatumValue" - 1 WHERE "MetadatumName" = \'Games-InProgress\''
                 );
    }
}

?>