<?php

function startgame ($AutoStart) {
    // Within this function, many variables that in other functions would be keys in the $GAME array are instead stand-alone variables.
    // These variables are all ones that get initialised within this function.
    // Some variables, like CurrentPlayers, are keys in the $GAME array as usual.
    // These are variables initialised earlier and stored as usual in $GAME, whose values are required by this function.
    // In a third category there is also $AutoStart, which is the argument of this function.
    global $GAME;
    if ( $GAME['InitialTurnOrder'] == 'Random' ) {
        $j = rand(0, 23);
        $GAME['InitialTurnOrder'] = array('Red, Yellow, Green, Purple, Grey',
                                          'Red, Yellow, Green, Grey, Purple',
                                          'Red, Yellow, Purple, Green, Grey',
                                          'Red, Yellow, Purple, Grey, Green',
                                          'Red, Yellow, Grey, Green, Purple',
                                          'Red, Yellow, Grey, Purple, Green',
                                          'Red, Green, Yellow, Purple, Grey',
                                          'Red, Green, Yellow, Grey, Purple',
                                          'Red, Green, Purple, Yellow, Grey',
                                          'Red, Green, Purple, Grey, Yellow',
                                          'Red, Green, Grey, Yellow, Purple',
                                          'Red, Green, Grey, Purple, Yellow',
                                          'Red, Purple, Yellow, Green, Grey',
                                          'Red, Purple, Yellow, Grey, Green',
                                          'Red, Purple, Green, Yellow, Grey',
                                          'Red, Purple, Green, Grey, Yellow',
                                          'Red, Purple, Grey, Yellow, Green',
                                          'Red, Purple, Grey, Green, Yellow',
                                          'Red, Grey, Yellow, Green, Purple',
                                          'Red, Grey, Yellow, Purple, Green',
                                          'Red, Grey, Green, Yellow, Purple',
                                          'Red, Grey, Green, Purple, Yellow',
                                          'Red, Grey, Purple, Yellow, Green',
                                          'Red, Grey, Purple, Green, Yellow'
                                          );
        $GAME['InitialTurnOrder'] = $GAME['InitialTurnOrder'][$j];
            // Generate a random initial turn order.
    }
    $TurnOrder = explode(', ', $GAME['InitialTurnOrder']);
    $j = rand(0, MAX_PLAYERS-1);
    $TurnOrderX = array();
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $TurnOrderX[] = $TurnOrder[$j];
        $j = ( $j + 1 ) % 5;
            // Construct an initial turn order with a random start player;
            // the other colours cycle round according to the predetermined order.
    }
    $colourtonumber = array('Red'    => 0,
                            'Yellow' => 1,
                            'Green'  => 2,
                            'Purple' => 3,
                            'Grey'   => 4
                            );
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $TurnOrder[$i] = $colourtonumber[$TurnOrderX[$i]];
            // Convert the colour names to the standard numbering system.
    }
    $i = 0;
    while ( $i < MAX_PLAYERS ) {
        // Push colours that are in the game to the start of the turn order.
        if ( $GAME['PlayerExists'][$TurnOrder[$i]] ) {
            $i++;
        } else {
            $doneshifting = true;
            for ($j=$i;$j<MAX_PLAYERS-1;$j++) {
                $TurnOrder[$j] = $TurnOrder[$j+1];
                if ( $GAME['PlayerExists'][$TurnOrder[$j]] ) {
                    $doneshifting = false;
                }
            }
            if ( $doneshifting ) { break; }
        }
    }
    $j = 0;
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        // After the previous operation, some colours are duplicated at the end of the turn order.
        // We replace them with the colours that aren't in the game. After we've done this,
        // $TurnOrder will be an array consisting of the integers 0, 1, 2, 3 and 4 in some order.
        if ( !$GAME['PlayerExists'][$i] ) {
            $TurnOrder[$GAME['CurrentPlayers']+$j] = $i;
            $j++;
        }
    }
    $letters = 'ABCDE';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        $TurnOrderLetters[$i] = $letters[$TurnOrder[$i]];
    }
    $PlayerToMove = $TurnOrder[0];
        // Set the player whose turn it is, as the first player in the turn order.
    if ( $AutoStart ) {
        $GameTicker = 'A'.
                      letter_end_number(time());
        $GameTickerNames = '';
    } else {
        $GameTicker = 'B'.
                      letter_end_number(time()).
                      letter_end_number($_SESSION['MyUserID']).
                      letter_end_number($_SESSION['MyGenderCode']);
        $GameTickerNames = $_SESSION['MyUserName'].'|';
    }
    switch ( $GAME['CreatorPronoun'] ) {
        case 'He':  $ThePronoun = 'A'; break;
        case 'She': $ThePronoun = 'B'; break;
        case 'It':  $ThePronoun = 'C'; break;
    }
    $GameTicker .= letter_end_number(strtotime($GAME['CreationTime'])).
                   letter_end_number($GAME['GameCreator']).
                   $ThePronoun;
    $GameTickerNames .= $GAME['GameCreatorName'].'|';
    $PEnum = 0;
    $playerdetails = '';
    for ($i=0; $i<MAX_PLAYERS; $i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            switch ( $GAME['Pronoun_Eng'][$i] ) {
                case 'He':  $ThePronoun = 'A'; break;
                case 'She': $ThePronoun = 'B'; break;
                case 'It':  $ThePronoun = 'C'; break;
            }
            $playerdetails .= letter_end_number($GAME['PlayerUserID'][$i]).
                              $ThePronoun;
            if ( $PEnum ) { $GameTickerNames .= '|'; }
            $GameTickerNames .= $GAME['PlayerName'][$i];
            $PEnum += pow(2,$i);
        }
    }
    $GameTicker .= letter_end_number($PEnum).
                   implode('', $TurnOrderLetters).
                   $playerdetails;
        // Now we have finished writing the initial details for the game log.
    $TurnOrder = implode('',$TurnOrder);
        // Convert $TurnOrder into a string.
    if ( $GAME['CurrentPlayers'] == 2 ) {
        $ModularBoardParts = 0;
        if ( ( $GAME['SpecialRules'] & 2 ) == 0 ) { $ModularBoardParts += 8; }
        $ShuffledTiles = '12222334';
        $TwoPlayers = 1;
    } else {
        if ( $GAME['GVersion'] == 1 ) { $ModularBoardParts = 7; }
        else                          { $ModularBoardParts = 0; }
        $ShuffledTiles = '001122223334';
        $TwoPlayers = 0;
    }
        // Set up some things that depend on whether there are more than two players.
    $RandomLog = '';
    $ShuffledDeck = array();
    for ($i=0; $i<count($GAME['carddetailarrayb']); $i++) {
        if ( $GAME['CurrentPlayers'] > 2 or !in_array($i,$GAME['CardsToRemove']) ) {
            $ShuffledDeck[] = $i;
                // Create the deck of cards to be used in the Canal Phase.
        }
    }
    shuffle($ShuffledDeck);
        // Shuffle the deck of cards.
    //  $TranslationString = '';
    //  $TranslationSymbols = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXY';
    //  $TranslationSymbolsProgress = 0;
    //  $LastTranslationNumber = $GAME['carddetailarrayb'][0];
    //  for ($i=0;$i<count($GAME['carddetailarrayb']);$i++) {
    //      if ( $GAME['carddetailarrayb'][$i] == $LastTranslationNumber ) {
    //          $TranslationString .= $TranslationSymbols[$TranslationSymbolsProgress];
    //      } else {
    //          $LastTranslationNumber = $GAME['carddetailarrayb'][$i];
    //          $TranslationSymbolsProgress++;
    //          $i--;
    //      }
    //  }
    //  for ($i=0;$i<count($ShuffledDeck);$i++) {
    //      $RandomLog .= $TranslationString[$ShuffledDeck[$i]];
    //  }
    //      // The above commented-out section of code allows the ordering of the cards
    //      // in the deck to be recorded. At present, it is not in use. The character
    //      // 'Z' added to RandomLog (on the next line) stands for "card ordering not recorded".
        $RandomLog .= 'Z';
    $ShuffledTiles = str_shuffle($ShuffledTiles);
    $RandomLog .= $ShuffledTiles;
    $RatingTotal = 0;
    for ($i=0;$i<MAX_PLAYERS;$i++) {
        if ( $GAME['PlayerExists'][$i] ) {
            $RatingTotal += $GAME['PlayerRating'][$i];
            for ($j=0;$j<8;$j++) { $Cards[$i][$j] = array_pop($ShuffledDeck); }
            sort($Cards[$i]);
            $Cards[$i] = implode('|',$Cards[$i]);
                // Deal 8 cards to each player, and sort them into the standard order.
        } else {
            $Cards[$i] = '';
        }
    }
    $AverageRating      = $RatingTotal / $GAME['CurrentPlayers'];
    $AppearancePriority = $AverageRating / 2 + 500;
    $AverageRating      = (int)$AverageRating;
    $AppearancePriority = (int)$AppearancePriority;
        // Set up the variables that control how frequently the game appears on the Main Page.
    $Cards = implode(':',$Cards);
    $ShuffledDeck = implode('|',$ShuffledDeck);
    $MoneyAmount = 30 - 5 * $TwoPlayers;
    $Money = $MoneyAmount.'|'.$MoneyAmount.'|'.$MoneyAmount.'|'.$MoneyAmount.'|'.$MoneyAmount;
    if ( $GAME['CurrentPlayers'] == 4 ) { $NumRounds =  8; }
    else                                { $NumRounds = 10; }
    $LinkStatus = '';
    for ($i=0; $i<$GAME['NumCanalLinks']; $i++) {
        $LinkStatus .= '9';
        // Initialise the details of the canal links.
    }
    if ( strlen($LinkStatus) % 2 ) {
        $LinkStatus .= 9;
    }
    $SpaceStatus = '';
    $SpaceTile   = '';
    $TechLevels  = '';
    $SpaceCubes  = '';
    for ($i=0; $i<$GAME['NumIndustrySpaces']; $i++) {
        $SpaceStatus .= '9';
        $SpaceTile   .= '0';
        $TechLevels  .= '0';
        $SpaceCubes  .= '1';
        // Initialise the details of the industry spaces.
    }
    if ( strlen($SpaceStatus) % 2 ) {
        $SpaceStatus .= '9';
        $SpaceTile   .= '0';
        $TechLevels  .= '0';
        $SpaceCubes  .= '1';
    }
    $CoalNet = array();
    for ($i=0;$i<$GAME['NumTowns'];$i++) {
        $CoalNet[] = $i;
    }
    $CoalNet = implode('|',$CoalNet);
    $HasPort = implode('',$GAME['ExternalLocations']);
        // Initialise the data for the coal network (all locations are disconnected from one another),
        // and the data describing which locations have Ports (just the external locations).
    dbquery( DBQUERY_WRITE,
             'UPDATE "Game" SET "LinkStatus" = UNHEX(:linkstatus:), "SpaceStatus" = UNHEX(:spacestatus:), "SpaceTile" = UNHEX(:spacetile:), "TechLevels" = UNHEX(:techlevels:), "SpaceCubes" = UNHEX(:spacecubes:), "GameStatus" = \'In Progress\', "TurnOrder" = :turnorder:, "Money" = :money:, "AmountSpent" = \'0|0|0|0|0\', "GameTicker" = :gameticker:, "GameTickerNames" = :gametickernames:, "NumRounds" = :numrounds:, "EffectiveNumPlayers" = :currentplayers:, "OriginalPlayers" = :currentplayers:, "ModularBoardParts" = :modularboardparts:, "LastMove" = UTC_TIMESTAMP(), "RandomLog" = :randomlog: WHERE "GameID" = :gameid:',
             'linkstatus'        , $LinkStatus             ,
             'spacestatus'       , $SpaceStatus            ,
             'spacetile'         , $SpaceTile              ,
             'techlevels'        , $TechLevels             ,
             'spacecubes'        , $SpaceCubes             ,
             'turnorder'         , $TurnOrder              ,
             'money'             , $Money                  ,
             'gameticker'        , $GameTicker             ,
             'gametickernames'   , $GameTickerNames        ,
             'numrounds'         , $NumRounds              ,
             'currentplayers'    , $GAME['CurrentPlayers'] ,
             'modularboardparts' , $ModularBoardParts      ,
             'randomlog'         , $RandomLog              ,
             'gameid'            , $GAME['GameID']
             );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "GameInProgress" ("Game", "AverageRating", "AppearancePriority", "PlayerToMove", "LastEvent", "Cards", "HasPort", "CoalNet", "ShuffledDeck", "ShuffledTiles", "PlayerToMoveName", "PlayerToMoveID", "GIPTimeLimitA", "GIPTimeLimitB", "GIPLastMove") VALUES (:game:, :averagerating:, :appearancepriority:, :playertomove:, UTC_TIMESTAMP(), :cards:, :hasport:, :coalnet:, :shuffleddeck:, :shuffledtiles:, :playertomovename:, :playertomoveid:, :timelimita:, :timelimitb:, UTC_TIMESTAMP())',
             'game'               , $GAME['GameID']                      ,
             'averagerating'      , $AverageRating                       ,
             'appearancepriority' , $AppearancePriority                  ,
             'playertomove'       , $PlayerToMove                        ,
             'cards'              , $Cards                               ,
             'hasport'            , $HasPort                             ,
             'coalnet'            , $CoalNet                             ,
             'shuffleddeck'       , $ShuffledDeck                        ,
             'shuffledtiles'      , $ShuffledTiles                       ,
             'playertomovename'   , $GAME['PlayerName'][$PlayerToMove]   ,
             'playertomoveid'     , $GAME['PlayerUserID'][$PlayerToMove] ,
             'timelimita'         , $GAME['TimeLimitA_Minutes']          ,
             'timelimitb'         , $GAME['TimeLimitB_Minutes']
             );
    dbquery( DBQUERY_WRITE,
             'DELETE FROM "LobbySettings" WHERE "Game" = :game:',
             'game' , $GAME['GameID']
             );
    dbquery(DBQUERY_COMMIT);
    file_put_contents(NUM_MOVES_MADE_DIR.'g'.$GAME['GameID'].'.txt',0);
        // Set up the number-of-moves file for AJAX requests.
    $MetadatumNames = array('Games-InProgress','Games-Total','Games-Players'.$GAME['CurrentPlayers']);
    if ( !$GAME['Friendly'] ) {
        $MetadatumNames[] = 'Games-Competitive';
        $MetadatumNames[] = 'Games-Competitive-Players'.$GAME['CurrentPlayers'];
    }
    dbquery( DBQUERY_WRITE,
             'UPDATE "Metadatum" SET "MetadatumValue" = "MetadatumValue" + 1 WHERE "MetadatumName" IN (\''.implode('\', \'',$MetadatumNames).'\')'
             );
        // Update some statistics in the Metadatum table.
    if ( $GAME['Email'][$PlayerToMove] != '' and
         $GAME['EmailPrompt'][$PlayerToMove] and
         ( $GAME['EmailPromptAgain'][$PlayerToMove] or
           !$GAME['HasBeenEmailed'][$PlayerToMove]
           )
         ) {
        // Email the player who is first to move.
        $subject = 'You\'re first up to make a move';
        $body = '<p>This is an automated message. Game number '.
                $GAME['GameID'].
                ' has been started, and it\'s you to move first. Here is the URL to the game page:</p><p><a href="'.
                SITE_ADDRESS.
                'board.php?GameID='.
                $GAME['GameID'].
                '">'.
                SITE_ADDRESS.
                'board.php?GameID='.
                $GAME['GameID'].
                '</a></p>'.
                EMAIL_FOOTER;
        send_email($subject, $body, $GAME['Email'][$PlayerToMove], null);
        dbquery( DBQUERY_WRITE,
                 'UPDATE "User" SET "HasBeenEmailed" = 1 WHERE "UserID" = :userid:',
                 'userid' , $GAME['PlayerUserID'][$PlayerToMove]
                 );
    }
}

?>