<?php

function DoTask() {
    global $GAME;
    $EscapedNewPassword = sanitise_str( @$_POST['PasswordB'],
                                        STR_GPC |
                                            STR_ESCAPE_HTML |
                                            STR_STRIP_TAB_AND_NEWLINE
                                        );
    $PostFailureTitle = false;
    do {
        if ( !$_SESSION['LoggedIn'] ) {
            $PostFailureTitle = 'Not logged in';
            $PostFailureMessage = 'You are not logged in. Please log in and then try again. Click <a href="lobby.php?GameID='.
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
            $PostFailureMessage = 'This game has now been started. Please click <a href="board.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( $_SESSION['MyUserID'] != $GAME['GameCreator'] ) {
            $PostFailureTitle = 'Cannot change game settings';
            $PostFailureMessage = 'You cannot change the game\'s settings, because you are not the game\'s creator. Click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( @$_POST['GPrivateBox'] and
             mb_strlen($EscapedNewPassword, 'UTF-8') > 20
             ) {
            $PostFailureTitle = 'New password too long';
            $PostFailureMessage = 'The new password you entered is too long. Maximum 20 characters. Click <a href="lobby.php?GameID='.
                                  $GAME['GameID'].
                                  '">here</a> to return to the lobby page, or <a href="index.php">here</a> to return to the Main Page.';
            break;
        }
        if ( @$_POST['GPrivateBox'] and
             mb_strlen($EscapedNewPassword, 'UTF-8') < 3
             ) {
            $PostFailureTitle = 'New password too short';
            $PostFailureMessage = 'The new password you entered is too short. Minimum 3 characters. Click <a href="lobby.php?GameID='.
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
    if ( @$_POST['GPrivateBox'] ) {
        $NewPrivValue = 1;
    } else {
        $NewPrivValue = 0;
        $EscapedNewPassword = mb_substr( $EscapedNewPassword,
                                         0,
                                         20,
                                         'UTF-8'
                                         );
    }
    dbquery( DBQUERY_WRITE,
             'UPDATE "LobbySettings" SET "GPrivate" = :private:, "Password" = :pass: WHERE "Game" = :game:',
             'private' , $NewPrivValue       ,
             'game'    , $GAME['GameID']     ,
             'pass'    , $EscapedNewPassword
             );
    page::redirect( 3,
                    'lobby.php?GameID='.$GAME['GameID'],
                    'Game settings successfully changed.'
                    );
}

?>