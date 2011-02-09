<?php
require('_std-include.php');

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then return to the translation pages. You can return to the Main Page by clicking <a href="index.php">here</a>.'
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
if ( !$Language ) {
    $mypage->title_body('Language set to English');
    $mypage->leaf( 'p',
                   'Your language is set to English. Please visit <a href="translatea.php">this page</a> and set it to a different language, then try again.'
                   );
    $mypage->finish();
}
if ( !isset($_GET['PhraseName']) ) {
    myerror( $unexpectederrormessage,
             'No phrase name specified in GET request'
             );
}

$PhraseName = sanitise_str($_GET['PhraseName'], STR_GPC | STR_ENSURE_ASCII);
$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'SELECT "PhraseInEnglish", "Description", "Notes", "CurrentlyInUse" FROM "Phrase" WHERE "PhraseName" = :phrasename:',
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
                'SELECT "Translation", "Comment" FROM "TranslatedPhrase" WHERE "Language" = :language: AND "Translator" = :translator: AND "PhraseName" = :phrasename:',
                'language'   , $Language             ,
                'translator' , $_SESSION['MyUserID'] ,
                'phrasename' , $PhraseName
                );

$notifications = fragment::blank();
if ( !$QR['CurrentlyInUse'] ) {
    $notifications->leaf( 'p',
                          'This phrase is not now in use on the website - it was previously in use, but currently is not. It is not necessary to translate this phrase at present.',
                          'style="color: #FF0000;"'
                          );
}
if ( $QRX === 'NONE' ) {
    $mypage->title_body('Translate phrase');
    $mypage->leaf('h3', 'Translate phrase');
    $QRX = array( 'Translation' => '',
                  'Comment'     => ''
                  );
} else {
    $mypage->title_body('Edit translation');
    $mypage->leaf('h3', 'Edit translation');
    $notifications->leaf( 'p',
                          '(You can delete your translation by blanking the "Translation" field and clicking "Submit". This will delete the comment too.)'
                          );
    if ( is_null($QRX['Comment']) ) {
        $QRX['Comment'] = '';
    }
}

$mypage->leaf( 'div',
               $QR['PhraseInEnglish'],
               'style="position: relative; width: 585px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #FFC18A; font-family: monospace;"'
               );
if ( !is_null($QR['Description']) ) {
    $mypage->opennode( 'div',
                       'style="position: relative; width: 585px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #EA77EA;"'
                       );
    $mypage->leaf('b', 'Description:');
    $mypage->text($QR['Description']);
    $mypage->closenode(); // div
}
if ( !is_null($QR['Notes']) ) {
    $mypage->opennode( 'div',
                       'style="position: relative; width: 585px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #E7E727;"'
                       );
    $mypage->leaf('b', 'Notes:');
    $mypage->text($QR['Notes']);
    $mypage->closenode(); // div
}
$mypage->opennode( 'form',
                   'action="translated.php" method="POST"'
                   );
$mypage->opennode( 'table',
                   'class="table_no_borders"'
                   );
$mypage->opennode('tr');
$mypage->opennode('td', 'align=right');
$mypage->text('Translation:');
$mypage->text('<br>(max. 750 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<textarea name="transtext" cols=45 rows=6>'.
                   $QRX['Translation'].
                   '</textarea>'
               );
$mypage->next();
$mypage->opennode('td', 'align=right');
$mypage->text('Comment:');
$mypage->text('<br>(not required)');
$mypage->text('<br>(max. 250 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<textarea name="transcomment" cols=45 rows=4>'.
                   $QRX['Comment'].
                   '</textarea>'
               );
$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Submit">'
               );
$mypage->closenode(2); // tr, table
$mypage->emptyleaf( 'input',
                    'type="hidden" name="ThePhrase" value="'.
                        $PhraseName.
                        '"'
                    );
$mypage->closenode(); // form
$mypage->append($notifications);
$mypage->finish();

?>