<?php
require('_std-include.php');

if ( !$_SESSION['LoggedIn'] ) {
    $mypage = page::standard();
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then return to this page. You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}
if ( $Administrator < 2 ) {
    $mypage = page::standard();
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to make use of this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$Translator = sanitise_int(@$_GET['Translator']);
$TLanguage  = sanitise_int(@$_GET['Language']);
$PhraseName = sanitise_str(@$_GET['PhraseName'], STR_GPC | STR_ENSURE_ASCII);
if ( @$_GET['Mark'] ) {
    dbquery( DBQUERY_WRITE,
             'UPDATE "TranslatedPhrase" SET "Chosen" = 1 WHERE "PhraseName" = :phrasename: AND "Translator" = :translator: AND "Language" = :language:',
             'phrasename' , $PhraseName ,
             'translator' , $Translator ,
             'language'   , $TLanguage
             );
    $report = 'Successfully chose translation.';
} else {
    dbquery( DBQUERY_WRITE,
             'UPDATE "TranslatedPhrase" SET "Chosen" = 0 WHERE "PhraseName" = :phrasename: AND "Translator" = :translator: AND "Language" = :language:',
             'phrasename' , $PhraseName ,
             'translator' , $Translator ,
             'language'   , $TLanguage
             );
    $report = 'Successfully unchose translation.';
}
page::redirect(1, false, $report);

?>