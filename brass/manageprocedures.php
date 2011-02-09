<?php
define('TEST_MODE', true);
require('_std-include.php');

function squash ($mystring) {
    $squashedstring = '';
    $inbackquotes   = false;
    $insinglequotes = false;
    $indoublequotes = false;
    $backslashed    = false;
    $seenspace      = false;
    for ($i=0; $i<strlen($mystring); $i++) {
        if ( $seenspace and
             !$inbackquotes and
             !$insinglequotes and
             !$indoublequotes and
             ( $mystring[$i] == ' ' or
               $mystring[$i] == "\n" or
               $mystring[$i] == "\t"
               )
             ) {
            continue;
        }
        if ( $backslashed ) {
            $backslashed = false;
            $squashedstring .= $mystring[$i];
        } else {
            switch ( $mystring[$i] ) {
                case '\\':
                    $seenspace = false;
                    $backslashed = true;
                    break;
                case '`':
                    $seenspace = false;
                    if ( !$insinglequotes and !$indoublequotes ) {
                        $inbackquotes = !$inbackquotes;
                    }
                    $squashedstring .= '`';
                    break;
                case '\'':
                    $seenspace = false;
                    if ( !$inbackquotes and !$indoublequotes ) {
                        $insinglequotes = !$insinglequotes;
                    }
                    $squashedstring .= '\'';
                    break;
                case '"':
                    $seenspace = false;
                    if ( !$inbackquotes and !$insinglequotes ) {
                        $indoublequotes = !$indoublequotes;
                    }
                    $squashedstring .= '"';
                    break;
                case ' ':
                case "\r":
                case "\n":
                case "\t":
                    $seenspace = true;
                    if ( $mystring[$i] != ' ' and
                         ( $inbackquotes or
                           $insinglequotes or
                           $indoublequotes
                           )
                         ) {
                        $squashedstring .= $mystring[$i];
                        $tagname = 'pre';
                    } else {
                        $squashedstring .= ' ';
                    }
                    break;
                case '(':
                    $seenspace = true;
                    $squashedstring .= '(';
                    break;
                case ')':
                case ';':
                    if ( $seenspace and
                         !$inbackquotes and
                         !$insinglequotes and
                         !$indoublequotes
                         ) {
                        $squashedstring = trim($squashedstring);
                    }
                    // fallthrough intentional
                default:
                    $seenspace = false;
                    $squashedstring .= $mystring[$i];
            }
        }
    }
    return $squashedstring;
}

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

