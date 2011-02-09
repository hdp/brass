<?php

if ( !defined('MAX_PLAYERS') ) {
    define('MAX_PLAYERS', 5);
}
if ( !defined('PERMITTED_DEPARTURES') ) {
    define('PERMITTED_DEPARTURES', 2);
}

function gamegetdata_board_see ($GameID) {
    $GAME = dbquery( DBQUERY_READ_SINGLEROW,
                     'CALL "GameGetData_Game_Display"(:gameid:)',
                     'gameid' , $GameID
                     );
    $QRP = dbquery( DBQUERY_READ_RESULTSET,
                    'CALL "GameGetData_User_Display"(:gameid:)',
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
    $colournames = array( transtext('_colourRed'),
                          transtext('_colourYellow'),
                          transtext('_colourGreen'),
                          transtext('_colourPurple'),
                          transtext('_colourGrey')
                          );
    $pronounmap_BasicUppercase = array( 'He'  => transtext('^pronounHeCap'),
                                        'She' => transtext('^pronounSheCap'),
                                        'It'  => transtext('^pronounItCap')
                                        );
    $pronounmap_BasicLowercase = array( 'He'  => transtext('^pronounHe'),
                                        'She' => transtext('^pronounShe'),
                                        'It'  => transtext('^pronounIt')
                                        );
     // $pronounmap_PossessiveUppercase = array( 'He'  => transtext('^pronounHisPoCap'),
     //                                          'She' => transtext('^pronounHerPoCap'),
     //                                          'It'  => transtext('^pronounItsPoCap')
     //                                          );
     // Not currently needed
    $pronounmap_PossessiveLowercase = array( 'He'  => transtext('^pronounHisPo'),
                                             'She' => transtext('^pronounHerPo'),
                                             'It'  => transtext('^pronounItsPo')
                                             );
    $pronounmap_IndirectObjectLowercase = array( 'He'  => transtext('^pronounHimIO'),
                                                 'She' => transtext('^pronounHerIO'),
                                                 'It'  => transtext('^pronounItIO')
                                                 );
    $GAME['VersionName'] = vname($GAME['VersionName'], $GAME['VersionNameSuffix']);
    if ( $GAME['GTitleDeletedByAdmin'] ) {
        $GAME['GameName_Title'] = transtext('_GameTitleHidden');
        if ( $Administrator ) {
            $GAME['GameName_Page'] = '<b>'.
                                     $GAME['GameName'].
                                     '</b> (Title hidden to non-Administrators)';
        } else {
            $GAME['GameName_Page'] = '<b>'.
                                     transtext('_GameTitleHidden').
                                     '</b>';
        }
    } else {
        $GAME['GameName_Title'] = $GAME['GameName'];
        $GAME['GameName_Page'] = '<b>'.$GAME['GameName'].'</b>';
    }
    $GAME['locationnames']          = explode('|', $GAME['LocationNames']);
    $GAME['spacetowns']             = explode('|', $GAME['SpaceTowns']);
    $GAME['tileindustries']         = explode('|', $GAME['TileIndustries']);
    $GAME['CanalStarts']            = explode('|', $GAME['CanalStarts']);
    $GAME['CanalEnds']              = explode('|', $GAME['CanalEnds']);
    $GAME['RailStarts']             = explode('|', $GAME['RailStarts']);
    $GAME['RailEnds']               = explode('|', $GAME['RailEnds']);
    $GAME['CanalAlwaysExists']      = explode('|', $GAME['CanalAlwaysExists']);
    $GAME['CanalExistenceArray']    = explode('|', $GAME['CanalExistenceArray']);
    $GAME['RailAlwaysExists']       = explode('|', $GAME['RailAlwaysExists']);
    $GAME['RailExistenceArray']     = explode('|', $GAME['RailExistenceArray']);
    $GAME['SpaceAlwaysExists']      = explode('|', $GAME['SpaceAlwaysExists']);
    $GAME['SpaceExistenceArray']    = explode('|', $GAME['SpaceExistenceArray']);
    $GAME['carddetailarray']        = explode('|', $GAME['CardDetailArray']);
    $GAME['carddetailarrayb']       = explode('|', $GAME['CardDetailArrayB']);
    $GAME['SpaceNumbersNotOrdinal'] = explode('|', $GAME['SpaceNumbers']);
    $GAME['spacenumbers']           = explode('|', $GAME['SpaceOrdinals']);
    $GAME['LocationAutoValue']      = explode('|', $GAME['LocationAutoValue']);
    $GAME['CanalExistenceArray']    = array_map( 'intval',
                                                 $GAME['CanalExistenceArray']
                                                 );
    $GAME['RailExistenceArray']     = array_map( 'intval',
                                                 $GAME['RailExistenceArray']
                                                 );
    $GAME['SpaceExistenceArray']    = array_map( 'intval',
                                                 $GAME['SpaceExistenceArray']
                                                 );
    $GAME['ModularBoardParts'] = (int)$GAME['ModularBoardParts'];
    $GAME['SpecialRules']      = (int)$GAME['SpecialRules'];
    if ( is_null($GAME['TileXPositionsPretty']) ) {
        $GAME['TileXPositions'] =
            array(null, explode('|', $GAME['TileXPositionsCompact']));
        $GAME['TileYPositions'] =
            array(null, explode('|', $GAME['TileYPositionsCompact']));
    } else {
        $GAME['TileXPositions'] =
            array( explode('|', $GAME['TileXPositionsPretty']),
                   explode('|', $GAME['TileXPositionsCompact'])
                   );
        $GAME['TileYPositions'] =
            array( explode('|', $GAME['TileYPositionsPretty']),
                   explode('|', $GAME['TileYPositionsCompact'])
                   );
    }
    if ( $GAME['RailPhase'] ) {
        if ( is_null($GAME['RailXPositionsPretty']) ) {
            $GAME['LinkDotXPositions'] =
                array(null, explode('|', $GAME['RailXPositionsCompact']));
            $GAME['LinkDotYPositions'] =
                array(null, explode('|', $GAME['RailYPositionsCompact']));
        } else {
            $GAME['LinkDotXPositions'] =
                array( explode('|', $GAME['RailXPositionsPretty']),
                       explode('|', $GAME['RailXPositionsCompact'])
                       );
            $GAME['LinkDotYPositions'] =
                array( explode('|', $GAME['RailYPositionsPretty']),
                       explode('|', $GAME['RailYPositionsCompact'])
                       );
        }
    } else {
        if ( is_null($GAME['CanalXPositionsPretty']) ) {
            $GAME['LinkDotXPositions'] =
                array(null, explode('|', $GAME['CanalXPositionsCompact']));
            $GAME['LinkDotYPositions'] =
                array(null, explode('|', $GAME['CanalYPositionsCompact']));
        } else {
            $GAME['LinkDotXPositions'] =
                array( explode('|', $GAME['CanalXPositionsPretty']),
                       explode('|', $GAME['CanalXPositionsCompact'])
                       );
            $GAME['LinkDotYPositions'] =
                array( explode('|', $GAME['CanalYPositionsPretty']),
                       explode('|', $GAME['CanalYPositionsCompact'])
                       );
        }
    }
    $GAME['NumTowns']          = count($GAME['locationnames']);
    $GAME['NumIndustrySpaces'] = count($GAME['spacetowns']);
    $GAME['NumCanalLinks']     = count($GAME['CanalStarts']);
    $GAME['NumRailLinks']      = count($GAME['RailEnds']);
    $NumLinks = $GAME['RailPhase'] ?
                $GAME['NumRailLinks'] :
                $GAME['NumCanalLinks'];
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
    $GAME['Money']          = explode('|', $GAME['Money']);
    $GAME['AmountSpent']    = explode('|', $GAME['AmountSpent']);
    $GAME['VictoryPoints']  = explode('|', $GAME['VictoryPoints']);
    $GAME['IncomeSpace']    = explode('|', $GAME['IncomeSpace']);
    $GAME['RemainingTiles'] = str_split($GAME['RemainingTiles'], MAX_PLAYERS);
    for ($i=0; $i<6; $i++) {
        $GAME['RemainingTiles'][$i] =
            array_map('hexdec', str_split($GAME['RemainingTiles'][$i]));
    }
    if ( is_null($GAME['Cards']) or $GAME['Cards'] == '' ) {
        $GAME['Cards'] = array(array(), array(), array(), array(), array());
        $GAME['HandSize'] = array(0, 0, 0, 0, 0);
    } else {
        $GAME['Cards'] = explode(':', $GAME['Cards']);
        for ($i=0;$i<MAX_PLAYERS;$i++) {
            $GAME['Cards'][$i] = explode('|', $GAME['Cards'][$i]);
            $GAME['HandSize'][$i] = count($GAME['Cards'][$i]);
            if ( $GAME['HandSize'][$i] == 1 and
                 $GAME['Cards'][$i][0] == ''
                 ) {
                $GAME['Cards'][$i]    = array();
                $GAME['HandSize'][$i] = 0;
            }
        }
    }
    $GAME['CanTakeLoans'] = 1;
    if ( $GAME['GameIsFinished'] or
         $GAME['IncomeSpace'][$GAME['PlayerToMove']] == 0 or
         ( $GAME['RailPhase'] and $GAME['NumRounds'] - $GAME['Round'] < 4 )
         ) {
        $GAME['CanTakeLoans'] = 0;
    }
    if ( $GAME['TilesDrawn'] == 'None' ) {
        $GAME['NumberOfTilesDrawn'] = 0;
    } else {
        $GAME['NumberOfTilesDrawn'] = strlen($GAME['TilesDrawn']);
    }
    if ( $GAME['GameStatus'] == 'In Progress' and
         time() - strtotime($GAME['LastMove']) > 60 * $GAME['TimeLimitA'] and
         !MAINTENANCE_DISABLED
         ) {
        $GAME['CanKickVote'] = 1;
    } else {
        $GAME['CanKickVote'] = 0;
    }
    if ( $GAME['TimeLimitA'] % 1440 == 0 ) {
        $GAME['TimeLimitA'] /= 1440;
        if ( $GAME['TimeLimitA'] == 1 ) {
            $GAME['TimeLimitAunit'] = transtext('_timeDay');
        } else {
            $GAME['TimeLimitAunit'] = transtext('_timeDayPl');
        }
    } else if ( $GAME['TimeLimitA'] % 60 == 0 ) {
        $GAME['TimeLimitA'] /= 60;
        if ( $GAME['TimeLimitA'] == 1 ) {
            $GAME['TimeLimitAunit'] = transtext('_timeHour');
            } else {
                $GAME['TimeLimitAunit'] = transtext('_timeHourPl');
            }
    } else {
        $GAME['TimeLimitAunit'] = transtext('_timeMinutePl');
    }
    if ( $GAME['TimeLimitB'] % 1440 == 0 ) {
        $GAME['TimeLimitB'] /= 1440;
        if ( $GAME['TimeLimitB'] == 1 ) {
            $GAME['TimeLimitBunit'] = transtext('_timeDay');
        } else {
            $GAME['TimeLimitBunit'] = transtext('_timeDayPl');
        }
    } else if ( $GAME['TimeLimitB'] % 60 == 0 ) {
        $GAME['TimeLimitB'] /= 60;
        if ( $GAME['TimeLimitB'] == 1 ) {
            $GAME['TimeLimitBunit'] = transtext('_timeHour');
        } else {
            $GAME['TimeLimitBunit'] = transtext('_timeHourPl');
        }
    } else {
        $GAME['TimeLimitBunit'] = transtext('_timeMinutePl');
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
    $GAME['HasBuilt'] = array(0,0,0,0,0);
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( strpos($GAME['SpaceStatus'],$i) !== false or
             strpos($GAME['LinkStatus'],$i) !== false
             ) {
            $GAME['HasBuilt'][$i] = 1;
        }
    }
    if ( $GAME['GVersion'] == 2 ) {
        $GAME['NarrativeTicker'] = 2;
    } else if ( is_null($GAME['GameTicker']) ) {
        $GAME['NarrativeTicker'] = 1;
    } else {
        $GAME['NarrativeTicker'] = 0;
    }
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $GAME['PlayerName'][$i]     = transtext('_PlayerBoxAbsent');
        $GAME['PlayerFullName'][$i] = $colournames[$i].
                                      ' ('.$GAME['PlayerName'][$i].')';
    }
    $GAME['MyColour'] = 50;
    $GAME['PlayersMissing'] = (int)$GAME['CurrentPlayers'];
    $GAME['PlayerMissing'] = $GAME['PlayerExists'];
    if ( is_null($GAME['AbortVote']) or $GAME['AbortVote'] == '' ) {
        $GAME['AbortVote'] = '00000';
    }
    if ( is_null($GAME['KickVote']) or $GAME['KickVote'] == '' ) {
        $GAME['KickVote'] = '00000';
    }
    if ( $GAME['GameStatus'] == 'In Progress' and
         strpos($GAME['KickVote'],'1') !== false
         ) {
        $GAME['CanKickVote'] = 1;
    }
    while ( $row = db_fetch_assoc($QRP) ) {
        $GAME['PlayersMissing']--;
        $GAME['PlayerMissing'][$row['Colour']]       = 0;
        $GAME['PlayerUserID'][$row['Colour']]        = $row['UserID'];
        $GAME['PlayerName'][$row['Colour']]          = $row['Name'];
        $GAME['PlayerFullName'][$row['Colour']]      = $colournames[$row['Colour']].
                                                       ' ('.$row['Name'].')';
        $GAME['Pronoun'][$row['Colour']]             = $pronounmap_BasicUppercase[$row['Pronoun']];
        $GAME['PronounLC'][$row['Colour']]           = $pronounmap_BasicLowercase[$row['Pronoun']];
        $GAME['PossessivePronounLC'][$row['Colour']] = $pronounmap_PossessiveLowercase[$row['Pronoun']];
        $GAME['OtherPronounLC'][$row['Colour']]      = $pronounmap_IndirectObjectLowercase[$row['Pronoun']];
        $GAME['Notes'][$row['Colour']]               = $row['Notes'];
        if ( $_SESSION['LoggedIn'] and
             $_SESSION['MyUserID'] == $row['UserID']
             ) {
            $GAME['MyColour'] = $row['Colour'];
            if ( $GAME['AbortVote'][$row['Colour']] ) {
                $GAME['IHaveAbortVoted'] = 1;
            } else {
                $GAME['IHaveAbortVoted'] = 0;
            }
            if ( $GAME['KickVote'][$row['Colour']] ) {
                $GAME['IHaveKickVoted'] = 1;
            } else {
                $GAME['IHaveKickVoted'] = 0;
            }
        }
    }
    $GAME['PlayerMissingAndMatters']  = $GAME['PlayerMissing'];
    $GAME['PlayersMissingThatMatter'] = $GAME['PlayersMissing'];
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        if ( $GAME['PlayerMissing'][$i] and
             $GAME['RailPhase'] and
             $GAME['PlayerToMove'] != $i and
             !$GAME['HandSize'][$i]
             ) {
            $GAME['PlayersMissingThatMatter']--;
            $GAME['PlayerMissingAndMatters'][$i] = 0;
        }
    }
    $GAME['PlayersVotingToAbort'] = 0;
    $GAME['PlayersVotingToKick']  = 0;
    $GAME['AbortVoteActive']      = false;
    $GAME['KickVoteActive']       = false;
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        if ( $GAME['PlayerMissing'][$i] or $GAME['AbortVote'][$i] ) {
            $GAME['PlayersVotingToAbort']++;
            }
        if ( $GAME['PlayerMissing'][$i] or $GAME['KickVote'][$i] ) {
            $GAME['PlayersVotingToKick']++;
        }
        if ( $GAME['AbortVote'][$i] ) { $GAME['AbortVoteActive'] = true; }
        if ( $GAME['KickVote'][$i]  ) { $GAME['KickVoteActive']  = true; }
    }
    if ( $GAME['PlayersMissingThatMatter'] ) {
        $GAME['ReplacementOffers']         = array();
        $GAME['ReplacementOffers_Users']   = array();
        $GAME['ReplacementOffers_Colours'] = array();
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
                                                      $row['Rating']  ,
                                                      $row['ToCount']
                                                      );
                $GAME['ReplacementOffers_Users'][]   = $row['User'];
                $GAME['ReplacementOffers_Colours'][] = $row['Colour'];
            }
        }
    }
    return $GAME;
}

?>