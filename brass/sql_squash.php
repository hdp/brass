<?php
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
if ( !$Administrator ) {
    $mypage->title_body('Not Permitted');
    $mypage->leaf( 'p',
                   'You are not permitted to view this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$mypage->title_body('SQL squasher');
$mypage->leaf('h3', 'SQL squasher');
if ( isset($_POST['sqltosquash']) ) {
    $mypage->leaf( 'p',
                   htmlspecialchars(squash($_POST['sqltosquash'])),
                   'style="font-family: monospace;"'
                   );
}
$mypage->opennode('form', 'action="sql_squash.php" method="POST"');
$mypage->leaf('textarea', '', 'rows=16 cols=80 name="sqltosquash"');
$mypage->opennode('p');
$mypage->emptyleaf('input', 'type="submit" value="Go"');
$mypage->text('Or, click <a href="index.php">here</a> to return to the Main Page.');
$mypage->finish();

?>