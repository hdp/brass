<?php
require('_std-include.php');
require(HIDDEN_FILES_PATH.'sanitise_str_fancy.php');

if ( !isset($_POST['FormSubmit']) ) {
    die($unexpectederrormessage);
}

switch ( $_POST['FormSubmit'] ) {

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    case 'Submit':
        if ( !isset($_POST['ThePhrase']) or
             !isset($_POST['transtext'])
             ) {
            die($unexpectederrormessage);
        }
        $PhraseName = sanitise_str( $_POST['ThePhrase'],
                                    STR_GPC | STR_ENSURE_ASCII
                                    );
        $EscapedTranslation = sanitise_str_fancy( $_POST['transtext'],
                                                  1,
                                                  750,
                                                  STR_GPC |
                                                      STR_ESCAPE_HTML |
                                                      STR_STRIP_TAB_AND_NEWLINE |
                                                      STR_PERMIT_ADMIN_HTML
                                                  );
        $EscapedComment = sanitise_str_fancy( @$_POST['transcomment'],
                                              1,
                                              250,
                                              STR_GPC |
                                                  STR_ESCAPE_HTML |
                                                  STR_STRIP_TAB_AND_NEWLINE
                                              );
        if ( $EscapedComment[1] == -1 ) {
            $EscapedComment[0] = null;
        }
        $PostFailureTitle = false;
        do {
            if ( !$_SESSION['LoggedIn'] ) {
                $PostFailureTitle = 'Not logged in';
                $PostFailureMessage = 'You are not logged in. Please log in and then return to the translation pages.';
                break;
            }
            if ( !$Administrator and !$Translator ) {
                $PostFailureTitle = 'Not authorised';
                $PostFailureMessage = 'You are not authorised to make use of this page.';
                break;
            }
            if ( !$Language ) {
                $PostFailureTitle = 'Language set to English';
                $PostFailureMessage = 'Your language is set to English. Please visit <a href="translatea.php">this page</a> and set it to a different language, then close this window and try again.';
                break;
            }
            if ( dbquery( DBQUERY_READ_RESULTSET,
                          'SELECT "Module" FROM "Phrase" WHERE "PhraseName" = :phrasename:',
                          'phrasename' , $PhraseName
                          ) === 'NONE' ) {
                $PostFailureTitle = 'Phrase not found';
                $PostFailureMessage = 'Unable to find a phrase with the supplied identification code.';
                break;
            }
            $QR = dbquery( DBQUERY_READ_RESULTSET,
                           'SELECT "Language" FROM "TranslatedPhrase" WHERE "Language" = :language: AND "PhraseName" = :phrasename: AND "Translator" = :translator:',
                           'phrasename' , $PhraseName           ,
                           'translator' , $_SESSION['MyUserID'] ,
                           'language'   , $Language
                           );
            if ( $EscapedTranslation[1] == -1 and $QR === 'NONE' ) {
                $PostFailureTitle = 'Translation is missing';
                $PostFailureMessage = 'Your translation is missing.';
                break;
            }
            if ( $EscapedTranslation[1] == 1 ) {
                $PostFailureTitle = 'Translation is too long';
                $PostFailureMessage = 'Your translation is too long. Maximum 750 characters.';
                break;
            }
            if ( $EscapedComment[1] == 1 ) {
                $PostFailureTitle = 'Comment is too long';
                $PostFailureMessage = 'Your comment is too long. Maximum 250 characters.';
                break;
            }
        } while ( false );
        if ( $PostFailureTitle !== false ) {
            $mypage = page::standard();
            $mypage->title_body($PostFailureTitle);
            $mypage->leaf('p', $PostFailureMessage);
            if ( $EscapedTranslation[1] >= 0 ) {
                $mypage->leaf('h3', 'Here is the translation you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( $_POST['transtext'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=10'
                               );
            }
            if ( $EscapedComment[1] >= 0 ) {
                $mypage->leaf('h3', 'Here is the comment you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( @$_POST['transcomment'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=10'
                               );
            }
            $mypage->finish();
        }
        if ( $QR === 'NONE' ) {
            $QR = dbquery( DBQUERY_WRITE,
                           'INSERT INTO "TranslatedPhrase" ("Language", "PhraseName", "Translator", "Translation", "Comment") VALUES (:language:, :phrasename:, :translator:, :trans:, :comment:)',
                           'language'    , $Language              ,
                           'phrasename'  , $PhraseName            ,
                           'translator'  , $_SESSION['MyUserID']  ,
                           'trans'       , $EscapedTranslation[0] ,
                           'comment'     , $EscapedComment[0]
                           );
            $report = 'Successfully added translation.';
        } else if ( $EscapedTranslation[1] == -1 ) {
            $QR = dbquery( DBQUERY_WRITE,
                           'DELETE FROM "TranslatedPhrase" WHERE "Language" = :language: AND "PhraseName" = :phrasename: AND "Translator" = :translator:',
                           'language'    , $Language             ,
                           'phrasename'  , $PhraseName           ,
                           'translator'  , $_SESSION['MyUserID']
                           );
            $report = 'Successfully deleted translation.';
        } else {
            $QR = dbquery( DBQUERY_WRITE,
                           'UPDATE "TranslatedPhrase" SET "Translation" = :trans:, "Comment" = :comment:, "Chosen" = 0 WHERE "Language" = :language: AND "PhraseName" = :phrasename: AND "Translator" = :translator:',
                           'language'    , $Language              ,
                           'phrasename'  , $PhraseName            ,
                           'translator'  , $_SESSION['MyUserID']  ,
                           'trans'       , $EscapedTranslation[0] ,
                           'comment'     , $EscapedComment[0]
                           );
            $report = 'Successfully altered translation.';
        }
        page::redirect(2, false, $report);

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    case 'Choose':
        if ( !isset($_POST['Translator']) or
             !isset($_POST['Language']) or
             !isset($_POST['PhraseName']) or
             !isset($_POST['editedtext'])
             ) {
            die($unexpectederrormessage);
        }
        $Translator = sanitise_int($_POST['Translator']);
        $TLanguage  = sanitise_int($_POST['Language']);
        $PhraseName = sanitise_str( $_POST['PhraseName'],
                                    STR_GPC | STR_ENSURE_ASCII
                                    );
        $EscapedTranslation = sanitise_str_fancy( $_POST['editedtext'],
                                                  1,
                                                  750,
                                                  STR_GPC |
                                                      STR_STRIP_TAB_AND_NEWLINE
                                                  );
        $PostFailureTitle = false;
        do {
            if ( !$_SESSION['LoggedIn'] ) {
                $PostFailureTitle = 'Not logged in';
                $PostFailureMessage = 'You are not logged in. Please log in and then return to the translation pages.';
                break;
            }
            if ( $Administrator < 2 ) {
                $PostFailureTitle = 'Not authorised';
                $PostFailureMessage = 'You are not authorised to make use of this page.';
                break;
            }
            $QR = dbquery( DBQUERY_READ_RESULTSET,
                           'SELECT "PhraseName" FROM "ChosenTranslatedPhrase" WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                           'phrasename' , $PhraseName ,
                           'language'   , $TLanguage
                           );
            if ( $EscapedTranslation[1] == -1 and $QR === 'NONE' ) {
                $PostFailureTitle = 'Modified translation is missing';
                $PostFailureMessage = 'Your modified translation is missing.';
                break;
            }
            if ( $EscapedTranslation[1] == 1 ) {
                $PostFailureTitle = 'Modified translation is too long';
                $PostFailureMessage = 'The modified translation you entered is too long. Maximum 750 characters.';
                break;
            }
        } while ( false );
        if ( $PostFailureTitle !== false ) {
            $mypage = page::standard();
            $mypage->title_body($PostFailureTitle);
            $mypage->leaf('p', $PostFailureMessage);
            if ( $EscapedTranslation[1] >= 0 ) {
                $mypage->leaf('h3', 'Here is the modified translation you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( $_POST['editedtext'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=14'
                               );
            }
            $mypage->finish();
        }
        if ( $EscapedTranslation[1] == -1 ) {
            dbquery( DBQUERY_WRITE,
                     'DELETE FROM "ChosenTranslatedPhrase" WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                     'phrasename' , $PhraseName ,
                     'language'   , $TLanguage
                     );
            dbquery( DBQUERY_WRITE,
                     'UPDATE "TranslatedPhrase" SET "Chosen" = 0 WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                     'phrasename' , $PhraseName ,
                     'language'   , $TLanguage
                     );
            $report = 'Successfully deleted chosen translation.';
        } else {
            if ( @$_POST['unmarkothers'] ) {
                dbquery( DBQUERY_WRITE,
                         'UPDATE "TranslatedPhrase" SET "Chosen" = 0 WHERE "PhraseName" = :phrasename: AND "Language" = :language: AND "Translator" <> :translator:',
                         'phrasename' , $PhraseName ,
                         'translator' , $Translator ,
                         'language'   , $TLanguage
                         );
            }
            if ( @$_POST['marktranslation'] ) {
                dbquery( DBQUERY_WRITE,
                         'UPDATE "TranslatedPhrase" SET "Chosen" = 1 WHERE "PhraseName" = :phrasename: AND "Language" = :language: AND "Translator" = :translator:',
                         'phrasename' , $PhraseName ,
                         'translator' , $Translator ,
                         'language'   , $TLanguage
                         );
            }
            if ( $QR === 'NONE' ) {
                dbquery( DBQUERY_WRITE,
                         'INSERT INTO "ChosenTranslatedPhrase" ("PhraseName", "Language", "Translation") VALUES (:phrasename:, :language:, :trans:)',
                         'phrasename' , $PhraseName            ,
                         'language'   , $TLanguage             ,
                         'trans'      , $EscapedTranslation[0]
                         );
                $report = 'Successfully added chosen translation.';
            } else {
                dbquery( DBQUERY_WRITE,
                         'UPDATE "ChosenTranslatedPhrase" SET "Translation" = :trans: WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                         'phrasename' , $PhraseName            ,
                         'language'   , $TLanguage             ,
                         'trans'      , $EscapedTranslation[0]
                         );
                $report = 'Successfully altered chosen translation.';
            }
        }
        page::redirect(2, false, $report);

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    case 'Make changes':
        if ( !isset($_POST['Language']) or
             !isset($_POST['PhraseName']) or
             !isset($_POST['editedtext']) ) {
            die($unexpectederrormessage);
        }
        $TLanguage  = sanitise_int($_POST['Language']);
        $PhraseName = sanitise_str( $_POST['PhraseName'],
                                    STR_GPC | STR_ENSURE_ASCII
                                    );
        $EscapedTranslation = sanitise_str_fancy( $_POST['editedtext'],
                                                  1,
                                                  750,
                                                  STR_GPC |
                                                      STR_STRIP_TAB_AND_NEWLINE
                                                  );
        $PostFailureTitle = false;
        do {
            if ( !$_SESSION['LoggedIn'] ) {
                $PostFailureTitle = 'Not logged in';
                $PostFailureMessage = 'You are not logged in. Please log in and then return to the translation pages.';
                break;
            }
            if ( $Administrator < 2 ) {
                $PostFailureTitle = 'Not authorised';
                $PostFailureMessage = 'You are not authorised to make use of this page.';
                break;
            }
            if ( dbquery( DBQUERY_READ_RESULTSET,
                          'SELECT "PhraseName" FROM "ChosenTranslatedPhrase" WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                          'phrasename' , $PhraseName ,
                          'language'   , $TLanguage
                          ) === 'NONE' ) {
                $PostFailureTitle = 'Chosen translated phrase not found';
                $PostFailureMessage = 'Unable to find a chosen translated phrase with the supplied identification code in the given language.';
                break;
            }
            if ( $EscapedTranslation[1] == 1 ) {
                $PostFailureTitle = 'Modified translation is too long';
                $PostFailureMessage = 'The modified translation you entered is too long. Maximum 750 characters.';
                break;
            }
        } while ( false );
        if ( $PostFailureTitle !== false ) {
            $mypage = page::standard();
            $mypage->title_body($PostFailureTitle);
            $mypage->leaf('p', $PostFailureMessage);
            if ( $EscapedTranslation[1] >= 0 ) {
                $mypage->leaf('h3', 'Here is the modified translation you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( $_POST['editedtext'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=14'
                               );
            }
            $mypage->finish();
        }
        if ( $EscapedTranslation[1] == -1 ) {
            dbquery( DBQUERY_WRITE,
                     'DELETE FROM "ChosenTranslatedPhrase" WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                     'phrasename' , $PhraseName ,
                     'language'   , $TLanguage
                     );
            dbquery( DBQUERY_WRITE,
                     'UPDATE "TranslatedPhrase" SET "Chosen" = 0 WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                     'phrasename' , $PhraseName ,
                     'language'   , $TLanguage
                     );
            $report = 'Successfully deleted chosen translation.';
        } else {
            dbquery( DBQUERY_WRITE,
                     'UPDATE "ChosenTranslatedPhrase" SET "Translation" = :trans: WHERE "PhraseName" = :phrasename: AND "Language" = :language:',
                     'phrasename' , $PhraseName            ,
                     'language'   , $TLanguage             ,
                     'trans'      , $EscapedTranslation[0]
                     );
            $report = 'Successfully altered chosen translation.';
        }
        page::redirect(2, false, $report);

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

    default:
        if ( $_POST['FormSubmit'] == 'Add phrase' ) {
            if ( !isset($_POST['TheModule']) ) {
                die($unexpectederrormessage);
            }
            $ModuleID = sanitise_int($_POST['TheModule']);
            $Insertmode = true;
        } else {
            if ( @$_POST['unchoose'] ) { $Unchoose = true;  }
            else                       { $Unchoose = false; }
            if ( @$_POST['deletechosentrans'] ) { $DeleteChosenT = true;  }
            else                                { $DeleteChosenT = false; }
            if ( !isset($_POST['ThePhrase']) or
                 !isset($_POST['whichmodule'])
                 ) {
                die($unexpectederrormessage);
            }
            $PhraseName = sanitise_str( $_POST['ThePhrase'],
                                        STR_GPC | STR_ENSURE_ASCII
                                        );
            $ModuleID = sanitise_int($_POST['whichmodule']);
            $Insertmode = false;
        }
        if ( !isset($_POST['name']) or
             !isset($_POST['phrasetext']) or
             !isset($_POST['forminuse'])
             ) {
            die($unexpectederrormessage);
        }
        $NewPhraseName = sanitise_str( $_POST['name'],
                                       STR_GPC | STR_ENSURE_ASCII
                                       );
        if ( @$_POST['phraseinuse'] ) { $InUse = true;  }
        else                          { $InUse = false; }
        if ( @$_POST['orderingnumber'] ) {
            $OrderingNumber = sanitise_int( $_POST['orderingnumber'],
                                            SANITISE_NO_FLAGS,
                                            0
                                            );
        } else {
            $OrderingNumber = dbquery( DBQUERY_READ_INTEGER,
                                       'SELECT IFNULL((SELECT MAX("OrderingNumber") FROM "Phrase" WHERE "Module" = :module: AND "CurrentlyInUse" = :inuse:), 0) + 10 AS "NewOrderingNumber"',
                                       'module' , $ModuleID ,
                                       'inuse'  , $InUse
                                       );
        }
        if ( $OrderingNumber > 65535 ) { $OrderingNumber = 65535; }
        $EscapedPhraseInEnglish = sanitise_str_fancy( $_POST['phrasetext'],
                                                      1,
                                                      500,
                                                      STR_GPC |
                                                          STR_ESCAPE_HTML |
                                                          STR_STRIP_TAB_AND_NEWLINE
                                                      );
        $EscapedFormInUse = sanitise_str_fancy( $_POST['forminuse'],
                                                1,
                                                500,
                                                STR_GPC |
                                                    STR_STRIP_TAB_AND_NEWLINE
                                                );
        $EscapedDescription = sanitise_str_fancy( @$_POST['description'],
                                                  1,
                                                  250,
                                                  STR_GPC |
                                                      STR_ESCAPE_HTML |
                                                      STR_STRIP_TAB_AND_NEWLINE
                                                  );
        $EscapedNotes = sanitise_str_fancy( @$_POST['notes'],
                                            1,
                                            250,
                                            STR_GPC |
                                                STR_ESCAPE_HTML |
                                                STR_STRIP_TAB_AND_NEWLINE
                                            );
        if ( $EscapedDescription[1] == -1 ) { $EscapedDescription[0] = null; }
        if ( $EscapedNotes[1]       == -1 ) { $EscapedNotes[0]       = null; }
        $PostFailureTitle = false;
        do {
            if ( !$_SESSION['LoggedIn'] ) {
                $PostFailureTitle = 'Not logged in';
                $PostFailureMessage = 'You are not logged in. Please log in and then return to the translation pages.';
                break;
            }
            if ( $Administrator < 2 ) {
                $PostFailureTitle = 'Not authorised';
                $PostFailureMessage = 'You are not authorised to make use of this page.';
                break;
            }
            if ( dbquery( DBQUERY_READ_INTEGER,
                          'SELECT "ModuleID" FROM "TranslationModule" WHERE "ModuleID" = :module:',
                          'module' , $ModuleID
                          ) === 'NONE' ) {
                $PostFailureTitle = 'Module not found';
                $PostFailureMessage = 'Unable to find a translation module with the supplied module ID number.';
                break;
            }
            if ( !$Insertmode and
                 dbquery( DBQUERY_READ_INTEGER,
                          'SELECT "OrderingNumber" FROM "Phrase" WHERE "PhraseName" = :phrasename:',
                          'phrasename' , $PhraseName
                          ) === 'NONE' ) {
                $PostFailureTitle = 'Phrase not found';
                $PostFailureMessage = 'Unable to find a phrase with the supplied identification code.';
                break;
            }
            if ( strlen($NewPhraseName) == 0 ) {
                $PostFailureTitle = 'Phrase name missing';
                $PostFailureMessage = 'The phrase name is missing.';
                break;
            }
            if ( strlen($NewPhraseName) > 16 ) {
                $PostFailureTitle = 'Phrase name is too long';
                $PostFailureMessage = 'The phrase name you entered is too long. Maximum 16 characters.';
                break;
            }
            if ( $EscapedPhraseInEnglish[1] == -1 ) {
                $PostFailureTitle = 'Phrase text missing';
                $PostFailureMessage = 'Your phrase text is missing. (Note that phrases currently cannot be deleted outright using this interface.)';
                break;
            }
            if ( $EscapedPhraseInEnglish[1] == 1 ) {
                $PostFailureTitle = 'Phrase text is too long';
                $PostFailureMessage = 'Your phrase text is too long. Maximum 500 characters.';
                break;
            }
            if ( $EscapedFormInUse[1] == -1 ) {
                $PostFailureTitle = '&quot;Form in use&quot; missing';
                $PostFailureMessage = 'Your &quot;form in use&quot; is missing. (Note that phrases currently cannot be deleted outright using this interface.)';
                break;
            }
            if ( $EscapedFormInUse[1] == 1 ) {
                $PostFailureTitle = '&quot;Form in use&quot; is too long';
                $PostFailureMessage = 'Your &quot;form in use&quot; is too long. Maximum 500 characters.';
                break;
            }
            if ( $EscapedDescription[1] == 1 ) {
                $PostFailureTitle = 'Description is too long';
                $PostFailureMessage = 'Your description is too long. Maximum 250 characters.';
                break;
            }
            if ( $EscapedNotes[1] == 1 ) {
                $PostFailureTitle = 'Notes are too long';
                $PostFailureMessage = 'Your notes are too long. Maximum 250 characters.';
                break;
            }
        } while ( false );
        if ( $PostFailureTitle !== false ) {
            $mypage = page::standard();
            $mypage->title_body($PostFailureTitle);
            $mypage->leaf('p', $PostFailureMessage);
            if ( strlen($NewPhraseName) ) {
                $mypage->leaf('h3', 'Here is the phrase name you entered:');
                $mypage->leaf( 'pre',
                               htmlspecialchars($NewPhraseName)
                               );
            }
            if ( $EscapedPhraseInEnglish[1] >= 0 ) {
                $mypage->leaf('h3', 'Here is the phrase text you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( $_POST['phrasetext'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=10'
                               );
            }
            if ( $EscapedFormInUse[1] >= 0 ) {
                $mypage->leaf('h3', 'Here is the &quot;form in use&quot; you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( $_POST['forminuse'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=10'
                               );
            }
            if ( $EscapedDescription[1] >= 0 ) {
                $mypage->leaf('h3', 'Here is the description you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( @$_POST['description'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=10'
                               );
            }
            if ( $EscapedNotes[1] >= 0 ) {
                $mypage->leaf('h3', 'Here are the notes you entered:');
                $mypage->leaf( 'textarea',
                               sanitise_str( @$_POST['notes'],
                                             STR_GPC | STR_ESCAPE_HTML
                                             ),
                               'cols=80 rows=10'
                               );
            }
            $mypage->finish();
        }
        if ( $Insertmode ) {
            dbquery( DBQUERY_WRITE,
                     'INSERT INTO "Phrase" ("PhraseName", "OrderingNumber", "PhraseInEnglish", "FormInUse", "CurrentlyInUse", "Module", "Description", "Notes") VALUES (:phrasename:, :orderingnumber:, :english:, :fiu:, :inuse:, :module:, :desc:, :notes:)',
                     'phrasename'     , $NewPhraseName             ,
                     'orderingnumber' , $OrderingNumber            ,
                     'inuse'          , $InUse                     ,
                     'module'         , $ModuleID                  ,
                     'english'        , $EscapedPhraseInEnglish[0] ,
                     'fiu'            , $EscapedFormInUse[0]       ,
                     'desc'           , $EscapedDescription[0]     ,
                     'notes'          , $EscapedNotes[0]
                     );
            page::redirect( 3,
                            'translateb.php?ModuleID='.$EscapedModuleID,
                            'Successfully added phrase.'
                            );
        }
        if ( $Unchoose ) {
            dbquery( DBQUERY_WRITE,
                     'UPDATE "TranslatedPhrase" SET "Chosen" = 0 WHERE "PhraseName" = :phrasename:',
                     'phrasename' , $PhraseName
                     );
        }
        if ( $DeleteChosenT ) {
            dbquery( DBQUERY_WRITE,
                     'DELETE FROM "ChosenTranslatedPhrase" WHERE "PhraseName" = :phrasename:',
                     'phrasename' , $PhraseName
                     );
        }
        dbquery( DBQUERY_WRITE,
                 'UPDATE "Phrase" SET "PhraseName" = :newname:, "OrderingNumber" = :orderingnumber:, "PhraseInEnglish" = :english:, "FormInUse" = :fiu:, "CurrentlyInUse" = :inuse:, "Module" = :module:, "Description" = :desc:, "Notes" = :notes: WHERE "PhraseName" = :phrasename:',
                 'newname'        , $NewPhraseName             ,
                 'orderingnumber' , $OrderingNumber            ,
                 'inuse'          , $InUse                     ,
                 'module'         , $ModuleID                  ,
                 'phrasename'     , $PhraseName                ,
                 'english'        , $EscapedPhraseInEnglish[0] ,
                 'fiu'            , $EscapedFormInUse[0]       ,
                 'desc'           , $EscapedDescription[0]     ,
                 'notes'          , $EscapedNotes[0]
                 );
        page::redirect( 2,
                        false,
                        'Successfully altered phrase.'
                        );

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

}

?>