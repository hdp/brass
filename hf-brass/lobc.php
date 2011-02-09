<?php

function DoTask() {
    global $Banned, $Email, $EmailPrompt, $EmailPromptAgain, $GAME, $HasBeenEmailed, $Rating;
    $EscapedPassword = sanitise_str( @$_POST['Password'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     );
    $PostFailureTitle = false;
    do {
        if ( !$_SESSION['LoggedIn'] ) {
            $PostFailureTitle = 'Not logged in';
            $PostFailureMessage = 'You cannot join this game, because you are not logged in. Please log in and then try again. Click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $Banned ) {
            $PostFailureTitle = 'Banned';
            $PostFailureMessage = 'You cannot join this game, because you are banned. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['GameStatus'] == 'Cancelled' ) {
            $PostFailureTitle = 'Game cancelled';
            $PostFailureMessage = 'This game has been cancelled. Please click <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['GameStatus'] != 'Recruiting' ) {
            $PostFailureTitle = 'Game has already started';
            $PostFailureMessage = 'This game has now been started, so you cannot join it. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['MyColour'] != 50 ) {
            $PostFailureTitle = 'Already playing';
            $PostFailureMessage = 'You cannot join this game, because you are playing in it already. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['GPrivate'] and
             $GAME['Password'] != $EscapedPassword
             ) {
            $PostFailureTitle = 'Incorrect password';
            $PostFailureMessage = 'The password you entered is incorrect. Please note that the password you need to enter here is <b>not</b> your user account password, but a different password that has been set by the game\'s creator to control access to the game. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['CurrentPlayers'] >= $GAME['MaximumPlayers'] ) {
            $PostFailureTitle = 'Game is full';
            $PostFailureMessage = 'You cannot join this game, because it has already reached its maximum number of players. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $GAME['MinimumRating'] > $Rating ) {
            $PostFailureTitle = 'Player rating too low';
            $PostFailureMessage = 'You cannot join this game, because your player rating is too low. Please click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( !is_null($GAME['MaximumRating']) and
             $GAME['MaximumRating'] < $Rating
             ) {
            $PostFailureTitle = 'Player rating too high';
            $PostFailureMessage = 'You cannot join this game, because your player rating is too high. Please click <a href="lobby.php?GameID='.
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
    $GAME['CurrentPlayers']++;
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( !$GAME['PlayerExists'][$i] ) {
            $GAME['PlayerExists'][$i]     = 1;
            $GAME['PlayerUserID'][$i]     = $_SESSION['MyUserID'];
            $GAME['PlayerName'][$i]       = $_SESSION['MyUserName'];
            $GAME['PlayerRating'][$i]     = $Rating;
            $GAME['Email'][$i]            = $Email;
            $GAME['EmailPrompt'][$i]      = $EmailPrompt;
            $GAME['EmailPromptAgain'][$i] = $EmailPromptAgain;
            $GAME['HasBeenEmailed'][$i]   = $HasBeenEmailed;
            switch ( $_SESSION['MyGenderCode'] ) {
                case 0:  $GAME['Pronoun_Eng'][$i] = 'He';  break;
                case 1:  $GAME['Pronoun_Eng'][$i] = 'She'; break;
                default: $GAME['Pronoun_Eng'][$i] = 'It';
            }
            dbquery( DBQUERY_WRITE,
                     'UPDATE "Game" SET "CurrentPlayers" = :currentplayers:, "PlayerExists" = :playerexists: WHERE "GameID" = :game:',
                     'currentplayers' , $GAME['CurrentPlayers'] ,
                     'playerexists'   , $GAME['PlayerExists']   ,
                     'game'           , $GAME['GameID']
                     );
            dbquery( DBQUERY_WRITE,
                     'INSERT INTO "PlayerGameRcd" ("User", "Game", "GameResult", "Inherited", "GameCounts", "Colour", "NumLongTurns", "CurrentOccupant") VALUES (:user:, :game:, \'Playing\', 0, 1, :colour:, 0, 0)',
                     'user'   , $_SESSION['MyUserID'] ,
                     'game'   , $GAME['GameID']       ,
                     'colour' , $i
                     );
            break;
        }
    }
    if ( $GAME['CurrentPlayers'] == $GAME['MaximumPlayers'] ) {
        if ( $GAME['AutoStart'] ) {
            require(HIDDEN_FILES_PATH.'sgresource.php');
            startgame(true);
            page::redirect( 3,
                            'board.php?GameID='.$GAME['GameID'],
                            'Successfully joined and started game.'
                            );
        } else if ( $GAME['CreatorEmailPrompt'] and
                    $GAME['CreatorEmail'] != ''
                    ) {
            $subject = 'Game is ready to be started';
            $body = '<p>Your game, number '.
                    $GAME['GameID'].
                    ', now has a full complement of players. Please review the game\'s lobby page and if you approve of the players, start the game. The URL of the lobby page is:</p><p><a href="'.
                    SITE_ADDRESS.
                    'lobby.php?GameID='.
                    $GAME['GameID'].
                    '">'.
                    SITE_ADDRESS.
                    'lobby.php?GameID='.
                    $GAME['GameID'].
                    '</a></p>'.
                    EMAIL_FOOTER;
            send_email($subject, $body, $GAME['CreatorEmail'], null);
        }
    }
    dbquery(DBQUERY_COMMIT);
    page::redirect( 3,
                    'lobby.php?GameID='.$GAME['GameID'],
                    'Successfully joined game.'
                    );
}

?>