<?php

function DoTask() {
    global $Administrator, $GAME;
    if ( !$Administrator and
         $GAME['MyColour'] != $GAME['PlayerToMove']
         ) {
        $mypage = page::standard();
        $mypage->title_body('Not your turn');
        $mypage->leaf( 'p',
                       'You cannot use this method to swap your cards, as it is not currently your turn. Please click <a href="board.php?GameID='.
                           $GAME['GameID'].
                           '">here</a> to go to the board page, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    if ( @$_POST['FirstCard'] == 'NoCardSelected' or
         @$_POST['SecondCard'] == 'NoCardSelected'
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
    $FC = sanitise_int( @$_POST['FirstCard'],
                        SANITISE_NO_FLAGS,
                        0,
                        $GAME['HandSize'][$GAME['PlayerToMove']] - 1
                        );
    $SC = sanitise_int( @$_POST['SecondCard'],
                        SANITISE_NO_FLAGS,
                        0,
                        $GAME['HandSize'][$GAME['PlayerToMove']] - 1
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
    $CardSwitch = $GAME['Cards'][$GAME['PlayerToMove']][$FC];
    $GAME['Cards'][$GAME['PlayerToMove']][$FC] = $GAME['Cards'][$GAME['PlayerToMove']][$SC];
    $GAME['Cards'][$GAME['PlayerToMove']][$SC] = $CardSwitch;
    dbformatgamedata();
    page::redirect( 3,
                    'board.php?GameID='.$GAME['GameID'],
                    'Successfully swapped cards.'
                    );
}

?>