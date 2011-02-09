<?php

function KickPlayer ($PlayerToKick, $KickType) {
    // This function returns true if it has called abortgame(),
    // and false otherwise.
    global $GAME;
    if ( isset($GAME['KickPlayerCalls']) and
         $GAME['KickPlayerCalls'] > 5
         ) {
        die('Programming error: Recursive function appears to be in unintended loop.');
    }
    if ( isset($GAME['KickPlayerCalls']) ) { $GAME['KickPlayerCalls']++;   }
    else                                   { $GAME['KickPlayerCalls'] = 1; }
    require_once(HIDDEN_FILES_PATH.'createpgr.php');
    CreatePGR(0, $KickType+1, 0, 0, $PlayerToKick);
        // Update the row in PlayerGameRcd to reflect
        // this player's having left the game.
    $ProceedToKickOrDownsize  = false;
    $ProceedToAbort           = false;
        // Do I need to go on to kick the current player, downsize
        // the game, or abort the game when I've finished?
    $GAME['PlayerMissing'][$PlayerToKick] = 1;
    $GAME['PlayersMissing']++;
        // This player is now missing.
    if ( !$GAME['RailPhase'] or
         $GAME['PlayerToMove'] == $PlayerToKick or
         $GAME['HandSize'][$PlayerToKick]
         ) {
        $GAME['PlayersMissingThatMatter']++;
        $GAME['PlayerMissingAndMatters'][$PlayerToKick] = 1;
            // Provided the player should take additional decisions
            // in the game, his departure "matters".
    }
    if ( !$GAME['AbortVote'][$PlayerToKick] ) { $GAME['PlayersVotingToAbort']++; }
    if ( !$GAME['KickVote'][$PlayerToKick]  ) { $GAME['PlayersVotingToKick']++;  }
        // This player now counts as having voted "yes" on any vote that's happening,
        // although we have to bear in mind that a vote is only actually happening
        // if the corresponding *VoteActive variable is set to something "true".
    $GAME['AbortVote'][$PlayerToKick] = 0;
    $GAME['KickVote'][$PlayerToKick]  = 0;
        // We should set this player's actual votes, however, as zero.
    if ( $GAME['AbortVoteActive'] and
         $GAME['PlayersVotingToAbort'] == $GAME['CurrentPlayers'] - 1
         ) {
        $ProceedToAbort = true;
            // If there's a vote on to abort the game, and this player was
            // the last person who needed to vote, then we should abort the game.
    }
    if ( $PlayerToKick == $GAME['PlayerToMove'] ) {
        $GAME['MoveMade'] = 1;
        $GAME['KickVote'] = '00000';
            // The vote has served its purpose and is over.
    } else if ( !$ProceedToAbort and
                $GAME['KickVoteActive'] and
                $GAME['PlayersVotingToKick'] == $GAME['CurrentPlayers'] - 1
                ) {
        $ProceedToKickOrDownsize = true;
            // If there's a vote on to kick the current player or to downsize
            // the game, and this player was the last person who needed to vote,
            // then we should go ahead and do it... although not if we're already
            // aborting the game. In that case we'll just abort the game and
            // ignore the rest.
    }
    switch ( $KickType ) {
        // We add notes to the game log to say what's happening.
        case 0: // quit
            $GAME['AltGameTicker'] .= '4C'.callmovetimediff().letter_end_number($PlayerToKick);
            break;
        case 1: // kicked by admin
            $GAME['AltGameTicker'] .= '5C'.
                                      callmovetimediff().
                                      letter_end_number($_SESSION['MyUserID']).
                                      letter_end_number($_SESSION['MyGenderCode']).
                                      letter_end_number($PlayerToKick);
            $GAME['GameTickerNames'] .= '|'.$_SESSION['MyUserName'];
            break;
        case 2: // kicked by vote
            $GAME['AltGameTicker'] .= '5D'.callmovetimediff();
            break;
        case 3: // kicked by the system
            $GAME['AltGameTicker'] .= '5A'.callmovetimediff();
    }
    if ( $GAME['PlayerMissing'][$GAME['PlayerToMove']] ) {
        $GAME['GameStatus'] = 'Recruiting Replacement';
            // If the current "seat" is unoccupied, then we need to put this game
            // in the list of games that require replacement players.
    }
    $GAME['NumDepartures']++;
    if ( $ProceedToAbort ) {
        abortgame(0);
        return true;
    } else if ( $ProceedToKickOrDownsize ) {
        if ( $GAME['GameStatus'] == 'Recruiting Replacement' ) {
            downsizegame(false);
        } else {
            KickPlayer($GAME['PlayerToMove'], 2);
        }
    } else if ( $GAME['NumDepartures'] > PERMITTED_DEPARTURES ) {
        // if this condition is satisfied then at some point I will have to
        // either abort or downsize the game (whichever I would do at Time Limit B).
        if ( ( $GAME['CurrentPlayers'] - $GAME['PlayersMissingThatMatter'] == $GAME['MinimumPlayersAllowed'] and
               $GAME['MinimumPlayersAllowed'] > 2
               ) or
             $GAME['CurrentPlayers'] == 3 or
             $GAME['DoWhatAtB'] == 'Kick current player; subsequently abort' or
             $GAME['DoWhatAtB'] == 'Abort'
             ) {
            // In this situation I'll have to abort the game sooner or later,
            // so I may as well abort it right now.
            abortgame(2);
            return true;
        } else if ( $GAME['GameStatus'] == 'Recruiting Replacement' ) {
            // If the current seat is empty then it's time to downsize the game.
            downsizegame(true);
        }
    }
    return false;
}

