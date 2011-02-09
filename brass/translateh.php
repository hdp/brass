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
if ( $Administrator < 2 ) {
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to make use of this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$Translator = sanitise_int(@$_GET['Translator']);
$TLanguage  = sanitise_int(@$_GET['Language']);
$PhraseName = sanitise_str(@$_GET['PhraseName'], STR_GPC | STR_ENSURE_ASCII);
$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'SELECT "PhraseInEnglish", "FormInUse", "Description", "Notes" FROM "Phrase" WHERE "PhraseName" = :phrasename:',
               'phrasename' , $PhraseName
               );
if ( $QR === 'NONE' ) {
    myerror( $unexpectederrormessage,
             'Phrase with the specified phrase name "'.
                 htmlspecialchars($PhraseName).
                 '" was not found'
             );
}
$QRX = dbquery( DBQUERY_READ_SINGLEROW,
                'SELECT "Translation", "Comment" FROM "TranslatedPhrase" WHERE "PhraseName" = :phrasename: AND "Translator" = :translator: AND "Language" = :language:',
                'phrasename' , $PhraseName ,
                'translator' , $Translator ,
                'language'   , $TLanguage
                );
if ( $QRX === 'NONE' ) {
    myerror( $unexpectederrormessage,
             'Translation with the specified phrase name "'.
                 htmlspecialchars($PhraseName).
                 '" and specified language and translator was not found'
             );
}
$QRXX = dbquery( DBQUERY_READ_SINGLEROW,
                 'SELECT "Translation" FROM "ChosenTranslatedPhrase" WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                 'phrasename' , $PhraseName ,
                 'language'   , $TLanguage
                 );

$mypage->title_body('Choose translation');
$mypage->leaf('h3', 'Choose translation');
$mypage->leaf( 'div',
               $QR['PhraseInEnglish'],
               'style="position: relative; width: 645px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #FFC18A; font-family: monospace;"'
               );
if ( !is_null($QR['Description']) ) {
    $mypage->opennode( 'div',
                       'style="position: relative; width: 645px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #EA77EA;"'
                       );
    $mypage->leaf('b', 'Description:');
    $mypage->text($QR['Description']);
    $mypage->closenode(); // div
}
if ( !is_null($QR['Notes']) ) {
    $mypage->opennode( 'div',
                       'style="position: relative; width: 645px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #E7E727;"'
                       );
    $mypage->leaf('b', 'Notes:');
    $mypage->text($QR['Notes']);
    $mypage->closenode(); // div
}
$mypage->leaf( 'div',
               $QR['FormInUse'],
               'style="position: relative; width: 645px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #000000; color: #FFFFFF; font-family: monospace;"'
               );
$mypage->leaf('p', 'Selected translation:');
$mypage->leaf( 'div',
               $QRX['Translation'],
               'style="position: relative; width: 645px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #9FFF9F; font-family: monospace;"'
               );
if ( !is_null($QRX['Comment']) ) {
    $mypage->leaf('p', 'The following comment was attached:');
    $mypage->leaf( 'div',
                   $QRX['Comment'],
                   'style="position: relative; width: 645px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #9FFF9F;"'
                   );
}
if ( $QRXX !== 'NONE' ) {
    $mypage->leaf( 'p',
                   'A chosen translation of this phrase already exists. It is as follows:',
                   'style="font-style: italic;"'
                   );
    $mypage->leaf( 'div',
                   $QRXX['Translation'],
                   'style="position: relative; width: 645px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #000000; color: #FFFFFF; font-family: monospace;"'
                   );
}

$mypage->opennode( 'form',
                   'action="translated.php" method="POST"'
                   );
$mypage->opennode( 'table',
                   'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                   );
$mypage->opennode('tr');
$mypage->opennode('td', 'align=right');
$mypage->text('Corrected version:');
$mypage->text('<br>(max. 500 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<textarea name="editedtext" cols=60 rows=6>'.
                   $QRX['Translation'].
                   '</textarea>'
               );
$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="marktranslation" value=1 checked>',
               'align=right'
               );
$mypage->leaf( 'td',
               'Mark this translation as chosen'
               );
$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="unmarkothers" value=1>',
               'align=right'
               );
$mypage->leaf( 'td',
               'Unmark other translations'
               );
$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Choose">'
               );
$mypage->closenode(2); // tr, table
$mypage->emptyleaf( 'input',
                    'type="hidden" name="PhraseName" value="'.$PhraseName.'"'
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" name="Translator" value='.$Translator
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" name="Language" value='.$TLanguage
                    );
$mypage->finish(); // form, body, html

?>