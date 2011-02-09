<?php
require('_std-include.php');

$mypage = page::standard();
if ( $Administrator ) {
    require(HIDDEN_FILES_PATH.'gamelistdisplay.php');
    $mypage->title_body('All Cancelled Games');
    $mypage->loginbox(false);
    if ( @$_GET['clearcache'] ) {
        $translation_directory_resource = @opendir(TRANSLATION_CACHE_PREFIX_NS);
        if ( $translation_directory_resource === false ) {
            $mypage->leaf( 'p',
                           '<i><b>Encountered an error while attempting to access the translation cache directory.</b></i>'
                           );
        } else {
            $finished = false;
            $numdeleted = 0;
            while ( !$finished ) {
                $currentfile = readdir($translation_directory_resource);
                if ( $currentfile === false ) {
                    $finished = true;
                } else if ( !is_dir(TRANSLATION_CACHE_PREFIX.$currentfile) and
                            substr($currentfile, 0, 3) == 'tc-' and
                            substr($currentfile, -4) == '.txt'
                            ) {
                    unlink(TRANSLATION_CACHE_PREFIX.$currentfile);
                    $numdeleted++;
                }
            }
            closedir($translation_directory_resource);
            $mypage->leaf( 'p',
                           '<i><b>Successfully cleared the translation cache. Deleted '.
                               $numdeleted.
                               ' files.</b></i>'
                           );
        }
    }
    if ( @$_GET['sync'] ) {
        dbquery(DBQUERY_WRITE, 'CALL "SynchroniseTranslations"()');
        $mypage->leaf( 'p',
                       '<i><b>Successfully synchronised translation data.</b></i>'
                       );
    }
    if ( @$_GET['delete'] ) {
        $QueryResult = dbquery( DBQUERY_WRITE,
                                'DELETE FROM "GeneralThread" USING "GeneralThread" JOIN "Game" ON "GeneralThread"."ThreadID" = "Game"."GameID" WHERE "Game"."GameStatus" = \'Cancelled\' AND "Game"."LastMove" < TIMESTAMPADD(DAY, -3, UTC_TIMESTAMP())'
                                );
        $mypage->leaf( 'p',
                       '<i><b>Cancelled games more than three days old were successfully deleted.</b></i>'
                       );
    }
    if ( $Administrator == 2 and @$_POST['FormSubmit'] ) {
        $didsomething = false;
        $GamesToDelete = array();
        $PGRsToHide_Games = array();
        $PGRsToHide_Users = array();
        for ($i=0;$i<10;$i++) {
            if ( @$_POST['DeletionGame'.$i] ) {
                if ( @$_POST['DeletionUser'.$i] ) {
                    $PGRsToHide_Games[] = (int)$_POST['DeletionGame'.$i];
                    $PGRsToHide_Users[] = (int)$_POST['DeletionUser'.$i];
                } else {
                    $ThisGameID = (int)$_POST['DeletionGame'.$i];
                    $GamesToDelete[] = $ThisGameID;
                    $ThisNMMFile = NUM_MOVES_MADE_DIR.'g'.$ThisGameID.'.txt';
                    if ( file_exists($ThisNMMFile) ) {
                        unlink($ThisNMMFile);
                    }
                }
            }
        }
        if ( count($GamesToDelete) ) {
            $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                                    'DELETE FROM "GeneralThread" USING "GeneralThread" JOIN "Game" ON "GeneralThread"."ThreadID" = "Game"."GameID" WHERE "Game"."GameID" IN :gtd:',
                                    'gtd' , $GamesToDelete
                                    );
            $didsomething = true;
        }
        for ($i=0; $i<count($PGRsToHide_Games); $i++) {
            $QR = dbquery( DBQUERY_WRITE,
                           'UPDATE "PlayerGameRcd" SET "GameResult" = \'Hide\' WHERE "Game" = :game: AND "User" = :user:',
                           'game' , $PGRsToHide_Games[$i] ,
                           'user' , $PGRsToHide_Users[$i]
                           );
            $QR = dbquery( DBQUERY_WRITE,
                           'DELETE FROM "PGRScore" WHERE "Game" = :game: AND "User" = :user:',
                           'game' , $PGRsToHide_Games[$i] ,
                           'user' , $PGRsToHide_Users[$i]
                           );
            $didsomething = true;
        }
        if ( $didsomething ) {
            $mypage->leaf( 'p',
                           '<i><b>Specified deletes and/or hides were successfully carried out.</b></i>'
                           );
        } else {
            $mypage->leaf( 'p',
                           '<i><b>You don\'t seem to have entered any data.</b></i>'
                           );
        }
    }
    $mypage->leaf( 'p',
                   'Here is a list of all games that have been cancelled during recruitment.'
                   );
    gamelistdisplayr($mypage, true);
    $mypage->leaf( 'p',
                   'You can click <a href="cancelledgames.php?delete=1">here</a> to delete all of these games that were cancelled more than three days ago.'
                   );
    $mypage->leaf( 'p',
                   'Click <a href="recalculate.php?DoAll=1">here</a> to recalculate all player ratings, player statistics, rankings and site statistics.'
                   );
    $mypage->leaf( 'p',
                   'Click <a href="cancelledgames.php?clearcache=1">here</a> to clear the translation cache.'
                   );
    $mypage->leaf( 'p',
                   'Click <a href="cancelledgames.php?sync=1">here</a> to synchronise translation data.'
                   );
    if ( $Administrator == 2 ) {
        $mypage->leaf( 'h3',
                       'Delete arbitrary games / hide player game records'
                       );
        $mypage->leaf( 'p',
                       'Enter the numbers of the games you want to delete in the left-hand column. If you only want to hide a single player game record, then enter the user ID in the right-hand column; otherwise, leave the right-hand column blank.'
                       );
        $mypage->opennode('form', 'action="cancelledgames.php" method="POST"');
        $mypage->opennode('table', 'class="table_no_borders"');
        for ($i=0; $i<10; $i++) {
            $mypage->opennode('tr');
            $mypage->leaf( 'td',
                           '<input type="text" name="DeletionGame'.$i.
                               '" size="10" maxlength="15">'
                           );
            $mypage->leaf( 'td',
                           '<input type="text" name="DeletionUser'.$i.
                               '" size="10" maxlength="15">'
                           );
            $mypage->closenode();
        }
        $mypage->closenode();
        $mypage->leaf( 'p',
                       '<input type="submit" name="FormSubmit" value="Execute">'
                       );
        $mypage->closenode();
    }
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
} else {
    $mypage->title_body('Not Permitted');
    $mypage->leaf( 'p',
                   'You are not permitted to view this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
}
$mypage->finish();

?>