function abortgame ($byAdmin) {
    global $GAME;
    if ( $GAME['GameStatus'] != 'Aborted' ) {
        // I don't think there is currently any code that will call this
        // function if the game is already aborted, but may as well be safe
        $GAME['GameStatus'] = 'Aborted';
        if ( $byAdmin == 2 ) { // by the system
            $GAME['AltGameTicker'] .= '6A'.callmovetimediff();
        } else if ( $byAdmin ) { // by an admin
            $GAME['AltGameTicker'] .= '6B'.
                                      callmovetimediff().
                                      letter_end_number($_SESSION['MyUserID']).
                                      letter_end_number($_SESSION['MyGenderCode']);
            $GAME['GameTickerNames'] .= '|'.$_SESSION['MyUserName'];
        } else { // by vote
            $GAME['AltGameTicker'] .= '6C'.callmovetimediff();
        }
        require_once(HIDDEN_FILES_PATH.'createpgr.php');
        for ($i=0; $i<5; $i++) {
            if ( $GAME['PlayerExists'][$i] and !$GAME['PlayerMissing'][$i] ) {
                CreatePGR(1, 0, 0, 0, $i);
            }
        }
    }
}

function gamecheck () {
    global $GAME, $PersonActing;
    if ( $GAME['GameStatus'] == 'In Progress' ) {
        if ( $GAME['PlayerMissing'][$GAME['PlayerToMove']] ) {
            $GAME['GameStatus'] = 'Recruiting Replacement';
            if ( $GAME['NumDepartures'] > PERMITTED_DEPARTURES ) {
                if ( ( $GAME['CurrentPlayers'] - $GAME['PlayersMissingThatMatter'] == $GAME['MinimumPlayersAllowed'] and
                       $GAME['MinimumPlayersAllowed'] > 2
                       ) or
                     $GAME['CurrentPlayers'] == 3
                     ) {
                    if ( $GAME['DoWhatAtB'] == 'Downsize' ) {
                        $GAME['DoWhatAtB'] = 'Abort';
                    }
                    if ( $GAME['DoWhatAtB'] == 'Kick current player; subsequently downsize' ) {
                        $GAME['DoWhatAtB'] = 'Kick current player; subsequently abort';
                    }
                }
                switch ( $GAME['DoWhatAtB'] ) {
                    case 'Downsize':
                    case 'Kick current player; subsequently downsize':
                        require_once(HIDDEN_FILES_PATH.'downsizeresource.php');
                        downsizegame(true);
                        return true;
                        break;
                    default:
                        abortgame(2);
                }
            } else if ( in_array($GAME['PlayerToMove'],$GAME['ReplacementOffers_Colours']) ) {
                $QR = dbquery( DBQUERY_READ_RESULTSET,
                               'SELECT "User"."UserID", "User"."Name", "User"."Pronoun" FROM "ReplacementOffer" JOIN "User" ON "ReplacementOffer"."User" = "User"."UserID" WHERE "ReplacementOffer"."Game" = :game: AND "ReplacementOffer"."Colour" = :colour:',
                               'game'   , $GAME['GameID']       ,
                               'colour' , $GAME['PlayerToMove']
                               );
                while ( $row = db_fetch_assoc($QR) ) {
                    switch ( $row['Pronoun'] ) {
                        case 'He':  $RepPronoun = 'A'; break;
                        case 'She': $RepPronoun = 'B'; break;
                        default:    $RepPronoun = 'C'; break;
                    }
                    $GAME['AltGameTicker'] .= '8A'.
                                              callmovetimediff().
                                              letter_end_number($row['UserID']).
                                              $RepPronoun;
                    $GAME['GameTickerNames'] .= '|'.$row['Name'];
                }
                for ($i=0;$i<MAX_PLAYERS;$i++) {
                    if ( $GAME['PlayerExists'][$i] and
                         !$GAME['PlayerMissing'][$i] and
                         $GAME['Email'][$i] != '' and
                         $GAME['EmailPrompt'][$i]
                         ) {
                        $subject = 'Replacement players available';
                        $body = '<p>This is an automated message. One or more users have volunteered to step in as a replacement in game number '.
                                $GAME['GameID'].
                                '. Please visit the game page to review the volunteers, and to accept one if you are satisfied of his suitability. Here is the URL to the game page:</p><p><a href="'.
                                SITE_ADDRESS.
                                'board.php?GameID='.
                                $GAME['GameID'].
                                '">'.
                                SITE_ADDRESS.
                                'board.php?GameID='.
                                $GAME['GameID'].
                                '</a></p>'.
                                EMAIL_FOOTER;
                        send_email($subject, $body, $GAME['Email'][$i], null);
                        dbquery( DBQUERY_WRITE,
                                 'UPDATE "User" SET "HasBeenEmailed" = 1 WHERE "UserID" = :user:',
                                 'user' , $GAME['PlayerUserID'][$i]
                                 );
                    }
                }
            }
        } else if ( $GAME['PlayerExists'][$GAME['PlayerToMove']] and
                    !$GAME['PlayerMissing'][$GAME['PlayerToMove']] and
                    $GAME['Email'][$GAME['PlayerToMove']] != '' and
                    $GAME['EmailPrompt'][$GAME['PlayerToMove']] and
                    ( $GAME['EmailPromptAgain'][$GAME['PlayerToMove']] or
                      !$GAME['HasBeenEmailed'][$GAME['PlayerToMove']]
                      ) and
                    $PersonActing != $GAME['PlayerUserID'][$GAME['PlayerToMove']]
                    ) {
            $subject = 'It\'s your turn to make a move';
            $body = '<p>This is an automated message. It\'s your turn to make a move in game number '.
                    $GAME['GameID'].
                    '. Here is the URL to the game page:</p><p><a href="'.
                    SITE_ADDRESS.
                    'board.php?GameID='.
                    $GAME['GameID'].
                    '">'.
                    SITE_ADDRESS.
                    'board.php?GameID='.
                    $GAME['GameID'].
                    '</a></p>'.
                    EMAIL_FOOTER;
            send_email($subject, $body, $GAME['Email'][$GAME['PlayerToMove']], null);
            dbquery( DBQUERY_WRITE,
                     'UPDATE "User" SET "HasBeenEmailed" = 1 WHERE "UserID" = :user:',
                     'user' , $GAME['PlayerUserID'][$GAME['PlayerToMove']]
                     );
        }
    }
    return false;
}

