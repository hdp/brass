<?php
require('_std-include.php');

$mypage = page::standard();
if ( $Administrator or $Translator ) {
    if ( !isset($_GET['ModuleID']) ) {
        $mypage->title_body('Error');
        $mypage->leaf( 'p',
                       'It looks as though you have been sent to the wrong page. Please click <a href="translatea.php">here</a> to select a module to translate, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $ModuleID = sanitise_int($_GET['ModuleID']);
    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "ModuleName", "ModuleDescription" FROM "TranslationModule" WHERE "ModuleID" = :module:',
                   'module' , $ModuleID
                   );
    if ( $QR === 'NONE' ) { die($unexpectederrormessage); }
    $SimulateTranslatorMode = ( $Administrator == 2 and @$_GET['SimulateTranslatorMode'] );
    if ( isset($_GET['Page']) ) {
        $Page = (int)$_GET['Page'];
        if ( $Page < 1 ) { $Page = 1; }
    } else {
        $Page = 1;
    }
    $StartPoint = 20 * ($Page-1);
    $mypage->script('popup.js');
    $mypage->title_body('Translation: '.$QR['ModuleName']);
    $mypage->loginbox();
    $mypage->leaf( 'p',
                   'Click <a href="translatea.php">here</a> to select another page to translate, or <a href="boardview.php?BoardID=6">here</a> to go to the translation discussion board.'
                   );
    $mypage->leaf('h1', 'Translation: '.$QR['ModuleName']);
    $mypage->leaf('p', $QR['ModuleDescription']);
    if ( !$Language and $Administrator < 2 ) {
        $mypage->leaf( 'p',
                       'Your language is set to English. Please click <a href="translatea.php">here</a> to select a language to translate into, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   'SELECT * FROM "Phrase" WHERE "Module" = :module: ORDER BY "CurrentlyInUse" DESC, "OrderingNumber" LIMIT :startpoint:, 20',
                   'module'     , $ModuleID   ,
                   'startpoint' , $StartPoint
                   );
    $QRX = dbquery( DBQUERY_READ_INTEGER,
                    'SELECT COUNT(*) AS "Co" FROM "Phrase" WHERE "Module" = :module:',
                    'module' , $ModuleID
                    );
    if ( !$QRX ) {
        $mypage->leaf( 'p',
                       'There are no phrases to translate for this page yet.'
                       );
    } else {
        require_once(HIDDEN_FILES_PATH.'paginate.php');
        $PBArgument4Array['ModuleID'] = $ModuleID;
        if ( $SimulateTranslatorMode ) {
            $PBArgument4Array['SimulateTranslatorMode'] = 1;
        }
        $PaginationBar = paginationbar( 'phrase',
                                        'phrases',
                                        SITE_ADDRESS.'translateb.php',
                                        $PBArgument4Array,
                                        20,
                                        $Page,
                                        $QRX
                                        );
        $mypage->append($PaginationBar[0]);
        $PhrasesStillInUse = true;
        while ( $row = db_fetch_assoc($QR) ) {
            if ( $PhrasesStillInUse and !$row['CurrentlyInUse'] ) {
                $PhrasesStillInUse = false;
                $mypage->leaf( 'pre',
                               "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n++++++++++++++++++++++++++++++   PHRASES FROM HERE ON DOWN ARE NOT IN USE   ++++++++++++++++++++++++++++++\n++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++"
                               );
            }
            if ( $Administrator < 2 or
                 ( $SimulateTranslatorMode and $Language )
                 ) {
                $mypage->opennode( 'div',
                                   'style="position: relative; width: 960px; border: 1pt solid black; padding: 7px; margin-bottom: 10px; background-color: #E7E7E7;"'
                                   );
                $mypage->leaf( 'div',
                               $row['OrderingNumber'],
                               'style="float: right; border: none; margin: 5px 0px; text-align: right; font-size: 75%;"'
                               );
                $mypage->leaf( 'p',
                               $row['PhraseName'],
                               'style="font-family: monospace;"'
                               );
                $mypage->leaf( 'div',
                               $row['PhraseInEnglish'],
                               'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #FFC18A; font-family: monospace;"'
                               );
                if ( !is_null($row['Description']) ) {
                    $mypage->opennode( 'div',
                                       'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #EA77EA;"'
                                       );
                    $mypage->leaf('b', 'Description:');
                    $mypage->text($row['Description']);
                    $mypage->closenode();
                }
                if ( !is_null($row['Notes']) ) {
                    $mypage->opennode( 'div',
                                       'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #E7E727;"'
                                       );
                    $mypage->leaf('b', 'Notes:');
                    $mypage->text($row['Notes']);
                    $mypage->closenode();
                }
                if ( !$row['CurrentlyInUse'] ) {
                    $mypage->leaf( 'p',
                                   'This phrase is not now in use on the website - it was previously in use, but currently is not. It is not necessary to translate this phrase at present.',
                                   'style="color: #FF0000; font-weight: bold;"'
                                   );
                }
                $QRX = dbquery( DBQUERY_READ_RESULTSET,
                                'SELECT "TranslatedPhrase"."Translation", "TranslatedPhrase"."Comment", "TranslatedPhrase"."Translator", "TranslatedPhrase"."Chosen", "User"."Name", "User"."Pronoun" FROM "TranslatedPhrase" LEFT JOIN "User" ON "TranslatedPhrase"."Translator" = "User"."UserID" WHERE "TranslatedPhrase"."PhraseName" = :phrasename: AND "TranslatedPhrase"."Language" = :language:',
                                'phrasename' , $row['PhraseName'] ,
                                'language'   , $Language
                                );
                if ( $QRX === 'NONE' ) {
                    $mypage->leaf( 'p',
                                   'Nobody has translated this phrase yet. <a href="translatec.php?PhraseName='.
                                       urlencode($row['PhraseName']).
                                       '" onClick="return popup(this,\'translatepopup'.
                                       $row['OrderingNumber'].
                                       '\',700,500,\'yes\')">Translate this phrase</a>'
                                   );
                } else {
                    $Translations      = array();
                    $Comments          = array();
                    $Translators       = array();
                    $Names             = array();
                    $Pronouns          = array();
                    $Chosen            = array();
                    $IsMine            = array();
                    $IHaveTranslatedIt = 0;
                    while ( $rowx = db_fetch_assoc($QRX) ) {
                        $Translations[] = $rowx['Translation'];
                        $Comments[]     = $rowx['Comment'];
                        $Translators[]  = $rowx['Translator'];
                        $Names[]        = $rowx['Name'];
                        $Pronouns[]     = $rowx['Pronoun'];
                        $Chosen[]       = $rowx['Chosen'];
                        if ( $rowx['Translator'] == $_SESSION['MyUserID'] ) {
                            $IsMine[] = 1;
                            $IHaveTranslatedIt = 1;
                        } else {
                            $IsMine[] = 0;
                        }
                    }
                    array_multisort( $IsMine       , SORT_DESC ,
                                     $Chosen       , SORT_DESC ,
                                     $Translators  ,
                                     $Names        ,
                                     $Pronouns     ,
                                     $Translations ,
                                     $Comments
                                     );
                    for ($i=0; $i<count($IsMine); $i++) {
                        if ( $IsMine[$i] ) {
                            $mypage->opennode( 'div',
                                               'style="position: relative; width: 945px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #87FCFC;"'
                                               );
                            if ( $Chosen[$i] ) {
                                $mypage->opennode('p');
                                $mypage->emptyleaf( 'img',
                                                    'src="gfx/astroid.png" alt="This translation has been put into use." style="vertical-align: middle; border-style: none"'
                                                    );
                                $mypage->text('Translation by you:');
                                $mypage->closenode();
                            } else {
                                $mypage->leaf('p', 'Translation by you:');
                            }
                            $mypage->leaf( 'p',
                                           $Translations[$i],
                                           'style="font-family: monospace;"'
                                           );
                            if ( !is_null($Comments[$i]) ) {
                                $mypage->leaf( 'p',
                                               'You added the following comment: '.
                                                   $Comments[$i]
                                               );
                            }
                            $mypage->leaf( 'p',
                                           '<a href="translatec.php?PhraseName='.
                                               urlencode($row['PhraseName']).
                                               '" onClick="return popup(this,\'translatepopup'.
                                               $row['OrderingNumber'].
                                               '\',700,500,\'yes\')">Edit your translation</a>'
                                           );
                        } else {
                            $mypage->opennode( 'div',
                                               'style="position: relative; width: 945px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #9FFF9F;"'
                                               );
                            if ( $Chosen[$i] ) {
                                $mypage->opennode('p');
                                $mypage->emptyleaf( 'img',
                                                    'src="gfx/astroid.png" alt="This translation has been put into use." style="vertical-align: middle; border-style: none"'
                                                    );
                                $mypage->text( 'Translation by <a href="userdetails.php?UserID='.
                                               $Translators[$i].
                                               '">'.
                                               $Names[$i].
                                               '</a>:'
                                               );
                                $mypage->closenode();
                            } else {
                                $mypage->leaf( 'p',
                                               'Translation by <a href="userdetails.php?UserID='.
                                                   $Translators[$i].
                                                   '">'.
                                                   $Names[$i].
                                                   '</a>:'
                                               );
                            }
                            $mypage->leaf( 'p',
                                           $Translations[$i],
                                           'style="font-family: monospace;"'
                                           );
                            if ( !is_null($Comments[$i]) ) {
                                $mypage->leaf( 'p',
                                               $Pronouns[$i].
                                                   ' added the following comment: '.
                                                   $Comments[$i]
                                               );
                            }
                        }
                        $mypage->closenode(); // div
                    }
                    if ( !$IHaveTranslatedIt ) {
                        $mypage->leaf( 'p',
                                       'Although others have translated this phrase, you have not. <a href="translatec.php?PhraseName='.
                                           urlencode($row['PhraseName']).
                                           '" onClick="return popup(this,\'translatepopup'.
                                           $row['OrderingNumber'].
                                           '\',700,500,\'yes\')">Translate this phrase</a>'
                                       );
                    }
                }
            } else {
                $mypage->opennode( 'div',
                                   'style="position: relative; width: 960px; border: 1pt solid black; padding: 7px; margin-bottom: 10px; background-color: #E7E7E7;"'
                                   );
                $mypage->leaf( 'div',
                               $row['OrderingNumber'],
                               'style="float: right; border: none; margin: 5px 0px; text-align: right; font-size: 75%;"'
                               );
                $mypage->leaf( 'p',
                               $row['PhraseName'],
                               'style="font-family: monospace;"'
                               );
                $mypage->leaf( 'div',
                               $row['PhraseInEnglish'],
                               'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #FFC18A; font-family: monospace;"'
                               );
                if ( !is_null($row['Description']) ) {
                    $mypage->opennode( 'div',
                                       'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #EA77EA;"'
                                       );
                    $mypage->leaf('b', 'Description:');
                    $mypage->text($row['Description']);
                    $mypage->closenode();
                }
                if ( !is_null($row['Notes']) ) {
                    $mypage->opennode( 'div',
                                       'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #E7E727;"'
                                       );
                    $mypage->leaf('b', 'Notes:');
                    $mypage->text($row['Notes']);
                    $mypage->closenode();
                }
                $mypage->opennode( 'div',
                                   'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #000000; color: #FFFFFF;"'
                                   );
                $mypage->leaf('b', 'English:');
                $mypage->leaf( 'p',
                               $row['FormInUse'],
                               'style="font-family: monospace;"'
                               );
                $mypage->closenode();
                $mypage->leaf( 'p',
                               '<a href="translatee.php?PhraseName='.
                                   urlencode($row['PhraseName']).
                                   '" onClick="return popup(this,\'translatepopup'.
                                   $row['OrderingNumber'].
                                   '\',700,500,\'yes\')">Edit this phrase</a>'
                               );
                $QRX = dbquery( DBQUERY_READ_SINGLEROW,
                                'SELECT COUNT(DISTINCT "Language") AS "NumLangs", SUM("Chosen") AS "SumChosen" FROM "TranslatedPhrase" WHERE "PhraseName" = :phrasename:',
                                'phrasename' , $row['PhraseName']
                                );
                if ( $QRX === 'NONE' ) {
                    die($unexpectederrormessage);
                }
                $notice = 'This phrase has ';
                switch ( $QRX['NumLangs'] ) {
                    case 0:
                        $notice .= 'not been translated into any language.';
                        break;
                    case 1:
                        $notice .= 'been translated into one language.';
                        break;
                    default:
                        $notice .= 'been translated into '.
                                   $QRX['NumLangs'].
                                   ' languages.';
                }
                $SumChosen = (int)$QRX['SumChosen'];
                if ( $SumChosen ) {
                    $notice .= ' At least one translation has been chosen for use.';
                }
                $mypage->leaf('p', $notice);
                if ( $Language ) {
                    $QRX = dbquery( DBQUERY_READ_RESULTSET,
                                    'SELECT "TranslatedPhrase"."Translation", "TranslatedPhrase"."Comment", "TranslatedPhrase"."Translator", "TranslatedPhrase"."Chosen", "User"."Name", "User"."Pronoun" FROM "TranslatedPhrase" LEFT JOIN "User" ON "TranslatedPhrase"."Translator" = "User"."UserID" WHERE "TranslatedPhrase"."PhraseName" = :phrasename: AND "TranslatedPhrase"."Language" = :language:',
                                    'phrasename' , $row['PhraseName'] ,
                                    'language'   , $Language
                                    );
                    if ( $QRX !== 'NONE' ) {
                        $Translations = array();
                        $Comments     = array();
                        $Translators  = array();
                        $Names        = array();
                        $Pronouns     = array();
                        $Chosen       = array();
                        $OneIsChosen  = 0;
                        while ( $rowx = db_fetch_assoc($QRX) ) {
                            $Translations[] = $rowx['Translation'];
                            $Comments[]     = $rowx['Comment'];
                            $Translators[]  = $rowx['Translator'];
                            $Names[]        = $rowx['Name'];
                            $Pronouns[]     = $rowx['Pronoun'];
                            $Chosen[]       = $rowx['Chosen'];
                            if ( $rowx['Chosen'] ) { $OneIsChosen = 1; }
                        }
                        array_multisort( $Chosen       , SORT_DESC ,
                                         $Translators  ,
                                         $Names        ,
                                         $Pronouns     ,
                                         $Translations ,
                                         $Comments
                                         );
                        for ($i=0; $i<count($Chosen); $i++) {
                            $mypage->opennode( 'div',
                                               'style="position: relative; width: 945px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #9FFF9F;"'
                                               );
                            if ( $Chosen[$i] ) {
                                $mypage->opennode('p');
                                $mypage->emptyleaf( 'img',
                                                    'src="gfx/astroid.png" alt="This translation has been put into use." style="vertical-align: middle; border-style: none;"'
                                                    );
                                $mypage->text( 'Translation by <a href="userdetails.php?UserID='.
                                               $Translators[$i].
                                               '">'.
                                               $Names[$i].
                                               '</a>:'
                                               );
                                $mypage->closenode();
                            } else {
                                $mypage->leaf( 'p',
                                               'Translation by <a href="userdetails.php?UserID='.
                                                   $Translators[$i].
                                                   '">'.
                                                   $Names[$i].
                                                   '</a>:'
                                               );
                            }
                            $mypage->leaf( 'p',
                                           $Translations[$i],
                                           'style="font-family: monospace;"'
                                           );
                            if ( !is_null($Comments[$i]) ) {
                                $mypage->leaf( 'p',
                                               $Pronouns[$i].
                                                   ' added the following comment: '.
                                                   $Comments[$i]
                                               );
                            }
                            $mypage->opennode('p');
                            $mypage->leaf( 'a',
                                           'Choose',
                                           'href="translateh.php?PhraseName='.
                                               urlencode($row['PhraseName']).
                                               '&Translator='.
                                               $Translators[$i].
                                               '&Language='.
                                               $Language.
                                               '" onClick="return popup(this,\'translatepopup'.
                                               $row['OrderingNumber'].
                                               '\',700,500,\'yes\')"'
                                           );
                            $mypage->text('---');
                            if ( $Chosen[$i] ) {
                                $mypage->leaf( 'a',
                                               'Unmark',
                                               'href="translateg.php?PhraseName='.
                                                   urlencode($row['PhraseName']).
                                                   '&Translator='.
                                                   $Translators[$i].
                                                   '&Language='.
                                                   $Language.
                                                   '&Mark=0" onClick="return popup(this,\'disposablewindow\',500,250,\'yes\')"'
                                               );
                            } else {
                                $mypage->leaf( 'a',
                                               'Mark as chosen',
                                               'href="translateg.php?PhraseName='.
                                                   urlencode($row['PhraseName']).
                                                   '&Translator='.
                                                   $Translators[$i].
                                                   '&Language='.
                                                   $Language.
                                                   '&Mark=1" onClick="return popup(this,\'disposablewindow\',500,250,\'yes\')"'
                                               );
                            }
                            $mypage->closenode(); // p
                            $mypage->opennode( 'form',
                                               'action="translatef.php" method="POST"'
                                               );
                            $mypage->opennode('p');
                            $mypage->emptyleaf( 'input',
                                                'type="submit" value="Delete"'
                                                );
                            $mypage->emptyleaf( 'input',
                                                'type="checkbox" name="IAmSure" value="1"'
                                                );
                            $mypage->text('Yes, I mean to delete this translation');
                            $mypage->closenode(); // p
                            $mypage->emptyleaf( 'input',
                                                'type="hidden" name="Translator" value="'.
                                                    $Translators[$i].
                                                    '"'
                                                );
                            $mypage->emptyleaf( 'input',
                                                'type="hidden" name="Language" value="'.
                                                    $Language.
                                                    '"'
                                                );
                            $mypage->emptyleaf( 'input',
                                                'type="hidden" name="PhraseName" value="'.
                                                    $row['PhraseName'].
                                                    '"'
                                                );
                            $mypage->emptyleaf( 'input',
                                                'type="hidden" name="ReturnPage" value="'.
                                                    $ModuleID.
                                                    '"'
                                                );
                            $mypage->closenode(2); // form, div
                        }
                    }
                } else {
                    $QRX = dbquery( DBQUERY_READ_RESULTSET,
                                    'SELECT "ChosenTranslatedPhrase"."Translation", "ChosenTranslatedPhrase"."Language", "TranslationLanguage"."LanguageNameInEnglish" FROM "ChosenTranslatedPhrase" LEFT JOIN "TranslationLanguage" ON "ChosenTranslatedPhrase"."Language" = "TranslationLanguage"."LanguageID" WHERE "ChosenTranslatedPhrase"."PhraseName" = :phrasename:',
                                    'phrasename' , $row['PhraseName']
                                    );
                    while ( $rowx = db_fetch_assoc($QRX) ) {
                        $mypage->opennode( 'div',
                                           'style="position: relative; width: 944px; border: 1pt solid black; padding: 7px; margin-bottom: 7px; background-color: #000000; color: #FFFFFF;"'
                                           );
                        $mypage->leaf( 'b',
                                       $rowx['LanguageNameInEnglish']
                                       );
                        $mypage->leaf( 'p',
                                       $rowx['Translation'],
                                       'style="font-family: monospace;"'
                                       );
                        $mypage->leaf( 'p',
                                       '<a href="translatei.php?PhraseName='.
                                           urlencode($row['PhraseName']).
                                           '&LanguageID='.
                                           $rowx['Language'].
                                           '" onClick="return popup(this,\'translatepopup'.
                                           $row['OrderingNumber'].
                                           '\',700,500,\'yes\')">Edit</a>'
                                       );
                        $mypage->closenode();
                    }
                }
            }
            $mypage->closenode(); // div
        }
        $mypage->append($PaginationBar[1]);
    }
    if ( $Administrator == 2 ) {
        $mypage->leaf( 'h3',
                       'Create a new phrase on this page'
                       );
        $mypage->opennode('form', 'action="translated.php" method="POST"');
        $mypage->opennode( 'table',
                           'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                           );
        $mypage->opennode('tr');
        $mypage->leaf( 'td',
                       'Name:<br>(ASCII, no quotes, max. 16 characters)',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<input type="text" name="name" size=28 maxlength=16>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       'Ordering number:<br>(optional)',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<input type="text" name="orderingnumber" size=14 maxlength=6>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       'Phrase in English:<br>(max. 500 characters)',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<textarea name="phrasetext" cols="65" rows="6"></textarea>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       'Form of phrase in use:<br>(max. 500 characters)',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<textarea name="forminuse" cols="65" rows="4"></textarea>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       'Description of phrase:<br>(max. 250 characters)',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<textarea name="description" cols="65" rows="4"></textarea>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       'Notes on the phrase:<br>(max. 250 characters)',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       '<textarea name="notes" cols="65" rows="4"></textarea>'
                       );
        $mypage->next();
        $mypage->leaf( 'td',
                       '<input type="checkbox" name="phraseinuse" value="1" checked>&nbsp;',
                       'align=right'
                       );
        $mypage->leaf( 'td',
                       'This phrase is currently in use on the site'
                       );
        $mypage->next();
        $mypage->leaf('td', '');
        $mypage->leaf( 'td',
                       '<input type="submit" name="FormSubmit" value="Add phrase">'
                       );
        $mypage->closenode(2); // tr, table
        $mypage->emptyleaf( 'input',
                            'type="hidden" name="TheModule" value="'.
                                $ModuleID.
                                '"'
                            );
        $mypage->closenode(); // form
    }
    $mypage->leaf( 'p',
                   'Click <a href="translatea.php">here</a> to select another module to translate, <a href="boardview.php?BoardID=6">here</a> to go to the translation discussion board, or <a href="index.php">here</a> to return to the Main Page.'
                   );
} else if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then return to the translation pages. You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
} else {
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to access this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
}
$mypage->finish();

?>