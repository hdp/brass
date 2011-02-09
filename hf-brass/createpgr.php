<?php

function CreatePGR($EventIsAbortion, $EventIsDeparture, $Score, $Position, $ThePlayer) {
    global $GAME;
    $GCounts = '';
    $IncrementCurrentOccupant = false;
    if ( $EventIsDeparture ) {
        $GCounts = ', "GameCounts" = 1';
        $IncrementCurrentOccupant = true;
        switch ( $EventIsDeparture ) {
            case 2:  $GameResult = 'Kicked by Admin';  break;
            case 3:  $GameResult = 'Kicked by Vote';   break;
            case 4:  $GameResult = 'Kicked by System'; break;
            default: $GameResult = 'Quit';
        }
    } else if ( $EventIsAbortion ) {
        $GameResult = 'Game Aborted';
    } else {
        $positionlabels = array('1st', '2nd', '3rd', '4th', '5th');
        $GameResult = 'Finished '.$positionlabels[$Position];
        if ( $Score >= 0 and
             dbquery( DBQUERY_READ_INTEGER,
                      'SELECT "GameCounts" FROM "PlayerGameRcd" WHERE "Game" = :game: AND "User" = :user:',
                      'game' , $GAME['GameID']                   ,
                      'user' , $GAME['PlayerUserID'][$ThePlayer]
                      )
             ) {
            dbquery( DBQUERY_WRITE,
                     'INSERT INTO "PGRScore" ("Game", "User", "Score", "NotWon", "Lost") VALUES (:game:, :user:, :score:, :notwon:, :lost:)',
                     'game'   , $GAME['GameID']                               ,
                     'user'   , $GAME['PlayerUserID'][$ThePlayer]             ,
                     'score'  , $Score                                        ,
                     'notwon' , (bool)$Position                               ,
                     'lost'   , $Position + 1 == $GAME['EffectiveNumPlayers']
                     );
        }
    }
    dbquery( DBQUERY_WRITE,
             'UPDATE "PlayerGameRcd" SET "GameResult" = :result:'.$GCounts.' WHERE "Game" = :game: AND "User" = :user:',
             'result' , $GameResult                       ,
             'game'   , $GAME['GameID']                   ,
             'user'   , $GAME['PlayerUserID'][$ThePlayer]
             );
    if ( $IncrementCurrentOccupant ) {
        dbquery( DBQUERY_WRITE,
                 'UPDATE "PlayerGameRcd" SET "CurrentOccupant" = "CurrentOccupant" + 1 WHERE "Game" = :game: AND "Colour" = :colour: ORDER BY "CurrentOccupant" DESC',
                 'game'   , $GAME['GameID'] ,
                 'colour' , $ThePlayer
                 );
    }
    dbquery( DBQUERY_WRITE,
             'CALL "CalculateRating"(:user:)',
             'user' , $GAME['PlayerUserID'][$ThePlayer]
             );
    $Incrementations = array('"NumGamesPlayed" = "NumGamesPlayed" + 1');
    if ( !$EventIsDeparture ) {
        $Incrementations[] = '"NumGamesCompleted" = "NumGamesCompleted" + 1';
    }
    switch ( $GAME['EffectiveNumPlayers'] ) {
        case 4:
            $Incrementations[] = '"NumGamesFourPlayer" = "NumGamesFourPlayer" + 1';
            break;
        case 3:
            $Incrementations[] = '"NumGamesThreePlayer" = "NumGamesThreePlayer" + 1';
            break;
        default:
            $Incrementations[] = '"NumGamesTwoPlayer" = "NumGamesTwoPlayer" + 1';
    }
    if ( !$GAME['Friendly'] ) {
        $Incrementations[] = '"NumCompetitiveGamesPlayed" = "NumCompetitiveGamesPlayed" + 1';
        if ( !$EventIsDeparture ) {
            $Incrementations[] = '"NumCompetitiveGamesCompleted" = "NumCompetitiveGamesCompleted" + 1';
        }
        if ( !$EventIsDeparture and !$EventIsAbortion and !$Position ) {
            $Incrementations[] = '"NumCompetitiveGamesWon" = "NumCompetitiveGamesWon" + 1';
        }
        switch ( $GAME['EffectiveNumPlayers'] ) {
            case 4:
                $Incrementations[] = '"NumCompetitiveGamesFourPlayer" = "NumCompetitiveGamesFourPlayer" + 1';
                break;
            case 3:
                $Incrementations[] = '"NumCompetitiveGamesThreePlayer" = "NumCompetitiveGamesThreePlayer" + 1';
                break;
            default:
                $Incrementations[] = '"NumCompetitiveGamesTwoPlayer" = "NumCompetitiveGamesTwoPlayer" + 1';
        }
    }
    dbquery( DBQUERY_WRITE,
             'UPDATE "User" SET '.implode(', ',$Incrementations).' WHERE "UserID" = :user:',
             'user' , $GAME['PlayerUserID'][$ThePlayer]
             );
}

?>