function drawDMtile () {
    global $GAME;
    if ( $GAME['TilesDrawn'] == 'None' ) {
        $GAME['TilesDrawn'] = array();
    } else {
        $GAME['TilesDrawn'] = str_split($GAME['TilesDrawn'],1);
    }
    $GAME['ShuffledTiles'] = str_split($GAME['ShuffledTiles'],1);
    $newtile = array_pop($GAME['ShuffledTiles']);
    $GAME['TilesDrawn'][] = $newtile;
    $GAME['TilesDrawn'] = implode('',$GAME['TilesDrawn']);
    $GAME['ShuffledTiles'] = implode('',$GAME['ShuffledTiles']);
    $GAME['CottonDemand'] += $newtile;
    if ( $GAME['CottonDemand'] < 8 ) {
        return true;
    } else {
        $GAME['CottonDemand'] = 8;
        return false;
    }
}

function DMSaleSuccessProbability () {
    global $GAME;
    $numtiles = strlen($GAME['ShuffledTiles']);
    if ( !$numtiles ) { return 0; }
    $numsuccesstiles = 0;
    for ($i=0;$i<$numtiles;$i++) {
        if ( $GAME['CottonDemand'] + $GAME['ShuffledTiles'][$i] < 8 ) {
            $numsuccesstiles++;
        }
    }
    $rtnval = $numsuccesstiles / $numtiles;
    return $rtnval;
}

function GetIron ($specifiedsource) {
    global $GAME;
    if ( $specifiedsource < $GAME['NumIndustrySpaces'] ) {
        if ( $GAME['SpaceStatus'][$specifiedsource] != 9 and
             $GAME['SpaceTile'][$specifiedsource] == 2 and
             $GAME['SpaceCubes'][$specifiedsource]
             ) {
            return $specifiedsource;
        } else {
            return 90;
        }
    } else {
        if ( $GAME['IronInLancs'] ) { return 91; }
        else                        { return 50; }
    }
}

