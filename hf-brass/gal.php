<?php
function DoTask() {
    global $Administrator, $GAME, $unexpectederrormessage;
    if ( $GAME['GameStatus'] != 'Recruiting Replacement' ) {
        $mypage = page::standard();
        $mypage->title_body('Current player is not missing');
        $mypage->leaf( 'p',
                       'The current player is not missing. (Perhaps somebody accepted a replacement player after you had loaded the board page.) Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game, so you cannot choose replacement players. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $ReplacementValid = false;
    for ($i=0; $i<count($GAME['ReplacementOffers']); $i++) {
        if ( $GAME['ReplacementOffers'][$i][2] == $GAME['PlayerToMove'] and
             $GAME['ReplacementOffers'][$i][0] == @$_POST['whotoaccept']
             ) {
            $ReplacementValid = $i;
        }
    }
    if ( $ReplacementValid === false ) {
        $mypage = page::standard();
        $mypage->title_body('User ID not found among replacements');
        $mypage->leaf( 'p',
                       'The submitted user ID was not found among the candidate replacements. (Perhaps the user withdrew his request after you had loaded the board page.) Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "Name", "Pronoun", "Email", "EmailPrompt" FROM "User" WHERE "UserID" = :user:',
                   'user' , $GAME['ReplacementOffers'][$ReplacementValid][0]
                   );
    if ( $QR === 'NONE' ) {
        myerror( $unexpectederrormessage,
                 'Candidate replacements list contains invalid user'
                 );
    }
    switch ( $QR['Pronoun'] ) {
        case 'He':  $RepPronoun = 'A'; break;
        case 'She': $RepPronoun = 'B'; break;
        default:    $RepPronoun = 'C'; break;
    }
    $GAME['AltGameTicker'] .= '8E'.
                              callmovetimediff().
                              letter_end_number($GAME['ReplacementOffers'][$ReplacementValid][0]).
                              $RepPronoun.
                              letter_end_number($GAME['MyColour']);
    $GAME['GameTickerNames'] .= '|'.$GAME['ReplacementOffers'][$ReplacementValid][1];
    $GAME['PlayerName'][$GAME['PlayerToMove']] = $QR['Name'];
    $GAME['KickVote']   = '00000';
    $GAME['GameStatus'] = 'In Progress';
    $GAME['MoveMade']   = 1;
    dbquery( DBQUERY_WRITE,
             'DELETE FROM "ReplacementOffer" WHERE "Game" = :game: AND "Colour" = :colour:',
             'game'   , $GAME['GameID']       ,
             'colour' , $GAME['PlayerToMove']
             );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "PlayerGameRcd" ("User", "Game", "GameResult", "Inherited", "GameCounts", "Colour", "NumLongTurns", "CurrentOccupant") VALUES (:user:, :game:, \'Playing\', 1, :counts:, :colour:, 0, 0)',
             'user'   , $GAME['ReplacementOffers'][$ReplacementValid][0] ,
             'game'   , $GAME['GameID']                                  ,
             'counts' , $GAME['ReplacementOffers'][$ReplacementValid][3] ,
             'colour' , $GAME['PlayerToMove']
             );
    dbformatgamedata();
    if ( $QR['EmailPrompt'] and
         $QR['Email'] != ''
         ) {
        $subject = 'Your request to join game number '.$GAME['GameID'].' has been accepted';
        $body = '<p>This is an automated message. One of the players in game number '.
                $GAME['GameID'].
                ' has accepted your request to join the game as a replacement player. This means that it is now your turn to move; please visit the game\'s page and take your turn. Here is the URL of the game page:</p><p><a href="'.
                SITE_ADDRESS.
                'board.php?GameID='.
                $GAME['GameID'].
                '">'.
                SITE_ADDRESS.
                'board.php?GameID='.
                $GAME['GameID'].
                '</a></p>'.
                EMAIL_FOOTER;
        send_email($subject, $body, $QR['Email'], null);
    }
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully accepted replacement.'
                    );
}

?>