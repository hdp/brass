<?php

function DoTask() {
    global $Administrator, $Banned, $GAME, $Rating;
    if ( $GAME['GameStatus'] == 'Cancelled' and !$Administrator ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot find game');
        $mypage->leaf( 'p',
                       'Cannot find a game with that game ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['GameStatus'] == 'Cancelled' ) {
        $GAME['GameName_Page'] .= ' <span style="font-size: 150%; color: #FF0000;">[CANCELLED]</span>';
    }
    $CreationTimeText = date( 'Y-m-d H:i:s',
                              strtotime($GAME['CreationTime'])
                              );
    if ( $GAME['SpecialRules'] & 1 ) { $ReverseVCText = 'Yes'; }
    else                             { $ReverseVCText = 'No';  }
    if ( $GAME['SpecialRules'] & 2 ) { $ForbidSCText = 'Yes'; }
    else                             { $ForbidSCText = 'No';  }
    if ( $GAME['SpecialRules'] & 4 ) { $ORAWText = 'Yes'; }
    else                             { $ORAWText = 'No';  }
    if ( $GAME['AnyPlayerStarts'] ) { $APSText = 'Yes'; }
    else                            { $APSText = 'No';  }
    if ( $GAME['Friendly'] ) { $FriendText = 'Friendly';    }
    else                     { $FriendText = 'Competitive'; }
    if ( $GAME['AutoStart'] ) { $AutoStartText = 'Yes'; }
    else                      { $AutoStartText = 'No';  }
    if ( $GAME['GPrivate'] ) { $PrivateText = ' checked'; }
    else                     { $PrivateText = '';         }
    if      ( $GAME['TimeLimitA_Minutes'] <=   30 ) { $x = 5; $TimeLimitAAlert = 'class="bigalert"';           }
    else if ( $GAME['TimeLimitA_Minutes'] <=   90 ) { $x = 4; $TimeLimitAAlert = 'class="bigalert"';           }
    else if ( $GAME['TimeLimitA_Minutes'] <=  720 ) { $x = 3; $TimeLimitAAlert = 'class="alert"';              }
    else if ( $GAME['TimeLimitA_Minutes'] <= 1080 ) { $x = 2; $TimeLimitAAlert = 'class="alert"';              }
    else if ( $GAME['TimeLimitA_Minutes'] <= 1800 ) { $x = 1; $TimeLimitAAlert = 'style="font-weight: bold;"'; }
    else                                            { $x = 0; $TimeLimitAAlert = null;                         }
    if      ( $GAME['TimeLimitB_Minutes'] <=   60 ) { $y = 5; $TimeLimitBAlert = 'class="bigalert"';           }
    else if ( $GAME['TimeLimitB_Minutes'] <=  180 ) { $y = 4; $TimeLimitBAlert = 'class="bigalert"';           }
    if      ( $GAME['TimeLimitB_Minutes'] <= 1440 ) { $y = 3; $TimeLimitBAlert = 'class="alert"';              }
    else if ( $GAME['TimeLimitB_Minutes'] <= 2160 ) { $y = 2; $TimeLimitBAlert = 'class="alert"';              }
    else if ( $GAME['TimeLimitB_Minutes'] <= 3600 ) { $y = 1; $TimeLimitBAlert = 'style="font-weight: bold;"'; }
    else                                            { $y = 0; $TimeLimitBAlert = null;                         }
    $cbmessageID = max($x,$y);
    $ShowTimeLimitWarning = true;
    switch ( $cbmessageID ) {
        case 5:
            $TimeLimitsWarning = 'These time limits indicate that the game\'s creator probably wants to play the game in near-real time. You should only join if you have a good stretch of time to spare and are prepared to play the game in a sitting. Failure to play rapidly when it is your turn may result in your being kicked from the game.';
            $TimeLimitsWarningAttributes = 'class="bigalert"';
            break;
        case 4:
            $TimeLimitsWarning = 'These time limits indicate that the game\'s creator probably wants to play through the game in the space of a day or less. You should only join if you will be able to check back frequently to make your moves until the game is over. Failure to play rapidly when it is your turn may result in your being kicked from the game.';
            $TimeLimitsWarningAttributes = 'class="bigalert"';
            break;
        case 3:
            $TimeLimitsWarning = 'These time limits indicate that the game\'s creator probably wants players to check the game several times a day to make their moves. You should only join if you can make this commitment. Delaying for too long when it is your turn may result in your being kicked from the game.';
            $TimeLimitsWarningAttributes = 'class="alert"';
            break;
        case 2:
            $TimeLimitsWarning = 'These time limits indicate that the game\'s creator probably wants players to check the game at least a couple of times a day to make their moves. You should only join if you can make this commitment. Delaying for too long when it is your turn may result in your being kicked from the game.';
            $TimeLimitsWarningAttributes = 'class="alert"';
            break;
        case 1:
            $TimeLimitsWarning = 'These time limits indicate that the game\'s creator probably wants players to check the game at least once a day to make their moves. You should only join if you can make this commitment. Delaying for too long when it is your turn may result in your being kicked from the game.';
            $TimeLimitsWarningAttributes = 'style="font-weight: bold;"';
            break;
        default:
            $ShowTimeLimitWarning = false;
    }
    $mypage = page::standard();
    $mypage->script('lobby.js');
    $mypage->title_body('Brass - #'.$GAME['GameID'].' '.$GAME['GameName_Title']);
    $mypage->loginbox(array( 'Location' => 6               ,
                             'GameID'   => $GAME['GameID']
                             ));
    $FormOpen = false;
    if ( $GAME['GameStatus'] != 'Cancelled' and
         @$_SESSION['LoggedIn'] and
         !$Banned
         ) {
        $FormOpen = true;
    }
    $mypage->leaf('h3', $GAME['GameName_Page']);
    if ( $FormOpen ) {
        $mypage->opennode( 'form',
                           'action="lobby.php" method="POST"'
                           );
    }
    $mypage->leaf('p', 'Game details:');
    $mypage->opennode( 'table',
                       'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                       );
    $mypage->opennode('tr');
    $mypage->leaf( 'td',
                   'Created by:',
                   'align=right style="min-width: 350px;"'
                   );
    if ( $_SESSION['LoggedIn'] ) {
        $mypage->leaf( 'td',
                       '<a href="userdetails.php?UserID='.
                           $GAME['GameCreator'].
                           '">'.
                           $GAME['GameCreatorName'].
                           '</a>'
                       );
    } else {
        $mypage->leaf( 'td',
                       $GAME['GameCreatorName']
                       );
    }
    $mypage->next();
    $mypage->leaf( 'td',
                   'Creation time:',
                   'align=right'
                   );
    $mypage->leaf('td', $CreationTimeText);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Friendly or Competitive?',
                   'align=right'
                   );
    $mypage->leaf('td', $FriendText);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Game version:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   $GAME['VersionName'].
                       ' ('.$GAME['Creators'].')'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Can be started by any player?',
                   'align=right'
                   );
    $mypage->leaf('td', $APSText);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Starts automatically on reaching capacity?',
                   'align=right'
                   );
    $mypage->leaf('td', $AutoStartText);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Minimum number of players:',
                   'align=right'
                   );
    $mypage->leaf('td', $GAME['MinimumPlayers']);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Maximum number of players:',
                   'align=right'
                   );
    $mypage->leaf('td', $GAME['MaximumPlayers']);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Reverse Virtual Connection?',
                   'align=right'
                   );
    $mypage->leaf('td', $ReverseVCText);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Forbid the Canal Link to Scotland?',
                   'align=right'
                   );
    $mypage->leaf('td', $ForbidSCText);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Modified Coal Overbuild Rules?',
                   'align=right'
                   );
    $mypage->leaf('td', $ORAWText);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Initial Turn Order:',
                   'align=right'
                   );
    $mypage->leaf('td', $GAME['InitialTurnOrder']);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Talk Rules:',
                   'align=right'
                   );
    $mypage->leaf('td', $GAME['TalkRules']);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Time Limit A:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   $GAME['TimeLimitA'].' '.$GAME['TimeLimitAunit'],
                   $TimeLimitAAlert
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Time Limit B:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   $GAME['TimeLimitB'].' '.$GAME['TimeLimitBunit'],
                   $TimeLimitBAlert
                   );
    if ( $ShowTimeLimitWarning ) {
        $mypage->next();
        $mypage->leaf('td', '');
        $mypage->leaf( 'td',
                       $TimeLimitsWarning,
                       $TimeLimitsWarningAttributes
                       );
    }
    $mypage->next();
    $mypage->leaf( 'td',
                   'Behaviour at Time Limit B:',
                   'align=right'
                   );
    $mypage->leaf('td', $GAME['DoWhatAtB']);
    $mypage->next();
    $mypage->leaf( 'td',
                   'Minimum Player Rating:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   $GAME['MinimumRating'] ?
                       $GAME['MinimumRating'] :
                       'None'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Maximum Player Rating:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   is_null($GAME['MaximumRating']) ?
                       'None' :
                       $GAME['MinimumRating']
                   );
    if ( $_SESSION['LoggedIn'] and
         ( $GAME['MinimumRating'] or !is_null($GAME['MaximumRating']) )
         ) {
        $mypage->next();
        $mypage->leaf('td', '');
        $mypage->leaf( 'td',
                       '(Your player rating is '.$Rating.')'
                       );
    }
    $mypage->next('style="height: 10px;"');
    $mypage->leaf('td', '');
    $mypage->leaf('td', '&nbsp;');
    if ( $GAME['GameCreator'] == @$_SESSION['MyUserID'] ) {
        $IAmTheCreator = true;
    } else {
        $IAmTheCreator = false;
    }
    $EnglishColourNames = array('Red', 'Yellow', 'Green', 'Purple', 'Grey');
    $TranslatedColourNames = array( transtext('_colourRed')    ,
                                    transtext('_colourYellow') ,
                                    transtext('_colourGreen')  ,
                                    transtext('_colourPurple') ,
                                    transtext('_colourGrey')
                                    );
    $PlayerColours = array('FFC18A', 'FFFFAF', '9FFF9F', 'FFC6FF', 'C4C4C4');
    $SlotAvailable = false;
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $mypage->next();
        $mypage->leaf( 'td',
                       $TranslatedColourNames[$i].':',
                       'align=right style="background-color: #'.
                           $PlayerColours[$i].
                           ';"'
                       );
        echo '<td>';
        if ( !$GAME['PlayerExists'][$i] ) {
            echo '&nbsp;&mdash;';
            if ( $FormOpen and
                 @$_SESSION['LoggedIn'] and
                 !$Banned and
                 $GAME['MyColour'] == 50 and
                 $GAME['CurrentPlayers'] < $GAME['MaximumPlayers'] and
                 $Rating >= $GAME['MinimumRating'] and
                 ( is_null($GAME['MaximumRating']) or $Rating <= $GAME['MaximumRating'] )
                 ) {
                $SlotAvailable = true;
                echo '&nbsp;&nbsp;<input type="submit" name="FormSubmit" value="Join Game as '.
                     $EnglishColourNames[$i].
                     '" onClick="';
                if ( $ShowTimeLimitWarning ) { echo 'ConfirmTimeLimit('; }
                else                         { echo 'ChangeActionID(';   }
                echo (20+$i).')">';
            }
        } else if ( $GAME['MyColour'] == $i ) {
            echo '&nbsp;<b>You</b>';
            if ( $FormOpen and
                 $GAME['CurrentPlayers'] > 1 and
                 !$IAmTheCreator
                 ) {
                echo '&nbsp;&nbsp;<input type="submit" name="FormSubmit" value="Leave this game" onClick="ChangeActionID(31)">';
            }
        } else if ( @$_SESSION['LoggedIn'] ) {
            echo '&nbsp;<a href="userdetails.php?UserID='.$GAME['PlayerUserID'][$i].'">'.$GAME['PlayerName'][$i].'</a>';
            if ( $FormOpen and
                 ( $Administrator or $IAmTheCreator ) and
                 $GAME['CurrentPlayers'] > 1 and
                 $GAME['PlayerUserID'][$i] != $GAME['GameCreator']
                 ) {
                echo '&nbsp;&nbsp;<input type="submit" name="FormSubmit" value="Remove '.
                     $EnglishColourNames[$i].
                     ' from the game" onClick="ChangeActionID('.(10+$i).')">';
            }
        } else {
            echo '&nbsp;'.$GAME['PlayerName'][$i];
        }
        echo '</td>';
    }
    $mypage->closenode(2); // tr, table
    if ( $FormOpen and $SlotAvailable ) {
        $mypage->leaf( 'p',
                       '<input type="submit" name="FormSubmit" value="Join as Any" onClick="'.
                           ( $ShowTimeLimitWarning ?
                             'ConfirmTimeLimit' :
                             'ChangeActionID'
                             ).
                           '(28);">'
                       );
        if ( $GAME['GPrivate'] ) {
            $mypage->opennode('p');
            $mypage->text('This game is private. In order to join it, you must first enter the password here (the game password, <i>not</i> your user password):');
            $mypage->emptyleaf( 'input',
                                'type="text" name="Password" size=20 maxlength=20'
                                );
            $mypage->closenode(); // p
        }
    }
    if ( $FormOpen and $IAmTheCreator ) {
        $mypage->opennode('p');
        $mypage->text('As the creator of the game, you may change whether the game is private or not.');
        $mypage->text('<br>Game is private?');
        $mypage->emptyleaf( 'input',
                            'type="checkbox" name="GPrivateBox" value=1'.
                                $PrivateText
                            );
        $mypage->text('Password:');
        $mypage->emptyleaf( 'input',
                            'type="text" name="PasswordB" size=20 maxlength=20 value="'.
                                $GAME['Password'].
                                '"'
                            );
        $mypage->emptyleaf( 'input',
                            'type="submit" name="FormSubmit" value="Amend" onClick="ChangeActionID(32);"'
                            );
        $mypage->text('<br>(The "Password" field is ignored if the game is not private. If used, the password should be between 3 and 20 characters. Please note that game passwords are stored merely as plain text, and not in encrypted form like user passwords; they are intended only as a simple barrier to joining the game for people to whom you have not given the password.)');
        $mypage->closenode(); // p
    }
    if ( $FormOpen and
         ( ( $GAME['CurrentPlayers'] >= $GAME['MinimumPlayersAllowed'] and
             $Administrator
           ) or
           ( $GAME['CurrentPlayers'] >= $GAME['MinimumPlayers'] and
             ( $IAmTheCreator or
               ( $GAME['AnyPlayerStarts'] and
                 $GAME['MyColour'] != 50
                 )
               )
             )
           )
         ) {
        $mypage->opennode('p');
        $mypage->leaf( 'a',
                       'Start the game',
                       'href="startgame.php?GameID='.
                           $GAME['GameID'].
                           '&amp;NumPlayers='.
                           $GAME['CurrentPlayers'].
                           '"'
                       );
        if ( $GAME['CurrentPlayers'] < $GAME['MinimumPlayers'] ) {
            $mypage->text('<br>(note: it does not have its "minimum number" of players yet)');
        }
        if ( !$GAME['AnyPlayerStarts'] and !$IAmTheCreator ) {
            $mypage->text('<br>(note: the game creator does not intend others to start it)');
        }
        $mypage->closenode(); // p
    }
    if ( $FormOpen and
         ( $Administrator or $IAmTheCreator )
         ) {
        $mypage->leaf( 'p',
                       '<input type="submit" name="FormSubmit" value="Cancel this game" onClick="ChangeActionID(30);">'
                       );
    }
    if ( $FormOpen and $Administrator ) {
        if ( $GAME['GTitleDeletedByAdmin'] ) {
            $mypage->leaf( 'p',
                           '<input type="submit" name="FormSubmit" value="Unclear title" onClick="ChangeActionID(34);">'
                           );
        } else {
            $mypage->leaf( 'p',
                           '<input type="submit" name="FormSubmit" value="Clear title" onClick="ChangeActionID(33);">'
                           );
        }
    }
    if ( $FormOpen ) {
        $mypage->emptyleaf( 'input',
                            'type="hidden" name="GameID" value="'.
                                $GAME['GameID'].
                                '"'
                            );
        $mypage->emptyleaf( 'input',
                            'type="hidden" name="FormActionID" id="FormActionIDid" value=0'
                            );
        $mypage->closenode(); // form
    }
    require(HIDDEN_FILES_PATH.'displaythread.php');
    if ( $Administrator ) {
        if ( $GAME['Closed'] == 'Forced Closed' ) { $StringFC = ' selected'; }
        else                                      { $StringFC = '';          }
        if ( $GAME['Closed'] == 'Closed' ) { $StringC = ' selected'; }
        else                               { $StringC = '';          }
        if ( $GAME['Closed'] == 'Forced Open' ) { $StringFO = ' selected'; }
        else                                    { $StringFO = '';          }
        if ( $GAME['Closed'] == 'Open' ) { $StringO = ' selected'; }
        else                             { $StringO = '';          }
        echo '<form action="threadview.php" method="POST"><p>Thread is <select name="Closedness"><option value="FC"'.$StringFC.'>Forced Closed<option value="C"'.$StringC.'>Closed<option value="FO"'.$StringFO.'>Forced Open<option value="O"'.$StringO.'>Open</select><input type="hidden" name="ThreadSticky" value="0"> --- <input type="submit" name="FormSubmitA" value="Execute"></p><input type="hidden" name="WhichThread" value='.$GAME['Thread'].'></form>';
    } else {
        if ( $GAME['Closed'] == 'Closed' or
             $GAME['Closed'] == 'Forced Closed'
             ) {
            $mypage->leaf( 'h3',
                           'This game\'s discussion thread has been closed by an Administrator.'
                           );
        }
    }
    displaythread($GAME['Thread'], $GAME['Closed'], 1, $Administrator, $Banned);
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

?>