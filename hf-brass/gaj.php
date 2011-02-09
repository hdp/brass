<?php

function DoTask() {
    global $Banned, $GAME;
    if ( @$_POST['Counts'] )  { $ToCount = 1; }
    else                      { $ToCount = 0; }
    if ( $GAME['RailPhase'] ) { $ToCount = 0; }
    $ChosenColour = sanitise_int( @$_POST['colourtoreplace'],
                                  SANITISE_NO_FLAGS,
                                  0,
                                  MAX_PLAYERS - 1
                                  );
    $PostFailureTitle = false;
    do {
        if ( !$GAME['PlayersMissing'] ) {
            $PostFailureTitle = 'No missing players';
            $PostFailureMessage = 'There aren\'t any players missing from this game at the moment. (Perhaps something happened after you loaded the board page.) Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( ( $GAME['GameStatus'] != 'In Progress' and
               $GAME['GameStatus'] != 'Recruiting Replacement'
               ) or
             !$GAME['PlayersMissingThatMatter']
             ) {
            $PostFailureTitle = 'No replacements needed';
            $PostFailureMessage = 'No replacement players are needed for this game at the moment. (Perhaps something happened after you loaded the board page.) Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( !$GAME['PlayerMissingAndMatters'][$ChosenColour] ) {
            $PostFailureTitle = 'Cannot join as selected colour';
            $PostFailureMessage = 'You cannot join this game as the selected colour. (Perhaps something happened after you loaded the board page.) Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['MyColour'] != 50 ) {
            $PostFailureTitle = 'Already playing in this game';
            $PostFailureMessage = 'You cannot join this game as a replacement, because you are already playing in it. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $Banned ) {
            $PostFailureTitle = 'Banned';
            $PostFailureMessage = 'You cannot join this game as a replacement, because you are banned. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( in_array($_SESSION['MyUserID'], $GAME['ReplacementOffers_Users']) ) {
            $PostFailureTitle = 'Request already received';
            $PostFailureMessage = 'You have already made a request to join this game. You might be seeing this message because you clicked the button twice. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( dbquery( DBQUERY_READ_RESULTSET,
                      'SELECT "User" FROM "PlayerGameRcd" WHERE "User" = :user: AND "Game" = :game:',
                      'user' , $_SESSION['MyUserID'] ,
                      'game' , $GAME['GameID']
                      ) !== 'NONE' ) {
            $PostFailureTitle = 'You have previously played in this game';
            $PostFailureMessage = 'You have previously played in this game. It is not possible to re-join the game. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
    } while ( false );
    if ( $PostFailureTitle !== false ) {
        $mypage = page::standard();
        $mypage->title_body($PostFailureTitle);
        $mypage->leaf('p', $PostFailureMessage);
        $mypage->finish();
    }
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "ReplacementOffer" ("Game", "User", "Colour", "ToCount") VALUES (:game:, :user:, :colour:, :tocount:)',
             'game'    , $GAME['GameID']       ,
             'user'    , $_SESSION['MyUserID'] ,
             'colour'  , $ChosenColour         ,
             'tocount' , $ToCount
             );
    if ( $ChosenColour == $GAME['PlayerToMove'] ) {
        $GAME['AltGameTicker'] .= '8A'.
                                  callmovetimediff().
                                  letter_end_number($_SESSION['MyUserID']).
                                  letter_end_number($_SESSION['MyGenderCode']);
        $GAME['GameTickerNames'] .= '|'.$_SESSION['MyUserName'];
        dbformatgamedata();
        for ($i=0; $i<MAX_PLAYERS; $i++) {
            if ( $GAME['PlayerExists'][$i] == 1 and
                 !$GAME['PlayerMissing'][$i] and
                 $GAME['EmailPrompt'][$i] and
                 $GAME['Email'][$i] != ''
                 ) {
                $subject = $_SESSION['MyUserName'].' wants to join game number '.$GAME['GameID'].' as a replacement';
                $body = '<p>This is an automated message. '.
                        $_SESSION['MyUserName'].
                        ' has asked to join game number '.
                        $GAME['GameID'].
                        ' as a replacement. As you are playing in this game, you can choose to accept this request. Here is the URL of the game page:</p><p><a href="'.
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
    } else {
        dbformatgamedata();
    }
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully requested to join.'
                    );
}

?>