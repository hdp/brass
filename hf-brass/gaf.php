<?php

function DoTask() {
    global $GAME;
    if ( $GAME['GameStatus'] != 'In Progress' and
         $GAME['GameStatus'] != 'Recruiting Replacement'
         ) {
        $mypage = page::standard();
        $mypage->title_body('Cannot abort this game');
        $mypage->leaf( 'p',
                       'This game cannot be aborted just now, perhaps because it has finished. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game, so you cannot vote to abort it. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( !$GAME['AbortVoteActive'] ) {
        $mypage = page::standard();
        $mypage->title_body('No vote is taking place');
        $mypage->leaf( 'p',
                       'At present, no vote on aborting the game is taking place. Perhaps somebody voted against aborting the game in the meantime. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( @$_POST['votevalue'] ) {
        if ( $GAME['PlayersVotingToAbort'] - $GAME['IHaveAbortVoted'] + 1 >= $GAME['CurrentPlayers'] ) {
            abortgame(0);
            dbformatgamedata();
            page::redirect( 3,
                            'board.php?GameID='.$GAME['GameID'],
                            'Successfully voted and aborted game.'
                            );
        }
        $GAME['AbortVote'][$GAME['MyColour']] = 1;
        dbformatgamedata();
        page::redirect( 3,
                        'board.php?GameID='.$GAME['GameID'],
                        'Successfully voted.'
                        );
    }
    $GAME['AbortVote'] = '00000';
    dbformatgamedata();
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] == 1 and
             !$GAME['PlayerMissing'][$i] and
             $i != $GAME['MyColour'] and
             $GAME['EmailPrompt'][$i] and
             $GAME['Email'][$i] != ''
             ) {
            $subject = 'The vote to abort game number '.$GAME['GameID'].' has failed';
            $body = '<p>This is an automated message. One of your fellow players in game number '.
                    $GAME['GameID'].
                    ' has voted against aborting the game. In order for the game to be aborted, all of the players must agree; this means that a unanimous vote is required. So, the game will not be aborted at this time. Here is the URL of the game page:</p><p><a href="'.
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