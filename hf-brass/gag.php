<?php

function DoTask() {
    global $GAME,$unexpectederrormessage;
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
    if ( $GAME['KickVoteActive'] ) {
        myerror( $unexpectederrormessage,
                 'Unexpected vote page "gag.php" reached'
                 );
    }
    if ( $GAME['CurrentPlayers'] - $GAME['PlayersMissing'] == 2 ) {
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
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] == 1 and
             !$GAME['PlayerMissing'][$i] and
             $i != $GAME['MyColour'] and
             $GAME['EmailPrompt'][$i] and
             $GAME['Email'][$i] != ''
             ) {
            if ( $i == $GAME['PlayerToMove'] ) {
                $subject = 'There is a vote to kick you from game number '.$GAME['GameID'];
                $body = 'This is an automated message. One of your fellow players in game number '.
                        $GAME['GameID'].
                        ' has initiated a vote to kick you from the game, as you are taking a long time to make your move. In order for you to be kicked, all of the other players must agree; this means that a unanimous vote is required. However, you can end the vote immediately by taking your turn. Please visit the game\'s page and shut the vote down by taking your turn. Here is the URL of the game page:'.
                        "\n\n".
                        SITE_ADDRESS.
                        'board.php?GameID='.
                        $GAME['GameID'].
                        EMAIL_FOOTER;
            } else {
                $subject = 'There is a vote to kick '.$GAME['PlayerFullName'][$GAME['PlayerToMove']].
                           ' from game number '.$GAME['GameID'];
                $body = '<p>This is an automated message. One of your fellow players in game number '.
                        $GAME['GameID'].
                        ' has initiated a vote to kick '.
                        $GAME['PlayerFullName'][$GAME['PlayerToMove']].
                        ' from the game, as '.
                        $GAME['PronounLC'][$GAME['PlayerToMove']].
                        ' is taking a long time to make '.
                        $GAME['PossessivePronounLC'][$GAME['PlayerToMove']].
                        ' move. In order for '.
                        $GAME['OtherPronounLC'][$GAME['PlayerToMove']].
                        ' to be kicked, all of the players must agree; this means that a unanimous vote is required. Please visit the game\'s page and either vote in favour of kicking '.
                        $GAME['PlayerName'][$GAME['PlayerToMove']].
                        ', or shut the vote down by voting not to kick '.
                        $GAME['OtherPronounLC'][$GAME['PlayerToMove']].
                        '. Here is the URL of the game page:</p><p><a href="'.
                        SITE_ADDRESS.
                        'board.php?GameID='.
                        $GAME['GameID'].
                        '">'.
                        SITE_ADDRESS.
                        'board.php?GameID='.
                        $GAME['GameID'].
                        '</a></p>'.
                        EMAIL_FOOTER;
            }
            send_email($subject, $body, $GAME['Email'][$i], null);
        }
    }
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully voted.'
                    );
}

?>