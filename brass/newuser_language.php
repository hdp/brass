<?php
require('_std-include.php');

$mypage = page::standard();
if ( $_SESSION['LoggedIn'] ) {
    $mypage->title_body('Logged in');
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
    $mypage->finish();
}

$foreignlanguagetext = dbquery(DBQUERY_READ_SINGLEROW, 'SELECT "FormInUse" FROM "Phrase" WHERE "PhraseName" = \'NewUserLanguage\'');
if ( $foreignlanguagetext === 'NONE' ) {
    die($unexpectederrormessage);
}
$foreignlanguagetext = $foreignlanguagetext['FormInUse'];
$QR = dbquery( DBQUERY_READ_RESULTSET,
               'SELECT "TranslationLanguage"."LanguageID", "TranslationLanguage"."LanguageNameInEnglish", "ChosenTranslatedPhrase"."Translation" FROM "TranslationLanguage" LEFT JOIN "ChosenTranslatedPhrase" ON "TranslationLanguage"."LanguageID" = "ChosenTranslatedPhrase"."Language" AND "ChosenTranslatedPhrase"."PhraseName" = \'NewUserLanguage\''
               );
if ( $QR === 'NONE' ) {
    die($unexpectederrormessage);
}
while ( $row = db_fetch_assoc($QR) ) {
    $textsarray[$row['LanguageID']] = array( $row['Translation'],
                                             $row['LanguageNameInEnglish']
                                             );
}

function textinlanguage($languagenumber) {
    global $foreignlanguagetext, $textsarray;
    if ( is_null($textsarray[$languagenumber][0]) ) {
        return str_replace( array( '\languagenumber',
                                   '\uppercaselanguagename',
                                   '\languagename'
                                   ),
                            array( $languagenumber,
                                   strtoupper($textsarray[$languagenumber][1]),
                                   $textsarray[$languagenumber][1]
                                   ),
                            $foreignlanguagetext
                            );
    } else {
        return str_replace( '\languagenumber',
                            $languagenumber,
                            $textsarray[$languagenumber][0]
                            );
    }
}

$mypage->title_body('Select language');
$mypage->leaf('h1', 'Select language');
$mypage->leaf( 'p',
               'Please click <a href="newuser.php?Language=0">here</a> if your first language is English. If your first language is not English, please review the other options below to see whether a language more familiar to you is currently available. (Please note that some or all of the site, including the new user registration form, may not yet be translated into your chosen language - translation is done by volunteers and is slow work. Text that is not yet translated will display in English.)'
               );
for ($i=1; $i<=NUM_FOREIGN_LANGUAGES; $i++) {
    $mypage->leaf('p', textinlanguage($i));
}
$mypage->leaf( 'p',
               'Click <a href="index.php">here</a> to return to the Main Page.'
               );
$mypage->finish();

?>