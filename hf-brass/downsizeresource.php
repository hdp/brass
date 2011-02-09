<?php

function downsizegame($downsizetype) {
    global $GAME;
    $GAME['MoveMade'] = 1;
    $GAME['KickVote'] = '00000';
    $GAME['SecondRailMode']      = 0;
    $GAME['SecondDevelopMode']   = 0;
    $GAME['ContinueSellingMode'] = 0;
    if ( $GAME['GameStatus'] == 'In Progress' ) {
        if ( KickPlayer($GAME['PlayerToMove'],3) ) { return; }
            // If KickPlayer aborts the game then it will return true.
            // We don't want any shenanigans going on after a game is aborted.
    }
    $GAME['PlayersMissing']--;
    $GAME['PlayerMissing'][$GAME['PlayerToMove']] = 0;
    $GAME['PlayersMissingThatMatter']--;
    $GAME['PlayerMissingAndMatters'][$GAME['PlayerToMove']] = 0;
    dbquery( DBQUERY_WRITE,
             'DELETE FROM "ReplacementOffer" WHERE "Game" = :game: AND "Colour" = :colour:',
             'game'   , $GAME['GameID']       ,
             'colour' , $GAME['PlayerToMove']
             );
    $GAME['GameStatus'] = 'In Progress';
    if ( $downsizetype ) { $GAME['AltGameTicker'] .= '7A'.callmovetimediff(); }
    else                 { $GAME['AltGameTicker'] .= '7B'.callmovetimediff(); }
    $GAME['PlayerExists'][$GAME['PlayerToMove']] = 0;
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        if ( $GAME['SpaceStatus'][$i] == $GAME['PlayerToMove'] ) {
            $GAME['SpaceStatus'][$i] = 8;
        }
    }
    for ($i=0; $i<$GAME['RailPhase']*$GAME['NumRailLinks']+(1-$GAME['RailPhase'])*$GAME['NumCanalLinks']; $i++) {
        if ( $GAME['LinkStatus'][$i] == $GAME['PlayerToMove'] ) {
            $GAME['LinkStatus'][$i] = 8;
        }
    }
    switch ( $GAME['CurrentPlayers'] ) {
        case 4:
            if ( $GAME['Round'] < 3 )       { $GAME['NumRounds'] = 10; }
            else if ( $GAME['Round'] == 3 ) { $GAME['NumRounds'] = 9;  }
            if ( !$GAME['RailPhase'] or $GAME['Round'] < 4 ) {
                $GAME['EffectiveNumPlayers'] = 3;
            }
            $GAME['ShuffledDeck'] = array_reverse($GAME['ShuffledDeck']);
            if ( count($GAME['ShuffledDeck']) == 1 and $GAME['ShuffledDeck'][0] === '' ) {
                $GAME['ShuffledDeck'] = array();
            }
            shuffle($GAME['Cards'][$GAME['PlayerToMove']]);
            for ($i=0;$i<$GAME['HandSize'][$GAME['PlayerToMove']];$i++) {
                $GAME['ShuffledDeck'][] = array_pop($GAME['Cards'][$GAME['PlayerToMove']]);
            }
            $GAME['ShuffledDeck'] = array_reverse($GAME['ShuffledDeck']);
            $GAME['AltGameTicker'] .= 'AAA9J';
            break;
        case 3:
            // At present, it is not permitted for games to be downsized from 3 players to 2 players.
            // This function should not be called when CurrentPlayers is equal to 3; it will stop script execution with an error message.
            die('Programming error: Downsize function called when number of players is 3. Please contact Administrator and give details of how you obtained this error message.');
            break;
        case 2:
            for ($i=0; $i<MAX_PLAYERS; $i++) {
                if ( $GAME['PlayerExists'][$i] ) { $TheLastPlayer = $i; }
            }
            if ( $GAME['RailPhase'] and
                 $GAME['NumRounds'] - $GAME['Round'] < 2
                 ) {
                require_once(HIDDEN_FILES_PATH.'scoringresource.php');
                endgamescoring();
            } else {
                $GAME['GameStatus'] = 'Finished';
                require_once(HIDDEN_FILES_PATH.'createpgr.php');
                CreatePGR(0, 0, -1, 0, $TheLastPlayer);
            }
            $GAME['AltGameTicker'] .= 'AAA9J';
            break;
    }
    $GAME['CurrentPlayers']--;
    if ( $GAME['CurrentPlayers'] > 1 ) {
        for ($i=0; $i<MAX_PLAYERS; $i++) {
            if ( $GAME['TurnOrder'][$i] == $GAME['PlayerToMove'] ) {
                $WhereInTurnOrder = $i;
            }
        }
        for ($i=$WhereInTurnOrder; $i<MAX_PLAYERS-1; $i++) {
            $GAME['TurnOrder'][$i] = $GAME['TurnOrder'][$i+1];
        }
        $GAME['TurnOrder'][MAX_PLAYERS-1] = $GAME['PlayerToMove'];
        if ( $GAME['DebtMode'] ) {
            $SomeoneInDebt = 9;
            for ($i=0; $i<MAX_PLAYERS; $i++) {
                if ( $GAME['PlayerExists'][$GAME['TurnOrder'][$i]] and
                     $GAME['Money'][$GAME['TurnOrder'][$i]] < 0
                     ) {
                    for ($j=0;$j<$GAME['NumIndustrySpaces'];$j++) {
                        if ( $GAME['SpaceStatus'][$j] == $GAME['TurnOrder'][$i] ) {
                            $SomeoneInDebt = $GAME['TurnOrder'][$i];
                            break 2;
                        }
                    }
                }
            }
            if ( $SomeoneInDebt == 9 ) {
                $GAME['DebtMode'] = 0;
                $GAME['PlayerToMove'] = $GAME['TurnOrder'][0];
            } else {
                $GAME['PlayerToMove'] = $SomeoneInDebt;
            }
        } else {
            $EndRound = true;
            for ($i=MAX_PLAYERS-2; $i>=$WhereInTurnOrder; $i--) {
                if ( $GAME['PlayerExists'][$GAME['TurnOrder'][$i]] ) {
                    $GAME['PlayerToMove'] = $GAME['TurnOrder'][$i];
                    $EndRound = false;
                }
            }
            if ( $EndRound ) {
                if ( $GAME['Round'] == $GAME['NumRounds'] ) {
                    if ( $GAME['RailPhase'] ) {
                        require_once(HIDDEN_FILES_PATH.'scoringresource.php');
                        endgamescoring();
                    } else {
                        require_once(HIDDEN_FILES_PATH.'scoringresource.php');
                        require_once(HIDDEN_FILES_PATH.'turnorderresource.php');
                        canalphasescoring();
                        DoTurnOrder(1);
                    }
                } else {
                    require_once(HIDDEN_FILES_PATH.'turnorderresource.php');
                    DoTurnOrder(1);
                }
            }
        }
        if ( $GAME['RailPhase'] and
             $GAME['Round'] == $GAME['NumRounds'] and
             $GAME['GameStatus'] != 'Finished'
             ) {
            require_once(HIDDEN_FILES_PATH.'nomovesresource.php');
            CheckNoMovesShell();
        }
    }
}

?>