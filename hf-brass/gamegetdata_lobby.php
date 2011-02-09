<?php

if ( !defined('MAX_PLAYERS') ) { define('MAX_PLAYERS', 5); }

function letter_end_number ($x) {
    $x = (int)$x;
    $letters = 'ABCDEFGHIJ';
    return substr($x,0,-1).$letters[substr($x,-1)];
}

function gamegetdata_lobby ($GameID, $DoingWork) {
    global $Administrator;
    $GAME = dbquery( DBQUERY_READ_SINGLEROW,
                     'CALL "GameGetData_Lobby_DisplayOrModify"(:gameid:)',
                     'gameid' , $GameID
                     );
    $QRP = dbquery( DBQUERY_READ_RESULTSET,
                    'CALL "GameGetData_User_Modify"(:gameid:)',
                    'gameid' , $GameID
                    );
    if ( $GAME === 'NONE' ) { return false; }
    if ( $GAME['GameStatus'] == 'Cancelled' and !$Administrator ) { return false; }
    if ( $GAME['GameStatus'] != 'Recruiting' and $GAME['GameStatus'] != 'Cancelled' ) { return 'WRONG PAGE'; }
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
    $GAME['GameID'] = $GameID;
    $GAME['SpecialRules'] = (int)$GAME['SpecialRules'];
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        $GAME['PlayerName'][$i]     = '';
        $GAME['PlayerFullName'][$i] = '';
    }
    $GAME['MyColour'] = 50;
    while ( $row = db_fetch_assoc($QRP) ) {
        $GAME['PlayerUserID'][$row['Colour']]        = $row['UserID'];
        $GAME['PlayerName'][$row['Colour']]          = $row['Name'];
        $GAME['PlayerFullName'][$row['Colour']]      = $colournames[$row['Colour']].' ('.$row['Name'].')';
        $GAME['Pronoun_Eng'][$row['Colour']]         = $row['Pronoun'];
        $GAME['PlayerRating'][$row['Colour']]        = $row['Rating'];
        $GAME['Pronoun'][$row['Colour']]             = $pronounmap_BasicUppercase[$row['Pronoun']];
        $GAME['PronounLC'][$row['Colour']]           = $pronounmap_BasicLowercase[$row['Pronoun']];
        $GAME['PossessivePronounLC'][$row['Colour']] = $pronounmap_PossessiveLowercase[$row['Pronoun']];
        $GAME['OtherPronounLC'][$row['Colour']]      = $pronounmap_IndirectObjectLowercase[$row['Pronoun']];
        $GAME['Email'][$row['Colour']]               = $row['Email'];
        $GAME['EmailPrompt'][$row['Colour']]         = $row['EmailPrompt'];
        $GAME['EmailPromptAgain'][$row['Colour']]    = $row['EmailPromptAgain'];
        $GAME['HasBeenEmailed'][$row['Colour']]      = $row['HasBeenEmailed'];
        if ( $_SESSION['LoggedIn'] and $_SESSION['MyUserID'] == $row['UserID'] ) {
            $GAME['MyColour'] = $row['Colour'];
        }
    }
    $GAME['TimeLimitA_Minutes'] = $GAME['TimeLimitA'];
    $GAME['TimeLimitB_Minutes'] = $GAME['TimeLimitB'];
    if ( $DoingWork ) {
        if ( is_null($GAME['CardsToRemove']) ) {
            $GAME['CardsToRemove'] = array();
        } else {
            $GAME['CardsToRemove'] = explode('|',$GAME['CardsToRemove']);
        }
        $GAME['SpaceAlwaysExists'] = explode('|',$GAME['SpaceAlwaysExists']);
        $GAME['CanalAlwaysExists'] = explode('|',$GAME['CanalAlwaysExists']);
        $GAME['carddetailarrayb']  = explode('|',$GAME['CardDetailArrayB']);
        $GAME['ExternalLocations'] = explode('|',$GAME['ExternalLocations']);
        $GAME['NumTowns']          = count($GAME['ExternalLocations']);
        $GAME['NumIndustrySpaces'] = count($GAME['SpaceAlwaysExists']);
        $GAME['NumCanalLinks']     = count($GAME['CanalAlwaysExists']);
        dbquery(DBQUERY_START_TRANSACTION);
    } else {
        $GAME['VersionName'] = vname($GAME['VersionName'], $GAME['VersionNameSuffix']);
        if ( $GAME['GTitleDeletedByAdmin'] ) {
            $GAME['GameName_Title'] = transtext('_GameTitleHidden');
            if ( $Administrator ) { $GAME['GameName_Page'] = '<b>'.$GAME['GameName'].'</b> (Title hidden to non-Administrators)'; }
            else                  { $GAME['GameName_Page'] = '<b>'.transtext('_GameTitleHidden').'</b>'; }
        } else {
            $GAME['GameName_Title'] = $GAME['GameName'];
            $GAME['GameName_Page'] = '<b>'.$GAME['GameName'].'</b>';
        }
        if ( $GAME['TimeLimitA'] % 1440 == 0 ) {
            $GAME['TimeLimitA'] /= 1440;
            if ( $GAME['TimeLimitA'] == 1 ) { $GAME['TimeLimitAunit'] = transtext('_timeDay');   }
            else                            { $GAME['TimeLimitAunit'] = transtext('_timeDayPl'); }
        } else if ( $GAME['TimeLimitA'] % 60 == 0 ) {
            $GAME['TimeLimitA'] /= 60;
            if ( $GAME['TimeLimitA'] == 1 ) { $GAME['TimeLimitAunit'] = transtext('_timeHour');   }
            else                            { $GAME['TimeLimitAunit'] = transtext('_timeHourPl'); }
        } else {
            $GAME['TimeLimitAunit'] = transtext('_timeMinutePl');
        }
        if ( $GAME['TimeLimitB'] % 1440 == 0 ) {
            $GAME['TimeLimitB'] /= 1440;
            if ( $GAME['TimeLimitB'] == 1 ) { $GAME['TimeLimitBunit'] = transtext('_timeDay');   }
            else                            { $GAME['TimeLimitBunit'] = transtext('_timeDayPl'); }
        } else if ( $GAME['TimeLimitB'] % 60 == 0 ) {
            $GAME['TimeLimitB'] /= 60;
            if ( $GAME['TimeLimitB'] == 1 ) { $GAME['TimeLimitBunit'] = transtext('_timeHour');   }
            else                            { $GAME['TimeLimitBunit'] = transtext('_timeHourPl'); }
        } else {
            $GAME['TimeLimitBunit'] = transtext('_timeMinutePl');
        }
    }
    return $GAME;
}

?>