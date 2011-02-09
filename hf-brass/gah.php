<?php

function DoTask() {
    global $GAME;
    if ( $GAME['GameStatus'] != 'In Progress' ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot vote to remove players from this game');
        $mypage->leaf( 'p',
                       'At the moment you cannot vote to remove players from this game. This might be because the game has finished, or it might be because the game is seeking a replacement player. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game, so you cannot vote to remove players. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['MyColour'] == $GAME['PlayerToMove'] ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot vote to kick yourself');
        $mypage->leaf( 'p',
                       'You cannot vote to kick yourself. If you want to leave the game, please select the option to "Quit" instead. Click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( !$GAME['CanKickVote'] ) {
        $mypage = page::standard();
        $mypage->title_body('Kick vote not currently available');
        $mypage->leaf( 'p',
                       'The option to vote to kick the current player is not presently available. (Perhaps the player you were waiting for took his turn in the meantime.) Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( !$GAME['KickVoteActive'] ) {
        $mypage = page::standard();
        $mypage->title_body('No vote is taking place');
        $mypage->leaf( 'p',
                       'At present, no vote on kicking the current player is taking place. (Perhaps the player you were waiting for took his turn in the meantime, or perhaps another player voted against.) Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( @$_POST['votevalueA'] ) {
        if ( $GAME['PlayersVotingToKick'] - $GAME['IHaveKickVoted'] + 2 >= $GAME['CurrentPlayers'] ) {
            $NameOfKickedPlayer = $GAME['PlayerFullName'][$GAME['PlayerToMove']];
            KickPlayer($GAME['PlayerToMove'], 2);
            dbformatgamedata();
            page::redirect( 3,
                            'board.php?GameID='.$GAME['GameID'],
                            'Successfully voted and kicked player.'
                            );
        }
        $GAME['KickVote'][$GAME['MyColour']] = 1;
        dbformatgamedata();
        page::redirect( 3,
                        'board.php?GameID='.$GAME['GameID'],
                        'Successfully voted.'
                        );
    }
    $GAME['KickVote'] = '00000';
    dbformatgamedata();
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] == 1 and
             !$GAME['PlayerMissing'][$i] and
             $i != $GAME['MyColour'] and
             $GAME['EmailPrompt'][$i] and
             $GAME['Email'][$i] != '' and
             $i != $GAME['PlayerToMove']
             ) {
            $subject = 'The vote to kick '.$GAME['PlayerFullName'][$GAME['PlayerToMove']].
                       'from game number '.$GAME['GameID'].' has failed';
            $body = '<p>This is an automated message. One of your fellow players in game number '.
                    $GAME['GameID'].
                    ' has voted against kicking '.
                    $GAME['PlayerFullName'][$GAME['PlayerToMove']].
                    ' from the game. In order for '.
                    $GAME['OtherPronounLC'][$GAME['PlayerToMove']].
                    ' to be kicked, all of the players must agree; this means that a unanimous vote is required. So, '.
                    $GAME['PronounLC'][$GAME['PlayerToMove']].
                    ' will not be kicked at this time. Here is the URL of the game page:</p><p><a href="'.
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
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully voted.'
                    );
}

?>