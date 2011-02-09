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
if ( !isset($_POST['Translator']) or
     !isset($_POST['Language']) or
     !isset($_POST['PhraseName'])
     ) {
    myerror( $unexpectederrormessage,
             'One or more expected variables were missing from GET request'
             );
}

$Translator = sanitise_int($_POST['Translator']);
$TLanguage  = sanitise_int($_POST['Language']);
$PhraseName = sanitise_str($_POST['PhraseName'], STR_GPC | STR_ENSURE_ASCII);
if ( isset($_POST['ReturnModule']) ) {
    $ReturnModule = 'translateb.php?ModuleID='.
                    sanitise_int($_POST['ReturnModule']);
} else {
    $ReturnModule = 'index.php';
}
if ( !isset($_POST['IAmSure']) or !$_POST['IAmSure'] ) {
    $mypage = page::standard();
    $mypage->title_body('Tickbox not ticked');
    $mypage->leaf( 'p',
                   'You did not tick the tickbox. Please <a href="'.
                       $ReturnModule.
                       '">return to the list of phrases</a> and tick the box before clicking the "Delete" button.'
                   );
    $mypage->finish();
}
dbquery( DBQUERY_WRITE,
         'DELETE FROM "TranslatedPhrase" WHERE "Translator" = :translator: AND "Language" = :language: AND "PhraseName" = :phrasename:',
         'translator' , $Translator ,
         'language'   , $TLanguage  ,
         'phrasename' , $PhraseName
         );
page::redirect( 3,
                $ReturnModule,
                'Successfully deleted translation.'
                );

?>