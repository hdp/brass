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

$TLanguage  = sanitise_int(@$_GET['LanguageID']);
$PhraseName = sanitise_str(@$_GET['PhraseName'], STR_GPC | STR_ENSURE_ASCII);
$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'SELECT "PhraseInEnglish", "FormInUse" FROM "Phrase" WHERE "PhraseName" = :phrasename:',
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
                'SELECT "Translation" FROM "ChosenTranslatedPhrase" WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                'phrasename' , $PhraseName ,
                'language'   , $TLanguage
                );
if ( $QRX === 'NONE' ) {
    myerror( $unexpectederrormessage,
             'Translation with the specified phrase name "'.
                 htmlspecialchars($PhraseName).
                 '" and specified language was not found'
             );
}

$mypage->title_body('Edit chosen translation');
$mypage->leaf('h3', 'Edit chosen translation');
$mypage->leaf( 'div',
               $QR['PhraseInEnglish'],
               'style="position: relative; width: 585px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #FFC18A; font-family: monospace;"'
               );
$mypage->leaf( 'div',
               $QR['FormInUse'],
               'style="position: relative; width: 585px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #000000; color: #FFFFFF; font-family: monospace;"'
               );
$mypage->opennode( 'form',
                   'action="translated.php" method="POST"'
                   );
$mypage->leaf( 'textarea',
               $QRX['Translation'],
               'name="editedtext" cols=45 rows=6'
               );
$mypage->leaf( 'p',
               '<input type="submit" name="FormSubmit" value="Make changes">'
               );
$mypage->emptyleaf( 'input',
                    'type="hidden" name="PhraseName" value="'.$PhraseName.'"'
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" name="Language" value='.$TLanguage
                    );
$mypage->finish(); // form, body, html

?>