$mypage = page::standard();
if ( $Administrator < 2 ) {
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to make use of this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$change_every_procedure =  sanitise_bool(@$_POST['change_every_procedure']);
$changes = ( !$change_every_procedure and isset($_POST['changes']) ) ?
           array_unique( preg_split( '/[\\s"]+/',
                                     sanitise_str($_POST['changes'], STR_GPC),
                                     null,
                                     PREG_SPLIT_NO_EMPTY
                                     )
                         ) :
           array();

$hf_directory_resource = @opendir(substr(HIDDEN_FILES_PATH,0,-1));
if ( $hf_directory_resource === false ) {
    $mypage->title_body('Cannot access directory');
    $mypage->leaf( 'p',
                   'The script encountered a problem while attempting to access the hidden files directory.'
                   );
    $mypage->finish();
}

$finished   = false;
$file_names = array();
$files      = array();
while ( !$finished ) {
    $current_file_name = readdir($hf_directory_resource);
    if ( $current_file_name === false ) {
        $finished = true;
    } else if ( !is_dir(HIDDEN_FILES_PATH.$current_file_name) and
                substr($current_file_name, 0, 11) == 'procedures_' and
                substr($current_file_name, -4) == '.txt'
                ) {
        $file_names[] = htmlspecialchars($current_file_name);
        $this_file = file_get_contents(HIDDEN_FILES_PATH.$current_file_name);
        if ( $this_file === false ) {
            $mypage->title_body('File error');
            $mypage->leaf( 'p',
                           'Encountered a problem while attempting to read file "'.
                               htmlspecialchars($current_file_name).
                               '"'
                           );
            $mypage->finish();
        }
        $files[] = $this_file;
    }
}
closedir($hf_directory_resource);
$num_files = count($file_names);

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

$page_top = fragment::blank();
$procedure_treated = array();
$procedure_files   = array();
$procedure_names   = array();
$procedures        = array();

if ( $num_files ) {
    $page_top->leaf( 'p',
                     'The following procedures files were found:'
                     );
    $page_top->opennode( 'table',
                         'class="table_extra_horizontal_padding"'
                         );
    $page_top->opennode('thead');
    $page_top->opennode('tr');
    $page_top->leaf('th', 'File name');
    $page_top->leaf('th', 'Number of procedures');
    $page_top->closenode(2); // tr, thead
    $page_top->opennode( 'tbody',
                         'class="table_monospace_font"'
                         );
    for ($i=0; $i<$num_files; $i++) {
        $procedures_in_this_file = array();
        $num_procedures_in_this_file = preg_match_all( '/CREATE PROCEDURE "(.+?)".+?^END>>>>>$/ims',
                                                       $files[$i],
                                                       $procedures_in_this_file,
                                                       PREG_SET_ORDER
                                                       );
        $page_top->opennode('tr');
        $page_top->leaf( 'td',
                         $file_names[$i],
                         'style="font-family: monospace;"'
                         );
        $page_top->leaf('td', $num_procedures_in_this_file);
        $page_top->closenode();
        for ($j=0; $j<$num_procedures_in_this_file; $j++) {
            $procedure_treated[] = false;
            $procedure_files[]   = $i;
            $procedure_names[]   = $procedures_in_this_file[$j][1];
            if ( $change_every_procedure ) {
                $changes[] = $procedures_in_this_file[$j][1];
            }
            $procedures[] = in_array($procedures_in_this_file[$j][1], $changes) ?
                            squash(substr($procedures_in_this_file[$j][0], 0, -5)) :
                            null;
        }
    }
    $num_procedures = count($procedure_names);
    $page_top->closenode(2); // tbody, table
    $page_top->leaf( 'p',
                     'Total '.
                         $num_procedures.
                         ' procedures found in files.'
                     );
} else {
    $num_procedures = 0;
    $page_top->leaf( 'p',
                     'Detected no procedures files!',
                     'style="font-weight: bold; color: #FF0000;"'
                     );
}

if ( count(array_unique($procedure_names)) != count($procedure_names) ) {
    $mypage->title_body('Duplicate procedure names');
    $mypage->leaf( 'p',
                   'Duplicate procedure names in files. Please review files and ensure all procedure names are unique.'
                   );
    $mypage->finish();
}

$procedure_indexes = array_flip($procedure_names);
$change_indexes    = array_flip($changes);
$num_changes       = count($changes);
$no_change_notices = array();
$failed_changes    = array();
if ( $num_changes ) {
    $change_treated = array_fill(0, $num_changes, false);
}

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

$mypage->title_body('Manage procedures');
$mypage->loginbox();
$mypage->leaf('h1', 'Manage procedures');
$mypage->append($page_top);
$mypage->leaf('h3', 'Procedures');
$mypage->opennode( 'table',
                   'class="table_extra_horizontal_padding"'
                   );
$mypage->opennode('thead');
$mypage->opennode('tr');
$mypage->leaf('th', 'Procedure name');
$mypage->leaf('th', 'Database status');
$mypage->leaf('th', 'File');
$mypage->closenode(2); // tr, thead
$mypage->opennode( 'tbody',
                   'class="table_monospace_font"'
                   );
                   
$QR = dbquery( DBQUERY_READ_RESULTSET,
               'SELECT "ROUTINE_NAME" AS "RN" FROM "INFORMATION_SCHEMA"."ROUTINES" WHERE "ROUTINE_SCHEMA" = :database:',
               'database' , DB_DATABASE
               );
               
while ( $row = db_fetch_assoc($QR) ) {
    if ( array_key_exists($row['RN'], $procedure_indexes) ) {
        $procedure_treated[$procedure_indexes[$row['RN']]] = true;
        $file_status = $file_names[$procedure_files[$procedure_indexes[$row['RN']]]];
        if ( array_key_exists($row['RN'], $change_indexes) ) {
            $change_treated[$change_indexes[$row['RN']]] = true;
            if ( !dbquery( DBQUERY_ALLPURPOSE_TOLERATE_ERRORS,
                           'DROP PROCEDURE "'.$row['RN'].'"'
                           )
                 ) {
                $change_outcomes[$change_indexes[$row['RN']]] = 'Failed to drop existing version';
                $change_table_colour_attributes[$change_indexes[$row['RN']]] = 'class="myattn"';
                $failed_changes[] = htmlspecialchars($row['RN']);
                $database_status = 'DROP FAILED';
                $tr_colour_attribute = 'class="myattn"';
            } else if ( !dbquery( DBQUERY_ALLPURPOSE_TOLERATE_ERRORS,
                                  $procedures[$procedure_indexes[$row['RN']]]
                                  )
                        ) {
                $change_outcomes[$change_indexes[$row['RN']]] = 'Creation failed after dropping previous version';
                $failed_changes[] = htmlspecialchars($row['RN']);
                $change_table_colour_attributes[$change_indexes[$row['RN']]] = 'class="myattn"';
                $database_status = 'CREATION FAILED AFTER DROP';
                $tr_colour_attribute = 'class="myattn"';
            } else {
                $change_outcomes[$change_indexes[$row['RN']]] = 'Successfully replaced';
                $change_table_colour_attributes[$change_indexes[$row['RN']]] = 'class="mymove"';
                $database_status = 'Successfully replaced';
                $tr_colour_attribute = 'class="mymove"';
            }
        } else {
            $tr_colour_attribute = 'class="mymove"';
            $database_status = 'Present';
        }
    } else {
        $tr_colour_attribute = 'style="background-color: #FAE800;"'; // amber
        $file_status = 'NOT FOUND';
        if ( array_key_exists($row['RN'], $change_indexes) ) {
            $change_treated[$change_indexes[$row['RN']]] = true;
            $change_outcomes[$change_indexes[$row['RN']]] = 'Not found in files';
            $change_table_colour_attributes[$change_indexes[$row['RN']]] = 'style="background-color: #FAE800;"';
            $failed_changes[] = htmlspecialchars($row['RN']);
            $no_change_notices[] = htmlspecialchars($row['RN']);
            $database_status = 'UNCHANGED <a href="#no_change_notice"><b>*</b></a>';
        } else {
            $database_status = 'Present';
        }
    }
    $mypage->opennode('tr', $tr_colour_attribute);
    $mypage->leaf('td', htmlspecialchars($row['RN']));
    $mypage->leaf('td', $database_status);
    $mypage->leaf('td', $file_status);
    $mypage->closenode(); // tr
}

for ($i=0; $i<$num_procedures; $i++) {
    if ( !$procedure_treated[$i] ) {
        if ( array_key_exists($procedure_names[$i], $change_indexes) ) {
            $change_treated[$change_indexes[$procedure_names[$i]]] = true;
                // The following if/else block is ordered in this way so as
                // to mirror the organisation of the larger block above.
            if ( !dbquery( DBQUERY_ALLPURPOSE_TOLERATE_ERRORS,
                           $procedures[$i]
                           )
                 ) {
                $change_outcomes[$change_indexes[$procedure_names[$i]]] = 'Creation failed';
                $change_table_colour_attributes[$change_indexes[$procedure_names[$i]]] = 'class="myattn"';
                $failed_changes[] = htmlspecialchars($procedure_names[$i]);
                $database_status = 'CREATION FAILED';
                $tr_colour_attribute = 'class="myattn"';
            } else {
                $change_outcomes[$change_indexes[$procedure_names[$i]]] = 'Successfully created';
                $change_table_colour_attributes[$change_indexes[$procedure_names[$i]]] = 'class="mymove"';
                $database_status = 'Successfully created';
                $tr_colour_attribute = 'class="mymove"';
            }
        } else {
            $tr_colour_attribute = 'class="myattn"';
            $database_status = 'NOT PRESENT';
        }
        $mypage->opennode('tr', $tr_colour_attribute);
        $mypage->leaf('td', htmlspecialchars($procedure_names[$i]));
        $mypage->leaf('td', $database_status);
        $mypage->leaf('td', $file_names[$procedure_files[$i]]);
        $mypage->closenode(); //tr
    }
}

for ($i=0; $i<$num_changes; $i++) {
    if ( !$change_treated[$i] ) {
        $change_outcomes[$i] = 'Not found';
        $change_table_colour_attributes[$i] = 'style="background-color: #C4C4C4;"';
        $failed_changes[] = htmlspecialchars($changes[$i]);
        $mypage->opennode('tr', 'style="background-color: #C4C4C4;"');
        $mypage->leaf('td', htmlspecialchars($changes[$i]));
        $mypage->leaf( 'td',
                       'Not found anywhere; misspelled?',
                       'colspan=2'
                       );
        $mypage->closenode(); //tr
    }
}

$mypage->closenode(2); // tbody, table

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

if ( count($changes) ) {
    $mypage->leaf( 'p',
                   'You attempted to alter the following procedures:'
                   );
    $mypage->opennode( 'table',
                       'class="table_extra_horizontal_padding"'
                       );
    $mypage->opennode('thead');
    $mypage->opennode('tr');
    $mypage->leaf('th', 'Procedure name');
    $mypage->leaf('th', 'Outcome');
    $mypage->closenode(2); // thead, thead
    $mypage->opennode( 'tbody',
                       'class="table_monospace_font"'
                       );
    for ($i=0; $i<$num_changes; $i++) {
        $mypage->opennode('tr', $change_table_colour_attributes[$i]);
        $mypage->leaf('td', htmlspecialchars($changes[$i]));
        $mypage->leaf('td', $change_outcomes[$i]);
        $mypage->closenode(); // tr
    }
    $mypage->closenode(2); // tbody, table
}

if ( count($no_change_notices) ) {
    $mypage->leaf( 'p',
                   '<a name="no_change_notice"><b>*</b> This script will not drop procedures without attempting to replace them. If you have removed a procedure from the files and want it dropped from the database too, you must drop it using the all-purpose SQL query page. The following procedures were not altered, because they were found in the database but not in the files:</a>'
                   );
    $mypage->opennode( 'ul',
                       'style="font-family: monospace;"'
                       );
    for ($i=0; $i<count($no_change_notices); $i++) {
        $mypage->leaf('li', $no_change_notices[$i]);
    }
    $mypage->closenode(); // ul
}

///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////

$mypage->leaf( 'p',
               'Enter names of procedures to be re-defined here, with or without identifier quotes and separated by whitespace. (Procedure names themselves must not contain identifier quotes or whitespace.)'
               );
$mypage->opennode( 'form',
                   'action="manageprocedures.php" method="POST"'
                   );
$mypage->leaf( 'textarea',
               implode("\n", $failed_changes),
               'name="changes" cols=60 rows=9'
               );
$mypage->leaf( 'p',
               '<input type="submit" value="Go">'
               );
$mypage->closenode(); // form
$mypage->opennode( 'form',
                   'action="manageprocedures.php" method="POST"'
                   );
$mypage->emptyleaf( 'input',
                    'type="submit" value="Redefine All"'
                    );
$mypage->emptyleaf( 'input',
                    'type="checkbox" name="change_every_procedure" value=1'
                    );
$mypage->text('Yes, I do want to redefine every procedure found in the files.');
$mypage->closenode(); // form
$mypage->leaf( 'p',
               'Click <a href="index.php">here</a> to return to the Main Page.'
               );
$mypage->finish();

?>