function fliptile ($wheretoflip) {
    global $GAME;
    $GAME['SpaceCubes'][$wheretoflip] = 0;
    if ( $GAME['SpaceStatus'][$wheretoflip] != 8 ) {
        $GAME['IncomeSpace'][$GAME['SpaceStatus'][$wheretoflip]] +=
            $GAME['TileIncomeValue'][$GAME['SpaceTile'][$wheretoflip]][$GAME['TechLevels'][$wheretoflip]-1];
        if ( $GAME['IncomeSpace'][$GAME['SpaceStatus'][$wheretoflip]] > 99 ) {
            $GAME['IncomeSpace'][$GAME['SpaceStatus'][$wheretoflip]] = 99;
        }
    }
}

function destroytile ($wheretodestroy, $repay, $recalculate) {
    global $GAME,$SystemActing;
    if ( $wheretodestroy != 100 ) {
        if ( $GAME['SpaceTile'][$wheretodestroy] == 1 ) {
            $GAME['CoalInLancs'] = $GAME['CoalInLancs'] - $GAME['SpaceCubes'][$wheretodestroy];
        }
        if ( $GAME['SpaceTile'][$wheretodestroy] == 2 ) {
            $GAME['IronInLancs'] = $GAME['IronInLancs'] - $GAME['SpaceCubes'][$wheretodestroy];
        }
        if ( $repay ) {
            $repayamount = $GAME['TileCosts'][$GAME['SpaceTile'][$wheretodestroy]][$GAME['TechLevels'][$wheretodestroy]-1] / 2;
            $repayamount = (int)$repayamount;
            $GAME['Money'][$GAME['SpaceStatus'][$wheretodestroy]] += $repayamount;
            if ( !$SystemActing ) {
                if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION[MyUserID] ) {
                    $AdminTakingMove = 0;
                } else {
                    $AdminTakingMove = 1;
                }
            } else {
                $AdminTakingMove = 0;
            }
            if ( $AdminTakingMove ) {
                $GAME['AltGameTicker'] .= '1F'.
                                          callmovetimediff().
                                          letter_end_number($_SESSION['MyUserID']).
                                          letter_end_number($_SESSION['MyGenderCode']).
                                          letter_end_number($wheretodestroy);
                $GAME['GameTickerNames'] .= '|'.$_SESSION['MyUserName'];
            } else {
                $GAME['AltGameTicker'] .= 'A'.
                                          callmovetimediff().
                                          letter_end_number($wheretodestroy);
                $GAME['MoveMadeByPlayer'][$GAME['PlayerToMove']] = 1;
            }
        }
        $GAME['SpaceStatus'][$wheretodestroy] = 9;
        $GAME['SpaceCubes'][$wheretodestroy] = 1;
    }
    if ( $recalculate ) {
        for ($i=0;$i<MAX_PLAYERS;$i++) {
            if ( $GAME['RemainingTiles'][5][$i] == 14 ) { $GAME['HasBuilt'][$i] = 0; }
            else                                        { $GAME['HasBuilt'][$i] = 1; }
            for ($j=0;$j<$GAME['NumTowns'];$j++) {
                $GAME['HasBuiltInTown'][$i][$j] = 0;
            }
            for ($j=0;$j<$GAME['NumIndustrySpaces'];$j++) {
                if ( $GAME['SpaceStatus'][$j] == $i ) {
                    $GAME['HasBuiltInTown'][$i][$GAME['spacetowns'][$j]] = 1;
                    $GAME['HasBuilt'][$i] = 1;
                }
            }
        }
        $GAME['HasPort'] = '';
        for ($i=0;$i<$GAME['NumTowns'];$i++) {
            $AssignHasPort = 0;
            for ($j=0;$j<$GAME['NumTowns'];$j++) {
                if ( $GAME['ExternalLocations'][$j] and $GAME['CoalNet'][$i] == $GAME['CoalNet'][$j] ) {
                    $AssignHasPort = 1;
                }
            }
            $GAME['HasPort'] .= $AssignHasPort;
        }
        for ($i=0;$i<$GAME['NumIndustrySpaces'];$i++) {
            if ( $GAME['SpaceStatus'][$i] != 9 and $GAME['SpaceTile'][$i] == 3 ) {
                for ($j=0;$j<$GAME['NumTowns'];$j++) {
                    if ( $GAME['CoalNet'][$j] == $GAME['CoalNet'][$GAME['spacetowns'][$i]] ) {
                        $GAME['HasPort'][$j] = 1;
                    }
                }
            }
        }
    }
}

