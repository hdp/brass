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
if ( !isset($_GET['PhraseName']) ) {
    myerror( $unexpectederrormessage,
             'No phrase name specified in GET request'
             );
}

$PhraseName = sanitise_str($_GET['PhraseName'], STR_GPC | STR_ENSURE_ASCII);
$QR = dbquery( DBQUERY_READ_SINGLEROW,
               'SELECT "PhraseInEnglish", "Description", "Notes", "CurrentlyInUse", "FormInUse", "Module", "OrderingNumber" FROM "Phrase" WHERE "PhraseName" = :phrasename:',
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
                'SELECT COUNT(*) AS "NumTranslations", SUM("Chosen") AS "SumChosen" FROM "TranslatedPhrase" WHERE "PhraseName" = :phrasename:',
                'phrasename' , $PhraseName
                );
if ( $QRX === 'NONE' ) {
    myerror( $unexpectederrormessage,
             'COUNT/SUM query unexpectedly failed to return results'
             );
}
if ( is_null($QR['Description']) ) {
    $QR['Description'] = '';
}
if ( is_null($QR['Notes']) ) {
    $QR['Notes'] = '';
}
$InUseText = sanitise_bool($QR['CurrentlyInUse'], BOOL_CHECKED);
if ( $QRX['SumChosen'] ) { $HasChosen = true;  }
else                     { $HasChosen = false; }

$mypage->title_body('Edit phrase');
$mypage->leaf('h3', 'Edit phrase');
$mypage->opennode( 'form',
                   'action="translated.php" method="POST"'
                   );
$mypage->opennode( 'table',
                   'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                   );
$mypage->opennode('tr');
$mypage->opennode('td', 'align=right');
$mypage->text('Name:');
$mypage->text('<br>(ASCII, no quotes, max. 16 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<input type="text" name="name" size=28 maxlength=20 value="'.
                   $PhraseName.
                   '">'
               );
$mypage->next();
$mypage->leaf( 'td',
               'Ordering number:',
               'align=right'
               );
$mypage->leaf( 'td',
               '<input type="text" name="orderingnumber" size=14 maxlength=6 value='.
                   $QR['OrderingNumber'].
                   '>'
               );
$mypage->next();
$mypage->opennode('td', 'align=right');
$mypage->text('Phrase in English:');
$mypage->text('<br>(max. 500 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<textarea name="phrasetext" cols=45 rows=6>'.
                   $QR['PhraseInEnglish'].
                   '</textarea>'
               );
$mypage->next();
$mypage->opennode('td', 'align=right');
$mypage->text('Form of phrase in use:');
$mypage->text('<br>(max. 500 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<textarea name="forminuse" cols=45 rows=6>'.
                   $QR['FormInUse'].
                   '</textarea>'
               );
$mypage->next();
$mypage->opennode('td', 'align=right');
$mypage->text('Description of phrase:');
$mypage->text('<br>(max. 250 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<textarea name="description" cols=45 rows=4>'.
                   $QR['Description'].
                   '</textarea>'
               );
$mypage->next();
$mypage->opennode('td', 'align=right');
$mypage->text('Notes on the phrase:');
$mypage->text('<br>(max. 250 characters)');
$mypage->closenode(); // td
$mypage->leaf( 'td',
               '<textarea name="notes" cols=45 rows=4>'.
                   $QR['Notes'].
                   '</textarea>'
               );
$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="phraseinuse" value=1'.
                   $InUseText.
                   '>',
               'align=right'
               );
$mypage->leaf( 'td',
               'This phrase is currently in use on the site'
               );
$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="deletechosentrans" value=1>',
               'align=right'
               );
$mypage->leaf( 'td',
               'De-implement chosen translations of this phrase'
               );
if ( $HasChosen ) {
    $mypage->next();
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="unchoose" value=1>',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   'Unchoose translations'
                   );
}
$mypage->next();
$mypage->leaf( 'td',
               'Module:',
               'align=right'
               );

$mypage->opennode('td');
$mypage->opennode('select', 'name="whichmodule"');
$QRY = dbquery( DBQUERY_READ_RESULTSET,
                'SELECT "ModuleName", "ModuleID" FROM "TranslationModule"'
                );
if ( $QRY === 'NONE' ) {
    myerror( $unexpectederrormessage,
             'No modules found for select list'
             );
}
while ( $rowy = db_fetch_assoc($QRY) ) {
    $mypage->leaf( 'option',
                   $rowy['ModuleName'],
                   'value='.
                       $rowy['ModuleID'].
                       ( ( $rowy['ModuleID'] == $QR['Module'] ) ?
                         ' selected' :
                         ''
                         )
                   );
}
$mypage->closenode(2); // select, td

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Alter phrase">'
               );
$mypage->closenode(2); // tr, table
$mypage->emptyleaf( 'input',
                    'type="hidden" name="ThePhrase" value="'.
                        $PhraseName.
                        '"'
                    );
$mypage->closenode(); // form

switch ( $QRX['NumTranslations'] ) {
    case 0:
        break;
    case 1:
        $mypage->leaf( 'p',
                       'Please note: Somebody has already translated this phrase.'.
                           ( $HasChosen ?
                             ' Said translation has been chosen for use.' :
                             ''
                             ),
                       'style="color: #FF0000;"'
                       );
        break;
    default:
        $mypage->leaf( 'p',
                       'Please note: '.
                           $QRX['NumTranslations'].
                           ' people have already translated this phrase.'.
                           ( $HasChosen ?
                             ' One or more of these translations has been chosen for use.' :
                             ''
                             ),
                       'style="color: #FF0000;"'
                       );
}
$mypage->finish();

?>