<?php

function DoTask() {
    global $GAME, $PlayersVotingToKick;
    if ( $GAME['GameStatus'] != 'Recruiting Replacement' ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot vote to downsize this game');
        $mypage->leaf( 'p',
                       'This game is not currently seeking a replacement player, so you cannot vote to downsize it. (Perhaps something happened after you loaded the board page.) Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['CurrentPlayers'] == 3 ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot downsize a 3-player game');
        $mypage->leaf( 'p',
                       'At present, it is not permitted for a 3-player game to be downsized. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['CurrentPlayers'] == $GAME['MinimumPlayersAllowed'] and
         $GAME['MinimumPlayersAllowed'] > 2
         ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot downsize from this number of players');
        $mypage->leaf( 'p',
                       'This game cannot be downsized, as the game board in use does not support fewer than '.
                           $GAME['MinimumPlayersAllowed'].
                           ' players. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game, so you cannot vote to downsize it. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( @$_POST['DSVote'] == 'Yes' ) {
        if ( $GAME['PlayersVotingToKick'] - $GAME['IHaveKickVoted'] + 1 >= $GAME['CurrentPlayers'] ) {
            require(HIDDEN_FILES_PATH.'downsizeresource.php');
            downsizegame(false);
            $didsomething = 1;
            while ( $didsomething ) {
                $didsomething = gamecheck();
            }
            dbformatgamedata();
            page::redirect( 3,
                            'board.php?GameID='.$GAME['GameID'],
                            'Successfully voted and downsized game.'
                            );
        }
        $GAME['KickVote'][$GAME['MyColour']] = 1;
        dbformatgamedata();
        if ( !$GAME['KickVoteActive'] ) {
            for ($i=0; $i<MAX_PLAYERS; $i++) {
                if ( $GAME['PlayerExists'][$i] == 1 and
                     !$GAME['PlayerMissing'][$i] and
                     !$GAME['PlayersVotingToKick'] and
                     $i != $GAME['MyColour'] and
                     $GAME['EmailPrompt'][$i] and
                     $GAME['Email'][$i] != ''
                     ) {
                    $subject = 'There is a vote to downsize game number '.$GAME['GameID'];
                    $body = '<p>This is an automated message. One of your fellow players in game number '.
                            $GAME['GameID'].
                            ' has initiated a vote to downsize the game instead of recruiting a replacement player. In order for the game to be downsized, all of the players must agree; this means that a unanimous vote is required. If you would like the game to be downsized, then please visit the game\'s page and vote in favour of downsizing. If you do not want the game to be downsized, then no action is required other than to accept a replacement if one asks to join. (You will be emailed if this happens. Bear in mind that if a replacement is not found before the Time Limit B for this game is exceeded, then the game will be downsized anyway.) Here is the URL of the game page:</p><p><a href="'.
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
                }
            }
        }
        page::redirect( 3,
                        'board.php?GameID='.$GAME['GameID'],
                        'Successfully voted.'
                        );
    }
    $GAME['KickVote'][$GAME['MyColour']] = 0;
    dbformatgamedata();
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully voted.'
                    );
}

?>