function moveexecute ($formdetails) {
    global $GAME,$SystemActing;
    $errorlist = '';
    $MoveOn = true;
    if ( $GAME['DebtMode'] ) {
        if ( $GAME['SpaceStatus'][$formdetails['IndustrySpace']] == 9 ) {
            $errorlist .= transtext('memNoTileInSpace').'<br>';
        } else if ( $GAME['SpaceStatus'][$formdetails['IndustrySpace']] != $GAME['PlayerToMove'] ) {
            $errorlist .= transtext('memTileNotYours').'<br>';
        } else {
            destroytile($formdetails['IndustrySpace'],1,1);
            $GAME['MoveMade'] = 1;
            $GAME['MoveMadeByPlayer'][$GAME['PlayerToMove']] = 1;
        }
        if ( $GAME['Money'][$GAME['PlayerToMove']] >= 0 ) {
            for ($i=0;$i<MAX_PLAYERS;$i++) {
                if ( $GAME['TurnOrder'][$i] == $GAME['PlayerToMove'] ) {
                    $WhereInTurnOrder = $i;
                }
            }
            $GAME['DebtMode'] = 0;
            for ($i=MAX_PLAYERS-1;$i>$WhereInTurnOrder;$i--) {
                if ( $GAME['PlayerExists'][$GAME['TurnOrder'][$i]] and
                     $GAME['Money'][$GAME['TurnOrder'][$i]] < 0
                     ) {
                    for ($j=0;$j<$GAME['NumIndustrySpaces'];$j++) {
                        if ( $GAME['SpaceStatus'][$j] == $GAME['TurnOrder'][$i] ) {
                            $GAME['PlayerToMove'] = $GAME['TurnOrder'][$i];
                            $GAME['DebtMode'] = 1;
                            break;
                        }
                    }
                }
            }
            if ( !$GAME['DebtMode'] ) {
                $GAME['PlayerToMove'] = $GAME['TurnOrder'][0];
                $MoveOn = false;
            }
        }
    } else if ( $GAME['SecondRailMode'] ) {
        if ( $formdetails['LinkToBuild'] == 50 ) {
            if ( $SystemActing ) {
                $AdminTakingMove = 0;
            } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
                $AdminTakingMove = 0;
            } else {
                $AdminTakingMove = 1;
            }
            if ( $AdminTakingMove ) {
                $ResultArray = array('',
                                     '',
                                     '2E'.callmovetimediff().
                                          letter_end_number($_SESSION['MyUserID']).
                                          letter_end_number($_SESSION['MyGenderCode']),
                                     '|'.$_SESSION['MyUserName']
                                     );
            } else {
                $ResultArray = array('','','J'.callmovetimediff(),'');
            }
            $GAME['SecondRailMode'] = 0;
        } else {
            require_once(HIDDEN_FILES_PATH.'coalresource.php');
            require_once(HIDDEN_FILES_PATH.'linkresource.php');
            $AssuredCoalSource = ClosestCoal($GAME['RailStarts'][$formdetails['LinkToBuild']],
                                             $GAME['RailEnds'][$formdetails['LinkToBuild']],
                                             $formdetails['CoalSource']
                                             );
            $ResultArray = buildlink($formdetails['LinkToBuild'],$AssuredCoalSource,2,50);
        }
        if ( $ResultArray[0] == '' ) {
            $GAME['AltGameTicker']   .= $ResultArray[2];
            $GAME['GameTickerNames'] .= $ResultArray[3];
        } else {
            $errorlist .= $ResultArray[0];
        }
    } else if ( $GAME['SecondDevelopMode'] ) {
        if ( $formdetails['TileType'] == 9 ) {
            if ( $SystemActing ) {
                $AdminTakingMove = 0;
            } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
                $AdminTakingMove = 0;
            } else {
                $AdminTakingMove = 1;
            }
            if ( $AdminTakingMove ) {
                $ResultArray = array('',
                                     '',
                                     '2E'.callmovetimediff().
                                          letter_end_number($_SESSION['MyUserID']).
                                          letter_end_number($_SESSION['MyGenderCode']),
                                     '|'.$_SESSION['MyUserName']
                                     );
            } else {
                $ResultArray = array('','','J'.callmovetimediff(),'');
            }
            $GAME['SecondDevelopMode'] = 0;
        } else {
            require_once(HIDDEN_FILES_PATH.'developresource.php');
            $AssuredIronSource = GetIron($formdetails['IronSource']);
            $ResultArray = develop($formdetails['TileType'],$AssuredIronSource,1,50);
        }
        if ( $ResultArray[0] == '' ) {
            $GAME['AltGameTicker']   .= $ResultArray[2];
            $GAME['GameTickerNames'] .= $ResultArray[3];
        } else {
            $errorlist .= $ResultArray[0];
        }
    } else if ( $GAME['ContinueSellingMode'] ) {
        if ( $formdetails['IndustrySpace'] == 50 ) {
            if ( $SystemActing ) {
                $AdminTakingMove = 0;
            } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
                $AdminTakingMove = 0;
            } else {
                $AdminTakingMove = 1;
            }
            if ( $AdminTakingMove ) {
                $ResultArray = array('',
                                     '',
                                     '2E'.callmovetimediff().
                                          letter_end_number($_SESSION['MyUserID']).
                                          letter_end_number($_SESSION['MyGenderCode']),
                                     '|'.$_SESSION['MyUserName']
                                     );
            } else {
                $ResultArray = array('','','J'.callmovetimediff(),'');
            }
            $GAME['ContinueSellingMode'] = 0;
        } else {
            require_once(HIDDEN_FILES_PATH.'cottonresource.php');
            $ResultArray = sellcotton($formdetails['IndustrySpace'],$formdetails['PortSpace'],1,50);
        }
        if ( $ResultArray[0] == '' ) {
            $GAME['AltGameTicker']   .= $ResultArray[2];
            $GAME['GameTickerNames'] .= $ResultArray[3];
        } else {
            $errorlist .= $ResultArray[0];
        }
    } else {
        if ( $formdetails['CardA'] == 9 ) {
            $errorlist .= 'No card selected.<br>';
        } else {
            $CardDetailsA = $GAME['carddetailarrayb'][$GAME['Cards'][$GAME['PlayerToMove']][$formdetails['CardA']]];
            if ( $formdetails['CardB'] == 9 ) {
                $CardDetailsB = 50;
                $passcb = '';
            } else {
                $CardDetailsB = $GAME['carddetailarrayb'][$GAME['Cards'][$GAME['PlayerToMove']][$formdetails['CardB']]];
                if ( ( $GAME['Round'] == 1 and !$GAME['RailPhase'] ) or
                     ( $GAME['HandSize'][$GAME['PlayerToMove']] % 2 )
                     ) {
                    $passcb = '';
                } else {
                    $passcb = letter_end_number($CardDetailsB);
                }
            }
            switch ( $formdetails['MoveType'] ) {
                case 0:
                case 1:
                    if ( $formdetails['MoveType'] and $formdetails['CardB'] == 9 ) {
                        $ResultArray = array(transtext('memNo2ndCard').'<br>','','');
                    } else if ( $formdetails['MoveType'] and
                                $formdetails['CardB'] == $formdetails['CardA']
                                ) {
                        $ResultArray = array(transtext('memDuplicCard').'<br>','','');
                            // "You have selected the same card twice. Please select two different cards."
                    } else if ( $formdetails['IndustrySpace'] == 50 or
                                $formdetails['TileType'] == 9
                                ) {
                        $ResultArray = array(transtext('memBadMoveData').'<br>','','');
                    } else {
                        require_once(HIDDEN_FILES_PATH.'coalresource.php');
                        require_once(HIDDEN_FILES_PATH.'tileresource.php');
                        $AssuredCoalSource = ClosestCoal($formdetails['IndustrySpace'],
                                                         50,
                                                         $formdetails['CoalSource']
                                                         );
                        $AssuredIronSource = GetIron($formdetails['IronSource']);
                        $ResultArray = buildtile($formdetails['IndustrySpace'],
                                                 $formdetails['TileType'],
                                                 $AssuredCoalSource,
                                                 $AssuredIronSource,
                                                 $formdetails['CardA'],
                                                 $formdetails['MoveType'],
                                                 $CardDetailsA,
                                                 $CardDetailsB
                                                 );
                    }
                    $TwoCards = $formdetails['MoveType'];
                    break;
                case 2:
                    require_once(HIDDEN_FILES_PATH.'linkresource.php');
                    if ( $formdetails['LinkToBuild'] == 50 ) {
                        $ResultArray = array(transtext('memBadMoveData').'<br>','','');
                    } else if ( $GAME['RailPhase'] ) {
                        require_once(HIDDEN_FILES_PATH.'coalresource.php');
                        $AssuredCoalSource = ClosestCoal($GAME['RailStarts'][$formdetails['LinkToBuild']],
                                                         $GAME['RailEnds'][$formdetails['LinkToBuild']],
                                                         $formdetails['CoalSource']
                                                         );
                        $ResultArray = buildlink($formdetails['LinkToBuild'],$AssuredCoalSource,1,$CardDetailsA);
                    } else {
                        $ResultArray = buildlink($formdetails['LinkToBuild'],50,1,$CardDetailsA);
                    }
                    $TwoCards = 0;
                    break;
                case 3:
                    if ( $formdetails['TileType'] == 9 ) {
                        $ResultArray = array(transtext('memBadMoveData').'<br>','','');
                    } else {
                        require_once(HIDDEN_FILES_PATH.'developresource.php');
                        $AssuredIronSource = GetIron($formdetails['IronSource']);
                        $ResultArray = develop($formdetails['TileType'],$AssuredIronSource,0,$CardDetailsA);
                    }
                    $TwoCards = 0;
                    break;
                case 4:
                    $ResultArray = array('','','');
                    if ( $formdetails['LoanAmount'] > 3 or $GAME['IncomeSpace'][$GAME['PlayerToMove']] - $formdetails['LoanAmount'] < 0 ) {
                        $ResultArray[0] .= transtext('memLoanTooBig').'<br>';
                    }
                    if ( $GAME['RailPhase'] and $GAME['NumRounds'] - $GAME['Round'] < 4 ) {
                        $ResultArray[0] .= transtext('memLoanTooLate').'<br>';
                    }
                    if ( $ResultArray[0] == '' ) {
                        $amount = 10*$formdetails['LoanAmount'];
                        $GAME['Money'][$GAME['PlayerToMove']] += $amount;
                        $reducearray = array(  0,  0,  1,  2,  3,  4,  5,  6,  7,  8,
                                               9, 10, 10, 12, 12, 14, 14, 16, 16, 18,
                                              18, 20, 20, 22, 22, 24, 24, 26, 26, 28,
                                              28, 30, 30, 30, 33, 33, 33, 36, 36, 36,
                                              39, 39, 39, 42, 42, 42, 45, 45, 45, 48,
                                              48, 48, 51, 51, 51, 54, 54, 54, 57, 57,
                                              57, 60, 60, 60, 60, 64, 64, 64, 64, 68,
                                              68, 68, 68, 72, 72, 72, 72, 76, 76, 76,
                                              76, 80, 80, 80, 80, 84, 84, 84, 84, 88,
                                              88, 88, 88, 92, 92, 92, 92, 96, 96, 96
                                              );
                        for ($i=0;$i<$formdetails['LoanAmount'];$i++) {
                            $GAME['IncomeSpace'][$GAME['PlayerToMove']] =
                                $reducearray[$GAME['IncomeSpace'][$GAME['PlayerToMove']]];
                        }
                        if ( $SystemActing ) {
                            $AdminTakingMove = 0;
                        } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
                            $AdminTakingMove = 0;
                        } else {
                            $AdminTakingMove = 1;
                        }
                        if ( $AdminTakingMove ) {
                            $ResultArray[2] = '2A'.
                                              callmovetimediff().
                                              letter_end_number($_SESSION['MyUserID']).
                                              letter_end_number($_SESSION['MyGenderCode']).
                                              letter_end_number($CardDetailsA).
                                              letter_end_number($formdetails['LoanAmount']);
                            $ResultArray[3] = '|'.$_SESSION['MyUserName'];
                        } else {
                            $ResultArray[2] = 'F'.
                                              callmovetimediff().
                                              letter_end_number($CardDetailsA).
                                              letter_end_number($formdetails['LoanAmount']);
                            $ResultArray[3] = '';
                        }
                    }
                    $TwoCards = 0;
                    break;
                case 5:
                    if ( $formdetails['IndustrySpace'] == 50 ) {
                        $ResultArray = array(transtext('memBadMoveData').'<br>','','');
                    } else {
                        require_once(HIDDEN_FILES_PATH.'cottonresource.php');
                        $ResultArray = sellcotton($formdetails['IndustrySpace'],
                                                  $formdetails['PortSpace'],
                                                  0,
                                                  $CardDetailsA
                                                  );
                    }
                    $TwoCards = 0;
                    break;
                case 6:
                    $ResultArray[0] = '';
                    if ( $GAME['HandSize'][$GAME['PlayerToMove']] % 2 ) {
                        $TwoCards = 0;
                    } else if ( !$GAME['RailPhase'] and $GAME['Round'] == 1 ) {
                        $TwoCards = 0;
                    } else {
                        $TwoCards = 1;
                        if ( $CardDetailsB == 50 ) {
                            $ResultArray[0] = transtext('memNo2ndCard').'<br>';
                                // No second card selected.
                        }
                        if ( $formdetails[CardB] == $formdetails[CardA] ) {
                            $ResultArray[0] = transtext('memDuplicCard').'<br>';
                                // "You have selected the same card twice. Please select two different cards."
                        }
                    }
                    if ( $SystemActing ) {
                        $AdminTakingMove = 0;
                    } else if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
                        $AdminTakingMove = 0;
                    } else {
                        $AdminTakingMove = 1;
                    }
                    if ( $AdminTakingMove ) {
                        $ResultArray[2] = '2C'.
                                          callmovetimediff().
                                          letter_end_number($_SESSION['MyUserID']).
                                          letter_end_number($_SESSION['MyGenderCode']).
                                          letter_end_number($CardDetailsA).
                                          $passcb;
                        $ResultArray[3] = '|'.$_SESSION['MyUserName'];
                    } else {
                        $ResultArray[2] = 'H'.
                                          callmovetimediff().
                                          letter_end_number($CardDetailsA).
                                          $passcb;
                        $ResultArray[3] = '';
                    }
            }
            if ( $ResultArray[0] == '' ) {
                $GAME['AltGameTicker']   .= $ResultArray[2];
                $GAME['GameTickerNames'] .= $ResultArray[3];
                if ( $TwoCards ) {
                    if ( $formdetails['CardA'] < $formdetails['CardB'] ) {
                        $formdetails['CardB']--;
                    }
                }
                $y = $GAME['Cards'][$GAME['PlayerToMove']][$formdetails['CardA']];
                for ($i=$formdetails['CardA'];$i<$GAME['HandSize'][$GAME['PlayerToMove']]-1;$i++) {
                    $GAME['Cards'][$GAME['PlayerToMove']][$i] = $GAME['Cards'][$GAME['PlayerToMove']][$i+1];
                }
                $GAME['Cards'][$GAME['PlayerToMove']][$GAME['HandSize'][$GAME['PlayerToMove']]-1] = $y;
                $GAME['DiscardPile'][] = array_pop($GAME['Cards'][$GAME['PlayerToMove']]);
                $GAME['HandSize'][$GAME['PlayerToMove']]--;
                if ( $TwoCards ) {
                    $y = $GAME['Cards'][$GAME['PlayerToMove']][$formdetails['CardB']];
                    for ($i=$formdetails['CardB'];$i<$GAME['HandSize'][$GAME['PlayerToMove']]-1;$i++) {
                        $GAME['Cards'][$GAME['PlayerToMove']][$i] = $GAME['Cards'][$GAME['PlayerToMove']][$i+1];
                    }
                    $GAME['Cards'][$GAME['PlayerToMove']][$GAME['HandSize'][$GAME['PlayerToMove']]-1] = $y;
                    $GAME['DiscardPile'][] = array_pop($GAME['Cards'][$GAME['PlayerToMove']]);
                    $GAME['HandSize'][$GAME['PlayerToMove']]--;
                }
            } else {
                $errorlist .= $ResultArray[0];
            }
        }
    }
    if ( $errorlist == '' ) {
        $GAME['MoveMade'] = 1;
        if ( !$SystemActing ) {
            if ( $GAME['PlayerUserID'][$GAME['PlayerToMove']] == $_SESSION['MyUserID'] ) {
                $GAME['MoveMadeByPlayer'][$GAME['PlayerToMove']] = 1;
            }
        } else {
            $GAME['MoveMadeByPlayer'][$GAME['PlayerToMove']] = 1;
        }
    }
    if ( $MoveOn and
         !$GAME['DebtMode'] and
         !$GAME['SecondRailMode'] and
         !$GAME['SecondDevelopMode'] and
         !$GAME['ContinueSellingMode'] and
         $errorlist == ''
         ) {
        if ( $GAME['RailPhase'] and $GAME['HandSize'][$GAME['PlayerToMove']] == 1 ) {
            require_once(HIDDEN_FILES_PATH.'nomovesresource.php');
            if ( CheckNoMoves($GAME['Cards'][$GAME['PlayerToMove']][0]) ) {
                $GAME['AltGameTicker'] .= '9H'.letter_end_number($GAME['carddetailarrayb'][$GAME['Cards'][$GAME['PlayerToMove']][0]]);
                $GAME['DiscardPile'][] = array_pop($GAME['Cards'][$GAME['PlayerToMove']]);
                $GAME['HandSize'][$GAME['PlayerToMove']] = 0;
            }
        }
        $EvenTest = $GAME['HandSize'][$GAME['PlayerToMove']] % 2;
        if ( !$EvenTest or ( !$GAME['RailPhase'] and $GAME['Round'] == 1 ) ) {
            if ( $GAME['NumRounds'] - $GAME['Round'] > 3 ) {
                if ( $GAME['RailPhase'] or $GAME['Round'] != 1 ) {
                    $GAME['Cards'][$GAME['PlayerToMove']][6] = array_pop($GAME['ShuffledDeck']);
                    $GAME['HandSize'][$GAME['PlayerToMove']]++;
                }
                $GAME['Cards'][$GAME['PlayerToMove']][7] = array_pop($GAME['ShuffledDeck']);
                $GAME['HandSize'][$GAME['PlayerToMove']]++;
                if ( $GAME['AutoSort'][$GAME['PlayerToMove']] ) {
                    sort($GAME['Cards'][$GAME['PlayerToMove']]);
                }
            }
            for ($i=0;$i<MAX_PLAYERS;$i++) {
                if ( $GAME['TurnOrder'][$i] == $GAME['PlayerToMove'] ) {
                    $WhereInTurnOrder = $i;
                }
            }
            $EndRound = true;
            for ($i=MAX_PLAYERS-1;$i>$WhereInTurnOrder;$i--) {
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
            if ( $GAME['RailPhase'] and
                 $GAME['Round'] == $GAME['NumRounds'] and
                 $GAME['GameStatus'] != 'Finished'
                 ) {
                require_once(HIDDEN_FILES_PATH.'nomovesresource.php');
                CheckNoMovesShell();
            }
        }
    }
    return $errorlist;
}

?>