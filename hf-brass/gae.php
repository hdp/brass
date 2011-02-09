<?php

function DoTask() {
    global $GAME, $unexpectederrormessage;
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
    if ( $GAME['AbortVoteActive'] ) {
        myerror( $unexpectederrormessage,
                 'Unexpected vote page "gae.php" reached'
                 );
    }
    if ( $GAME['CurrentPlayers'] - $GAME['PlayersMissing'] == 1 ) {
        abortgame(0);
        dbformatgamedata();
        page::redirect( 3,
                        'board.php?GameID='.$GAME['GameID'],
                        'Successfully voted and aborted game.'
                        );
    }
    $GAME['AbortVote'][$GAME['MyColour']] = 1;
    dbformatgamedata();
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] == 1 and
             !$GAME['PlayerMissing'][$i] and
             $i != $GAME['MyColour'] and
             $GAME['EmailPrompt'][$i] and
             $GAME['Email'][$i] != ''
             ) {
            $subject = 'There is a vote to abort game number '.$GAME['GameID'];
            $body = '<p>This is an automated message. One of your fellow players in game number '.
                    $GAME['GameID'].
                    ' has initiated a vote to abort the game. In order for the game to be aborted, all of the players must agree; this means that a unanimous vote is required. Please visit the game\'s page and either vote in favour of aborting it, or shut the vote down by voting not to abort. Here is the URL of the game page:</p><p><a href="'.
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