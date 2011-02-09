<?php
require('_std-include.php');

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then return to this page. You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}
if ( !$Administrator and !$Translator ) {
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to make use of this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( isset($_POST['ChangeLanguage']) ) {
    $Language = sanitise_int( @$_POST['lang'],
                              INT_OUT_OF_RANGE_SET_LIMIT,
                              0,
                              NUM_FOREIGN_LANGUAGES
                              );
    dbquery( DBQUERY_WRITE,
             'UPDATE "User" SET "Language" = :language: WHERE "UserID" = :user:',
             'language' , $Language             ,
             'user'     , $_SESSION['MyUserID']
             );
    get_translation_module(1);
    get_translation_module(2);
}

$mypage->title_body('Translation: Select Module');
$mypage->loginbox();
$mypage->opennode( 'form',
                   'action="translatea.php" method="POST"'
                   );
$mypage->leaf('i', 'Quick language select:');
$mypage->opennode('select', 'name="lang"');
$mypage->leaf( 'option',
               'English',
               'value=0'.
                   ( ( $Language == 0 ) ?
                     ' selected' :
                     ''
                     )
               );
$QR = dbquery( DBQUERY_READ_RESULTSET,
               'SELECT "LanguageNameInEnglish" FROM "TranslationLanguage"'
               );
$i = 0;
while ( $row = db_fetch_assoc($QR) ) {
    $i++;
    $mypage->leaf( 'option',
                   $row['LanguageNameInEnglish'],
                   'value='.
                       $i.
                       ( ( $Language == $i ) ?
                         ' selected' :
                         ''
                         )
                   );
}
$mypage->closenode(); // select
$mypage->emptyleaf( 'input',
                    'type="submit" name="ChangeLanguage" value="Go"'
                    );
$mypage->closenode(); // form
$mypage->leaf( 'p',
               'Click <a href="boardview.php?BoardID=6">here</a> to go to the translation discussion board.'
               );
if ( !$Language and $Administrator < 2 ) {
    $mypage->leaf( 'p',
                   'Please select the language you will translate into from the "quick language select" box above, and click "Go". You must change the language from English to the translation language before you can proceed. NB: You can also change your preferred language through your User Details page.'
                   );
    $mypage->finish();
}

$mypage->leaf('h1', 'Translation: Select Module');
$mypage->opennode('table', 'class="table_extra_horizontal_padding"');
$mypage->opennode('thead');
$mypage->opennode( 'tr',
                   'style="font-weight: bold; text-align: center"'
                   );
$mypage->leaf('th', 'Module name'       );
$mypage->leaf('th', 'Module description');
$mypage->leaf('th', 'Number of phrases' );
if ( $Language ) {
    $mypage->leaf( 'th',
                   'Phrases not yet implemented'
                   );
    $mypage->leaf( 'th',
                   'Phrases not yet translated'
                   );
    $mypage->leaf( 'th',
                   'Phrases translated but not yet implemented'
                   );
    if ( $Administrator == 2 ) {
        $mypage->leaf('th', '');
    }
}
$mypage->closenode(2); // tr, thead
$mypage->opennode('tbody');

if ( $Language ) {
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   'CALL "GetTranslationStatistics"(:language:)',
                   'language' , $Language
                   );
    if ( $QR === 'NONE' ) { die($unexpectederrormessage); }
    while ( $row = db_fetch_assoc($QR) ) {
        $QRX = dbquery( DBQUERY_READ_INTEGER,
                        'SELECT COUNT(*) AS "Co" FROM "Phrase" WHERE "Module" = :module:',
                        'module' , $row['ModuleID']
                        );
        $mypage->opennode('tr');
        if ( $QRX > 20 ) {
            $mypage->opennode('td');
            $mypage->leaf( 'a',
                           $row['ModuleName'],
                           'href="translateb.php?ModuleID='.
                               $row['ModuleID'].
                               '"'
                           );
            $mypage->text('<br>Pages:');
            $mypage->opennode('b');
            for ($i=1; 20*($i-1)<$QRX; $i++) {
                $mypage->leaf( 'a',
                               $i,
                               'href="translateb.php?ModuleID='.
                                   $row['ModuleID'].
                                   '&Page='.
                                   $i.
                                   '"'
                               );
            }
            $mypage->closenode(2); // b, td
        } else {
            $mypage->leaf( 'td',
                           '<a href="translateb.php?ModuleID='.
                               $row['ModuleID'].
                               '">'.
                               $row['ModuleName'].
                               '</a>'
                           );
        }
        $mypage->leaf( 'td',
                       $row['ModuleDescription'],
                       'class="font_serif" align=left'
                       );
        $mypage->leaf('td', $row['NumPhrases']               );
        $mypage->leaf('td', $row['NumUnchosenPhrases']       );
        $mypage->leaf('td', $row['NumUntranslatedPhrases']   );
        $mypage->leaf('td', $row['NumTranslatedButNotChosen']);
        if ( $Administrator == 2 ) {
            if ( $QRX > 20 ) {
                $mypage->opennode('td');
                $mypage->leaf( 'a',
                               'Simulate Translator Mode',
                               'href="translateb.php?ModuleID='.
                                   $row['ModuleID'].
                                   '&SimulateTranslatorMode=1"'
                               );
                $mypage->text('<br>Pages:');
                $mypage->opennode('b');
                for ($i=1; 20*($i-1)<$QRX; $i++) {
                    $mypage->leaf( 'a',
                                   $i,
                                   'href="translateb.php?ModuleID='.
                                       $row['ModuleID'].
                                       '&Page='.
                                       $i.
                                       '&SimulateTranslatorMode=1"'
                                   );
                }
                $mypage->closenode(2); // b, td
            } else {
                $mypage->leaf( 'td',
                               '<a href="translateb.php?ModuleID='.
                                   $row['ModuleID'].
                                   '&SimulateTranslatorMode=1">Simulate Translator Mode</a>'
                               );
            }
        }
        $mypage->closenode(); // tr
    }
} else {
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   'SELECT "TranslationModule"."ModuleID", "TranslationModule"."ModuleName", "TranslationModule"."ModuleDescription", CASE WHEN "SubqueryA"."NumPhrases" IS NULL THEN 0 ELSE "SubqueryA"."NumPhrases" END AS "NumPhrases" FROM "TranslationModule" LEFT JOIN (SELECT "Module", COUNT(*) AS "NumPhrases" FROM "Phrase" WHERE "CurrentlyInUse" = 1 GROUP BY "Module") AS "SubqueryA" ON "TranslationModule"."ModuleID" = "SubqueryA"."Module"'
                   );
    if ( $QR === 'NONE' ) { die($unexpectederrormessage); }
    while ( $row = db_fetch_assoc($QR) ) {
        $QRX = dbquery( DBQUERY_READ_INTEGER,
                        'SELECT COUNT(*) AS "Co" FROM "Phrase" WHERE "Module" = :module:',
                        'module' , $row['ModuleID']
                        );
        $mypage->opennode('tr');
        if ( $QRX > 20 ) {
            $mypage->opennode('td');
            $mypage->leaf( 'a',
                           $row['ModuleName'],
                           'href="translateb.php?ModuleID='.
                               $row['ModuleID'].
                               '"'
                           );
            $mypage->text('<br>Pages:');
            $mypage->opennode('b');
            for ($i=1; 20*($i-1)<$QRX; $i++) {
                $mypage->leaf( 'a',
                               $i,
                               'href="translateb.php?ModuleID='.
                                   $row['ModuleID'].
                                   '&Page='.
                                   $i.
                                   '"'
                               );
            }
            $mypage->closenode(2); // b, td
        } else {
            $mypage->leaf( 'td',
                           '<a href="translateb.php?ModuleID='.
                               $row['ModuleID'].
                               '">'.
                               $row['ModuleName'].
                               '</a>'
                           );
        }
        $mypage->leaf( 'td',
                       $row['ModuleDescription'],
                       'class="font_serif" align=left'
                       );
        $mypage->leaf('td', $row['NumPhrases']);
        $mypage->closenode(); // tr
    }
}
$mypage->closenode(2); // tbody, table
$mypage->leaf( 'p',
               'Click <a href="boardview.php?BoardID=6">here</a> to go to the translation discussion board, or <a href="index.php">here</a> to return to the Main Page.'
               );
$mypage->finish();

?>