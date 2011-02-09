<?php

function DoTask() {
    global $GAME;
    if ( $GAME['MyColour'] == 50 ) {
        $mypage = page::standard();
        $mypage->title_body('Not playing in this game');
        $mypage->leaf( 'p',
                       'You are not currently playing in this game. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( @$_POST['FirstCardNotMyTurn'] == 'NoCardSelected' or
         @$_POST['SecondCardNotMyTurn'] == 'NoCardSelected'
         ) {
        $mypage = page::standard();
        $mypage->title_body('Cards not selected');
        $mypage->leaf( 'p',
                       'You omitted to select a card in one or both of the selection lists. Please select a card in each list and then try again. Click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $FC = sanitise_int( @$_POST['FirstCardNotMyTurn'],
                        SANITISE_NO_FLAGS,
                        0,
                        $GAME['HandSize'][$GAME['MyColour']] - 1
                        );
    $SC = sanitise_int( @$_POST['SecondCardNotMyTurn'],
                        SANITISE_NO_FLAGS,
                        0,
                        $GAME['HandSize'][$GAME['MyColour']] - 1
                        );
    if ( $FC == $SC ) {
        $mypage = page::standard();
        $mypage->title_body('Same cards selected');
        $mypage->leaf( 'p',
                       'You selected the same card in each selection list. Please select a different card in each list and then try again. Click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $CardSwitch = $GAME['Cards'][$GAME['MyColour']][$FC];
    $GAME['Cards'][$GAME['MyColour']][$FC] = $GAME['Cards'][$GAME['MyColour']][$SC];
    $GAME['Cards'][$GAME['MyColour']][$SC] = $CardSwitch;
    dbformatgamedata();
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully swapped cards.'
                    );
}

?>