<?php
require('_std-include.php');

$mypage = page::standard();
if ( !isset($_GET['GameID']) ) {
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'CALL "GameGetData_CardDistribution"(:game:)',
               'game' , (int)$_GET['GameID']
               );
if ( $QR === 'NONE' ) {
    $mypage->title_body('Game not found');
    $mypage->leaf( 'p',
                   'Could not find a game with that ID number.'
                   );
    $mypage->finish();
}

extract($QR);
if ( $GameStatus != 'In Progress' and
     $GameStatus != 'Recruiting Replacement'
     ) {
    $mypage->title_body('Game has finished');
    $mypage->leaf('p', 'This game has finished.');
    $mypage->finish();
}

$industrynames = array( transtext('_indCotnMill'),
                        transtext('_indCoalMine'),
                        transtext('_indIronWorks'),
                        transtext('_indPort'),
                        transtext('_indShipyard')
                        );
$CardDetailArray = explode('|',$CardDetailArray);
$LocationNames = explode('|',$LocationNames);
$playablecards = array();
$allcards = array();
if ( $DiscardPile != '' ) {
    $allcards = explode('|',$DiscardPile);
}
if ( $ShuffledDeck != '' ) {
    $ShuffledDeck = explode('|',$ShuffledDeck);
    while ( count($ShuffledDeck) ) {
        $newcard = array_pop($ShuffledDeck);
        $allcards[] = $newcard;
        $playablecards[] = $newcard;
    }
}
$Cards = explode(':',$Cards);
for ($i=0;$i<count($Cards);$i++) {
    if ( $Cards[$i] != '' ) {
        $Cards[$i] = explode('|',$Cards[$i]);
        while ( count($Cards[$i]) ) {
            $newcard = array_pop($Cards[$i]);
            $allcards[] = $newcard;
            $playablecards[] = $newcard;
        }
    }
}
sort($allcards);
sort($playablecards);

$mypage->title_body('Card distribution and remaining cards');
$mypage->opennode('h3');
$mypage->text('Remaining cards');
$mypage->leaf( 'span',
               '(this is a list of all cards in this game that are not accounted for, i.e. that have not been played)',
               'style="font-weight: normal;"'
               );
$mypage->closenode();
$mypage->opennode( 'ul',
                   'style="list-style: none; padding: 0px; margin: 0px; margin-top: 16px;"'
                   );
for ($i=0;$i<count($playablecards);$i++) {
    if ( $playablecards[$i] <= $TopLocationCard ) {
        $mypage->leaf( 'li',
                       $LocationNames[$CardDetailArray[$playablecards[$i]]]
                       );
    } else {
        $mypage->leaf( 'li',
                       $industrynames[$CardDetailArray[$playablecards[$i]]]
                       );
    }
}
$mypage->closenode();
$mypage->opennode('h3');
$mypage->text('Card distribution');
$mypage->leaf( 'span',
               '(includes cards that are in the discard pile, if any)',
               'style="font-weight: normal;"'
               );
$mypage->closenode();
$mypage->opennode( 'ul',
                   'style="list-style: none; padding: 0px; margin: 0px; margin-top: 16px;"'
                   );
for ($i=0;$i<count($allcards);$i++) {
    if ( $allcards[$i] <= $TopLocationCard ) {
        $mypage->leaf( 'li',
                       $LocationNames[$CardDetailArray[$allcards[$i]]]
                       );
    } else {
        $mypage->leaf( 'li',
                       $industrynames[$CardDetailArray[$allcards[$i]]]
                       );
    }
}
$mypage->finish(); // ul, body, html

?>