<?php

if ( TEST_MODE ) { error_reporting(E_ALL & ~E_STRICT); }
else             { error_reporting(0);                 }

if (!defined('PHP_MAJOR_VERSION')) {
    define('PHP_MAJOR_VERSION', (int)phpversion());
}
define('MYSQL_DATETIME_FORMAT', 'Y-m-d H:i:s');
date_default_timezone_set('UTC');
setlocale(LC_CTYPE, 'C');
ini_set('session.gc_maxlifetime', 10800);

define( 'HIDDEN_FILES_PATH'        , HIDDEN_FILES_PATH_NS.'/'        );
define( 'GFX_DIR'                  , GFX_DIR_NS.'/'                  );
define( 'NUM_MOVES_MADE_DIR'       , NUM_MOVES_MADE_DIR_NS.'/'       );
define( 'SPEC_DIR'                 , SPEC_DIR_NS.'/'                 );
define( 'TRANSLATION_CACHE_PREFIX' , TRANSLATION_CACHE_PREFIX_NS.'/' );

if ( ini_get('magic_quotes_sybase') or
     ini_get('register_globals') or
     ( PHP_MAJOR_VERSION < 6 and get_magic_quotes_runtime() ) or
     PHP_MAJOR_VERSION < 5
     ) {
    die('One or more server configuration settings has an unexpected value. Execution of script disabled as a precaution.');
}

function generateSalt () {
    $saltChars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $x = '$1$';
    for ($i=0; $i<8; $i++) {
        $x .= $saltChars[mt_rand(0, 61)];
    }
    return $x.'$';
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

define('DOCTYPE_ETC','<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="shortcut icon" type="image/vnd.microsoft.icon" href="'.SITE_ADDRESS.'favicon.ico"><link rel="apple-touch-icon" href="'.SITE_ADDRESS.'apple-touch-icon.png"><link rel="stylesheet" type="text/css" href="http://orderofthehammer.com/brass.css">');
define('EMAIL_FOOTER','<p>Please note that this automated message is being sent from an unwatched email account, and so direct replies are unlikely to be read. If you do not wish to receive further emails related to Brass Online then please log in to your account and adjust your preference settings (found in your User Details page). Alternatively, you can email unsubscribe@orderofthehammer.com and request that your email address be blanked in your profile.</p>');

$readerrormessage = 'Failed to read from the database. Suggest you try a second time. If the same error message appears again, please contact the Administrator.';
$writeerrormessage = 'Failed to write to the database. Suggest you try a second time. If the same error message appears again, please contact the Administrator.';
$unexpectederrormessage = 'Something unexpected happened. Suggest you go back to the start of whatever you were doing, and try a second time. If the same error message appears again, please contact the Administrator.';

$Currencies = array( 'Appropriate to board',
                     '&curren;',
                     '&pound;',
                     '&#8355;',
                     '$',
                     '&euro;',
                     '&yen;'
                     );
$CurrencySymbolAfterNumber = array( false,
                                    false,
                                    false,
                                    true,
                                    false,
                                    false,
                                    false
                                    );

$EscapeSequencesA = array( '[pound]',
                           '[pounds]',
                           '[franc]',
                           '[francs]',
                           '[yen]',
                           '[euro]',
                           '[euros]',
                           '[currency]',
                           '[slash]',
                           '[left]',
                           '[right]'
                           );
$EscapeSequencesB = array( '&pound;',
                           '&pound;',
                           '&#8355;',
                           '&#8355;',
                           '&yen;',
                           '&euro;',
                           '&euro;',
                           '&curren;',
                           '/',
                           '[',
                           ']'
                           );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

function dieandpost ($diemsg = 'This script has encountered a problem and cannot complete its task.') {
    $mypage = page::standard();
    $mypage->title_body('Error');
    $mypage->leaf('p', $diemsg);
    $mypage->leaf( 'p',
                   'You may want to try refreshing the page to see if this is just a momentary glitch. Alternatively, you can click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $first_POST_item = true;
    foreach ( $_POST as $key => $value ) {
        if ( stripos($key, 'password') === false ) {
            if ( $first_POST_item ) {
                $first_POST_item = false;
                $mypage->leaf( 'p',
                               'It looks like you provided POST data with your page request. Here I reproduce that data, in case you spent a long time typing it out. (I have excluded any variable whose name contains "password"). If you do not know what the below data means, you can safely ignore it.'
                               );
            }
            $mypage->leaf('h3', htmlspecialchars($key));
            $mypage->leaf('p', htmlspecialchars($value));
        }
    }
    $mypage->finish();
}

define('MYERROR_NOT_IN_FUNCTION', -1);
function myerror ($msg_friendly, $msg_informative = null, $levels_upward = 0) {
    if ( is_null($msg_informative) ) {
        die($msg_friendly);
    }
    $levels_upward++;
    $backtrace = debug_backtrace();
    do {
        for ($i=$levels_upward; $i<count($backtrace); $i++) {
            if ( isset($backtrace[$i]['file']) and
                 isset($backtrace[$i]['line'])
                 ) {
                $where = '; in script '.$backtrace[$i]['file'].
                         ' at line '.$backtrace[$i]['line'];
                break 2;
            }
        }
        $where = '; unable to determine script or line number at which function was called (backtrace failed unexpectedly)';
    } while (false);
    $full_error = $msg_informative.$where;
    error_log('[myerror] '.$full_error);
    if ( TEST_MODE ) {
        die($full_error);
    }
    die($msg_friendly);
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

function ensure_valid_ascii ($x) {
    $x = (string)$x;
    $length = strlen($x);
    $y = '';
    for ($i=0; $i<$length; $i++) {
        $c = ord($x[$i]);
        if ( ( $c > 31 and $c < 127 ) or
             $c == 9 or
             $c == 10 or
             $c == 13
             ) {
            $y .= $x[$i];
        } else {
            $y .= '?';
            if      ( $c > 253 ) {          }
            else if ( $c > 251 ) { $i += 5; }
            else if ( $c > 247 ) { $i += 4; }
            else if ( $c > 239 ) { $i += 3; }
            else if ( $c > 223 ) { $i += 2; }
            else if ( $c > 191 ) { $i += 1; }
        }
    }
    return $y;
}

function ensure_valid_utf8 ($x) {
    $x = (string)$x;
    $length = strlen($x);
    $y = '';
    $replacement = chr(239).chr(191).chr(189);
    for ($i=0; $i<$length; $i++) {
        $c = ord($x[$i]);
        if ( ( $c > 31 and $c < 127 ) or
             $c == 9 or
             $c == 10 or
             $c == 13
             ) {
            $y .= $x[$i];
        } else if ( $c < 194 or $c > 253 ) {
            if ( $c == 192 or $c == 193 ) { $i++; }
            $y .= $replacement;
        } else {
            if      ( $c > 251 ) { $extrabytes = 5; }
            else if ( $c > 247 ) { $extrabytes = 4; }
            else if ( $c > 239 ) { $extrabytes = 3; }
            else if ( $c > 223 ) { $extrabytes = 2; }
            else                 { $extrabytes = 1; }
            if ( $i + $extrabytes >= $length ) {
                $y .= $replacement;
                break;
            }
            if ( $extrabytes > 2 ) {
                $i += $extrabytes;
                $y .= $replacement;
                continue;
            }
            $z = $x[$i];
            while ( $extrabytes ) {
                $i++;
                $c = ord($x[$i]);
                if ( $c < 128 or $c > 191 ) {
                    $i += $extrabytes - 1;
                    $y .= $replacement;
                    continue 2;
                }
                $z .= $x[$i];
                $extrabytes--;
            }
            $y .= $z;
        }
    }
    return $y;
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

define('SANITISE_NO_FLAGS', 0);

define('BOOL_CHECKED', 1);
function sanitise_bool ($x, $flags = 0) {
    $x = (bool)$x;
    if ( $flags & BOOL_CHECKED and $x ) { return ' checked';  }
    if ( $flags & BOOL_CHECKED        ) { return '';          }
    return $x;
}

define( 'INT_TOO_LOW_SET_MIN'          ,  1 );
define( 'INT_TOO_LOW_SET_DEFAULT'      ,  2 );
define( 'INT_TOO_HIGH_SET_MAX'         ,  4 );
define( 'INT_TOO_HIGH_SET_DEFAULT'     ,  8 );
define( 'INT_OUT_OF_RANGE_SET_LIMIT'   ,  5 );
define( 'INT_OUT_OF_RANGE_SET_DEFAULT' , 10 );
define( 'INT_MIN_MAX_ANY_ORDER'        , 16 );
function sanitise_int ($x, $flags = 0, $min = null, $max = null, $default = null) {
    global $unexpectederrormessage;
    $x = (int)$x;
    $MinSet     = false;
    $MaxSet     = false;
    $DefaultSet = false;
    if ( !is_null($min)     ) { $min     = (int)$min;     $MinSet     = true; }
    if ( !is_null($max)     ) { $max     = (int)$max;     $MaxSet     = true; }
    if ( !is_null($default) ) { $default = (int)$default; $DefaultSet = true; }
    if ( $MinSet and $MaxSet and $min > $max ) {
        if ( $flags & INT_MIN_MAX_ANY_ORDER ) {
            $swapvar = $min;
            $min     = $max;
            $max     = $swapvar;
        } else {
            myerror( $unexpectederrormessage,
                     'Minimum value ('.$min.') and maximum value ('.$max.') are the wrong way around'
                     );
        }
    }
    if ( $MinSet and $x < $min ) {
        if ( $flags & INT_TOO_LOW_SET_MIN ) {
            return $min;
        } else if ( $DefaultSet and $flags & INT_TOO_LOW_SET_DEFAULT ) {
            return $default;
        } else {
            myerror( $unexpectederrormessage,
                     'User input integer value too low at '.$x.' (expected minimum '.$min.')'
                     );
        }
    }
    if ( $MaxSet and $x > $max ) {
        if ( $flags & INT_TOO_HIGH_SET_MAX ) {
            return $max;
        } else if ( $DefaultSet and $flags & INT_TOO_HIGH_SET_DEFAULT ) {
            return $default;
        } else {
            myerror( $unexpectederrormessage,
                     'User input integer value too high at '.$x.' (expected maximum '.$max.')'
                     );
        }
    }
    return $x;
}

define( 'STR_GPC'                          ,     1 );
define( 'STR_ENSURE_ASCII'                 ,     2 );
define( 'STR_TO_UPPERCASE'                 ,     4 );
define( 'STR_TO_LOWERCASE'                 ,     8 );
define( 'STR_NO_TRIM'                      ,    16 );
define( 'STR_NO_STRIP_CR'                  ,    32 );
define( 'STR_ESCAPE_HTML'                  ,    64 );
define( 'STR_STRIP_TAB_AND_NEWLINE'        ,   128 );
define( 'STR_CONVERT_ESCAPE_SEQUENCES'     ,   256 );
define( 'STR_PERMIT_FORMATTING'            ,   512 );
define( 'STR_HANDLE_IMAGES'                ,  1024 );
define( 'STR_PERMIT_ADMIN_HTML'            ,  2048 );
define( 'STR_DISREGARD_GAME_STATUS'        ,  4096 );
define( 'STR_EMAIL_FORMATTING'             ,  8192 );
define( 'STR_MULTIBYTE_LENGTH_CONSTRAINTS' , 16384 );
    // When changing these flags (or adding more), check sanitise_str_fancy.php
    // and make sure that the flags noted in the comments there are consistent
    // with those actually defined here.
function sanitise_str ($x, $flags = 0) {
    global $EscapeSequencesA, $EscapeSequencesB;
    $x = (string)$x;
    if ( $flags & STR_GPC and
         PHP_MAJOR_VERSION < 6 and
         get_magic_quotes_gpc()
         ) {
        $x = stripslashes($x);
    }
    if ( $flags & STR_ENSURE_ASCII )  { $x = ensure_valid_ascii($x);    }
    else                              { $x = ensure_valid_utf8($x);     }
    if (  $flags & STR_TO_UPPERCASE ) { $x = strtoupper($x);            }
    if (  $flags & STR_TO_LOWERCASE ) { $x = strtolower($x);            }
    if ( ~$flags & STR_NO_TRIM      ) { $x = trim($x);                  }
    if ( ~$flags & STR_NO_STRIP_CR  ) { $x = str_replace("\r", '', $x); }
    if ( $flags &
         ( STR_ESCAPE_HTML |
           STR_PERMIT_FORMATTING |
           STR_HANDLE_IMAGES |
           STR_PERMIT_ADMIN_HTML |
           STR_DISREGARD_GAME_STATUS |
           STR_EMAIL_FORMATTING
           )
         ) {
        $x = htmlspecialchars($x, ENT_COMPAT, 'UTF-8');
    }
    if ( $flags & STR_CONVERT_ESCAPE_SEQUENCES ) {
        $x = str_replace($EscapeSequencesA, $EscapeSequencesB, $x);
    }
    if ( $flags & STR_STRIP_TAB_AND_NEWLINE ) {
        $x = str_replace(array("\n","\t"), '', $x);
    }
    return $x;
}

function sanitise_enum ($x, $possibilities, $default = null) {
    global $unexpectederrormessage;
    $x = (string)$x;
    if ( PHP_MAJOR_VERSION < 6 and get_magic_quotes_gpc() ) {
        // sanitise_enum() is assumed always to be used on GPC data, so no
        // flag-based behaviour is provided to decide whether this happens
        $x = stripslashes($x);
    }
    $x = trim($x);
    if ( !is_array($possibilities) ) {
        myerror( $unexpectederrormessage,
                 'Second argument to sanitise_enum() should be an array of allowed values'
                 );
    }
    if ( !in_array($x, $possibilities) ) {
        if ( !is_null($default) ) {
            return (string)$default;
        } else {
            myerror( $unexpectederrormessage,
                     'User input selection list value of "'.
                         substr(htmlspecialchars($x), 0, 50).
                         '" is not one of the '.
                         count($possibilities).
                         ' permitted values'
                     );
        }
    }
    return $x;
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

define( 'DBQUERY_ECHO_STATEMENT_ONLY'        ,  1 );
define( 'DBQUERY_RETURN_STATEMENT_ONLY'      ,  2 );
define( 'DBQUERY_DISCONNECT_AFTERWARDS'      ,  4 );
define( 'DBQUERY_START_TRANSACTION'          ,  0 );
define( 'DBQUERY_COMMIT'                     ,  8 );
define( 'DBQUERY_ROLLBACK'                   , 16 );
define( 'DBQUERY_WRITE'                      , 24 );
define( 'DBQUERY_AFFECTED_ROWS'              , 32 );
define( 'DBQUERY_INSERT_ID'                  , 40 );
define( 'DBQUERY_READ_RESULTSET'             , 48 );
define( 'DBQUERY_READ_SINGLEROW'             , 56 );
define( 'DBQUERY_READ_INTEGER'               , 64 );
define( 'DBQUERY_READ_INTEGER_TOLERANT_ZERO' , 72 );
define( 'DBQUERY_READ_INTEGER_TOLERANT_NONE' , 80 );
define( 'DBQUERY_ALLPURPOSE_TOLERATE_ERRORS' , 88 );
function dbquery () {
    global $cxn, $readerrormessage, $unexpectederrormessage, $writeerrormessage;
    $args = func_get_args();
    $numargs = count($args);
    if ( ( $numargs % 2 and $numargs > 1 ) or
         $numargs == 0
         ) {
        myerror( $unexpectederrormessage,
                 'dbquery() expects either 1 argument or a positive even number of arguments, but received '.$numargs
                 );
    }
    $BehaviourFlags = $args[0];
    if ( !is_int($BehaviourFlags) ) {
        myerror($unexpectederrormessage, 'First argument to dbquery() should be an integer');
    }
    $QueryType = $BehaviourFlags - ($BehaviourFlags % 8);
    switch ( $QueryType ) {
        case DBQUERY_START_TRANSACTION:
        case DBQUERY_COMMIT:
        case DBQUERY_ROLLBACK:
        case DBQUERY_WRITE:
        case DBQUERY_AFFECTED_ROWS:
        case DBQUERY_INSERT_ID:
            $ErrorMessage = $writeerrormessage;
            break;
        case DBQUERY_READ_RESULTSET:
        case DBQUERY_READ_SINGLEROW:
        case DBQUERY_READ_INTEGER:
        case DBQUERY_READ_INTEGER_TOLERANT_ZERO:
        case DBQUERY_READ_INTEGER_TOLERANT_NONE:
            $ErrorMessage = $readerrormessage;
            break;
        case DBQUERY_ALLPURPOSE_TOLERATE_ERRORS:
            $ErrorMessage = $unexpectederrormessage;
            break;
        default:
            myerror($unexpectederrormessage, 'Unrecognised query type');
    }
    if ( $numargs > 1 ) {
        $Query = $args[1];
        if ( !is_string($Query) ) {
            myerror($unexpectederrormessage, 'Second argument to dbquery() should be a string');
        }
    } else if ( $QueryType > DBQUERY_ROLLBACK ) {
        myerror($unexpectederrormessage, 'No query supplied');
    }
    if ( $QueryType == DBQUERY_START_TRANSACTION ) { $Query = 'START TRANSACTION' ; }
    if ( $QueryType == DBQUERY_COMMIT            ) { $Query = 'COMMIT'            ; }
    if ( $QueryType == DBQUERY_ROLLBACK          ) { $Query = 'ROLLBACK'          ; }
    $ParameterNames  = array();
    $ParameterValues = array();
    for ($i=1; 2*$i<$numargs; $i++) {
        if ( !is_string($args[2*$i]) ) {
            myerror( $ErrorMessage,
                     'Parameter name number '.$i.' is not a string'
                     );
        }
        $ParameterNames[]  = ':'.$args[2*$i].':';
        $parameter_value = $args[2*$i+1];
        if ( !is_string($parameter_value) and
             !is_int($parameter_value) and
             !is_bool($parameter_value) and
             !is_null($parameter_value) and
             !is_array($parameter_value)
             ) {
            myerror( $ErrorMessage,
                     'Parameter value number '.$i.' is not a suitable type'
                     );
        }
        $ParameterValues[] = $parameter_value;
    }
    if ( count(array_unique($ParameterNames)) != count($ParameterNames) ) {
        myerror($ErrorMessage, 'Duplicated parameter names');
    }
    if ( !isset($cxn) ) {
        $cxn = @mysqli_connect('localhost', DB_USER, DB_PASSWORD, DB_DATABASE) or
               dieandpost('Failed to connect to the database. This may be because the Administrator is in the middle of routine password-changing. Please try again in five minutes.');
        mysqli_set_charset($cxn, 'utf8');
        mysqli_query($cxn, 'SET SESSION sql_mode = \'ANSI_QUOTES,TRADITIONAL\'');
    }
    $ParameterLocations = array();
    $OrderingArray      = array();
    for ($i=0; $i<count($ParameterValues); $i++) {
        $ParameterValues[$i] = treat_parameter( $cxn,
                                                false,
                                                $ParameterValues[$i],
                                                $ErrorMessage
                                                );
        $SearchStartPoint = 0;
        while ( true ) {
            $CurrentParameterLocation = strpos(substr($Query,$SearchStartPoint), $ParameterNames[$i]);
            if ( $CurrentParameterLocation === false ) {
                break;
            } else {
                $OrderingArray[]       = $i;
                $ParameterLocations[]  = $SearchStartPoint +
                                             $CurrentParameterLocation;
                $SearchStartPoint     += $CurrentParameterLocation +
                                             strlen($ParameterNames[$i]);
            }
        }
    }
    if ( count($ParameterLocations) ) {
        array_multisort($ParameterLocations, $OrderingArray);
        $ExplodedQuery = explode( ':!!:',
                                  str_replace($ParameterNames,':!!:',$Query)
                                  );
        $Query = '';
        for ($i=0; $i<count($ParameterLocations); $i++) {
            $Query .= $ExplodedQuery[$i].
                      $ParameterValues[$OrderingArray[$i]];
        }
        $Query .= $ExplodedQuery[count($ParameterLocations)];
    }
    if ( $BehaviourFlags & DBQUERY_ECHO_STATEMENT_ONLY ) {
        die('<pre>'.htmlspecialchars($Query).'</pre>');
    } else if ( $BehaviourFlags & DBQUERY_RETURN_STATEMENT_ONLY ) {
        return htmlspecialchars($Query);
    }
    $QueryResult = mysqli_query($cxn, $Query);
    if ( !$QueryResult ) {
        if ( $QueryType == DBQUERY_ALLPURPOSE_TOLERATE_ERRORS ) {
            $rtnvalue = false;
        } else {
            myerror( $ErrorMessage,
                     'Query results in error message: "'.
                         mysqli_error($cxn).
                         '"'
                     );
        }
    } else if ( $QueryType == DBQUERY_ALLPURPOSE_TOLERATE_ERRORS ) {
        $rtnvalue = true;
    } else if ( $QueryType == DBQUERY_INSERT_ID ) {
        if ( mysqli_more_results($cxn) ) {
            myerror( $ErrorMessage,
                     'Unexpected extra resultsets to retrieve after supposed INSERT query'
                     );
        } else {
            $rtnvalue = mysqli_insert_id($cxn);
        }
    } else if ( $QueryType == DBQUERY_AFFECTED_ROWS ) {
        $rtnvalue = mysqli_affected_rows($cxn);
    } else if ( $QueryResult === true or
                mysqli_num_rows($QueryResult) == 0
                ) {
        if ( $QueryType == DBQUERY_READ_INTEGER ) {
            myerror( $ErrorMessage,
                     'Expecting at least one row of output but found no rows'
                     );
        } else if ( $QueryType == DBQUERY_READ_INTEGER_TOLERANT_ZERO ) {
            $rtnvalue = 0;
        } else {
            $rtnvalue = 'NONE';
        }
    } else if ( $QueryType == DBQUERY_READ_INTEGER or
                $QueryType == DBQUERY_READ_INTEGER_TOLERANT_ZERO or
                $QueryType == DBQUERY_READ_INTEGER_TOLERANT_NONE
                ) {
        $rtnvalue = mysqli_fetch_row($QueryResult);
        $rtnvalue = (int)$rtnvalue[0];
    } else if ( $QueryType == DBQUERY_READ_SINGLEROW ) {
        $rtnvalue = mysqli_fetch_assoc($QueryResult);
    } else {
        $rtnvalue = $QueryResult;
    }
    while ( mysqli_more_results($cxn) ) { mysqli_next_result($cxn); }
    if ( $BehaviourFlags & DBQUERY_DISCONNECT_AFTERWARDS ) {
        mysqli_close($cxn);
        $cxn = null;
    }
    return $rtnvalue;
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

function db_fetch_assoc ($resultset) {
    global $unexpectederrormessage;
    if ( is_a($resultset, 'mysqli_result') ) {
        return mysqli_fetch_assoc($resultset);
    }
    if ( $resultset === 'NONE' ) {
        return false;
    }
    myerror( $unexpectederrormessage,
             'db_fetch_assoc() expects a mysqli_result or the string \'NONE\''
             );
}

function treat_parameter ($cxn, $within_array, $rawvalue, $ErrorMessage) {
    if ( is_array($rawvalue) ) {
        if ( $within_array ) {
            myerror( $ErrorMessage,
                     'Multidimensional arrays are not valid parameter values',
                     2
                     );
        }
        if ( !count($rawvalue) ) {
            myerror( $ErrorMessage,
                     'You have passed an empty array as a parameter value - you must check before passing the array that it is not empty',
                     1
                     );
        }
        $QuotedValues = false;
        foreach ( $rawvalue as $offset => $element ) {
            if ( is_string($rawvalue[$offset]) ) {
                $QuotedValues = true;
                break;
            }
        }
        foreach ( $rawvalue as $offset => $element ) {
            if ( $QuotedValues and
                 !is_string($element) and
                 !is_null($element)
                 ) {
                $AddQuotes = true;
            } else {
                $AddQuotes = false;
            }
            $rawvalue[$offset] = treat_parameter( $cxn,
                                                  true,
                                                  $element,
                                                  $ErrorMessage
                                                  );
            if ( $AddQuotes ) {
                $rawvalue[$offset] = '\''.$rawvalue[$offset].'\'';
            }
        }
        return '('.implode(', ', $rawvalue).')';
    }
    if ( is_string($rawvalue) ) {
        return '\''.mysqli_real_escape_string($cxn, $rawvalue).'\'';
    }
    if ( $rawvalue === true  ) { return '1';               }
    if ( $rawvalue === false ) { return '0';               }
    if ( is_null($rawvalue) )  { return 'NULL';            }
    if ( is_int($rawvalue) )   { return (string)$rawvalue; }
    myerror( $ErrorMessage,
             'Array parameter contains unsuitable values',
             2
             );
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

function EchoLoginForm ($redirect_vars = null, $AlwaysShowMessage = 0) {
    // If specified, $redirect_vars should be null or an array of redirect
    // variables to be included as hidden fields in the login form. (The
    // names of the fields will be the keys of the array, prefixed by
    // 'red_'.) Passing a non-array gives the same results as passing an
    // empty array. No sanitisation whatsoever is performed by this
    // function; sanitisation is the responsibility of the script calling
    // the function.
    global $Administrator, $Translator;
    echo '<noscript><p style="font-family: Helvetica,Tahoma,Verdana,Arial,sans-serif; font-size: 130%; font-weight: bold; text-align: center;">PLEASE ENABLE JAVASCRIPT</p></noscript>';
    $timebox = '<div style="float: right; border: none; margin: 5px 0px; text-align: right; font-size: 75%;">Generated '.
               date('M-d H:i:s').
               ' GMT</div>';
    if ( $_SESSION['LoggedIn'] ) {
        echo '<div id="loginbox" class="modularbox font_sans_serif mygame">'.
             $timebox.
             '<p>'.
             TEST_ENVIRONMENT_NOTICE.
             str_replace('\username', $_SESSION['MyUserName'], transtext('^LoggedInAs')).
             '</p><p><a href="usersgames.php?UserID='.$_SESSION['MyUserID'].'">'.
             transtext('^LkYourGames').
             '</a> --- <a href="userdetails.php?UserID='.$_SESSION['MyUserID'].'">'.
             transtext('^LkUserDetails').
             '</a> --- <a href="emails.php">Emails</a>';
        if ( $Administrator or $Translator ) {
            echo ' --- <a href="translatea.php">Translation interface</a>';
        }
        echo ' --- <a href="logout.php">'.
             transtext('^LkLogOut').
             '</a> --- <a href="index.php">'.
             transtext('^LkMainPage').
             '</a></p>';
    } else {
        echo '<div id="loginbox" class="modularbox font_sans_serif myattn">'.
             $timebox.
             '<p>'.
             TEST_ENVIRONMENT_NOTICE.
             transtext('^NotLoggedIn');
        if ( LOGIN_DISABLED ) {
            echo ' <b>(Login is currently disabled except for Administrators.)</b>';
        }
        echo '</p><form action="login.php" method="POST"><p>Name: <input type="text" name="Name" size=28 maxlength=20> Password: <input type="password" name="Password" size=28 maxlength=20> <input type="submit" value="Log in">';
        if ( is_array($redirect_vars) ) {
            foreach ( $redirect_vars as $key => $value ) {
                echo '<input type="hidden" name="red_'.
                     $key.
                     '" value="'.
                     $value.
                     '">';
            }
        }
        echo '</p></form><p>Or: <a href="recoveraccount.php">Recover Account (Forgotten Password)</a> / ';
        if ( LOGIN_DISABLED or REGISTRATION_DISABLED ) {
            echo '<del>Register new user / Re-send validation email</del> (Registration is currently disabled.)';
        } else {
            echo '<a href="newuser_language.php">Register new user</a> / <a href="resendvalemail.php">Re-send validation email</a>';
        }
        echo ' / <a href="index.php">'.
             transtext('^LkMainPage').
             '</a></p>';
    }
    if ( MAINTENANCE_DISABLED ) {
        echo '<p><font color="#FF0000"><b>'.
             transtext('^MaintenanceOff').
             '</b></font></p>';
    }
    if ( DISPLAY_SYSTEM_MESSAGE == 2 ) {
        echo '<p><span style="color: #FF0000; font-weight: bold;">'.
             transtext('^ImportantMsg').
             '</span> '.
             SYSTEM_MESSAGE.
             '</p>';
    } else if ( DISPLAY_SYSTEM_MESSAGE and $AlwaysShowMessage ) {
        echo '<p><span style="color: #FF0000; font-weight: bold;">'.
             transtext('^SystemMessage').
             '</span> '.
             SYSTEM_MESSAGE.
             '</p>';
    }
    echo '</div>';
}

function send_email ($subject, $body, $address, $replyto = null) {
    if (!EMAIL_ENABLED) { return false; }
    require_once('Mail.php');
    $headers = array( 'From'         => EMAIL_FROM,
                      'To'           => $address,
                      'Subject'      => $subject,
                      'Content-Type' => 'text/html; charset=utf-8'
                      );
    if (!is_null($replyto)) {
        $headers['Reply-To'] = $replyto;
    }
    $emarray = array( 'auth'     => true,
                      'host'     => EMAIL_HOST,
                      'username' => EMAIL_USERNAME,
                      'password' => EMAIL_PASSWORD
                      );
    $smtp = Mail::factory('smtp', $emarray);
    $mail = $smtp->send( $address,
                         $headers,
                         '<html><body style="font-family: monospace;">'.$body.'</body></html>'
                         );
    if (PEAR::isError($mail)) { return false; }
    else                      { return true;  }
}

function vname ($name, $suffix) {
    return $name.(is_null($suffix) ? '' : ' '.$suffix);
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

function transtext ($x) {
    global $TranslatableText;
    if ( isset($TranslatableText[$x]) ) { return $TranslatableText[$x]; }
    return 'MISSING TEXT ('.$x.')';
}

function get_translation_module ($modulenumber) {
    global $Language, $TranslatableText;
    $TranslationFilename = TRANSLATION_CACHE_PREFIX.
                           'tc-'.
                           $Language.
                           '-'.
                           $modulenumber.
                           '.txt';
    $getfromdb = false;
    if ( file_exists($TranslationFilename) and
         time() - filemtime($TranslationFilename) < 1000
         ) {
        $filecontents = file($TranslationFilename, FILE_IGNORE_NEW_LINES);
        if ( is_array($filecontents) ) {
            for ($i=0; 2*$i+1<count($filecontents); $i++) {
                $TranslatableText[$filecontents[2*$i]] = $filecontents[2*$i+1];
            }
        } else {
            $getfromdb = true;
        }
    } else {
        $getfromdb = true;
    }
    if ( $getfromdb ) {
        if ( $Language ) {
            $queryresult = dbquery( DBQUERY_READ_RESULTSET,
                                    'SELECT "Phrase"."PhraseName", IFNULL("ChosenTranslatedPhrase"."Translation", "Phrase"."FormInUse") AS "FormInUse" FROM "Phrase" LEFT JOIN "ChosenTranslatedPhrase" ON "Phrase"."PhraseName" = "ChosenTranslatedPhrase"."PhraseName" AND "ChosenTranslatedPhrase"."Language" = :language: WHERE "Phrase"."Module" = :module:',
                                    'language' , $Language     ,
                                    'module'   , $modulenumber
                                    );
        } else {
            $queryresult = dbquery( DBQUERY_READ_RESULTSET,
                                    'SELECT "PhraseName", "FormInUse" FROM "Phrase" WHERE "Module" = :module:',
                                    'module' , $modulenumber
                                    );
        }
        if ( $queryresult === 'NONE' ) {
            file_put_contents($TranslationFilename, "0\n0");
        } else {
            $filecontents = array();
            while ( $row = db_fetch_assoc($queryresult) ) {
                $TranslatableText[$row['PhraseName']] = $row['FormInUse'];
                $filecontents[] = $row['PhraseName'];
                $filecontents[] = $row['FormInUse'];
            }
            file_put_contents($TranslationFilename, implode("\n",$filecontents));
        }
    }
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

function string_or_null ($x) {
    if ( is_null($x) ) { return null; }
    return (string)$x;
}

define( 'DOCTYPE',
        '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
        );
define('INDENT_BLOCK', "\t");

define( 'TAGTREE_OPEN_NODE'  , 0 );
define( 'TAGTREE_CLOSE_NODE' , 1 );
define( 'TAGTREE_LEAF'       , 2 );
define( 'TAGTREE_EMPTY_LEAF' , 3 );
define( 'TAGTREE_TEXT'       , 4 );
define( 'TAGTREE_COMMENT'    , 5 );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

abstract class tagtree {
    protected $element_types,
              $node_names,
              $node_attributes,
              $leaf_contents,
              $open_nodes,
              $level;

    protected function __construct () {
        $this->element_types   = array();
        $this->node_names      = array();
        $this->node_attributes = array();
        $this->leaf_contents   = array();
        $this->open_nodes      = array();
        $this->level           = 0;
    }

    public function opennode ($node_name, $attributes = null) {
        $node_name = (string)$node_name;
        $this->element_types[]   = TAGTREE_OPEN_NODE;
        $this->node_names[]      = $node_name;
        $this->node_attributes[] = string_or_null($attributes);
        $this->open_nodes[]      = $node_name;
        $this->level++;
    }

    public function leaf ($node_name, $content, $attributes = null) {
        $this->element_types[]   = TAGTREE_LEAF;
        $this->node_names[]      = (string)$node_name;
        $this->leaf_contents[]   = (string)$content;
        $this->node_attributes[] = string_or_null($attributes);
    }

    public function emptyleaf ($node_name, $attributes = null) {
        $this->element_types[]   = TAGTREE_EMPTY_LEAF;
        $this->node_names[]      = (string)$node_name;
        $this->node_attributes[] = string_or_null($attributes);
    }

    public function text () {
        $args = func_get_args();
        $num_args = count($args);
        for ($i=0; $i<$num_args; $i++) {
            $this->element_types[] = TAGTREE_TEXT;
            $this->leaf_contents[] = (string)$args[$i];
        }
    }

    public function comment ($content) {
        $this->element_types[] = TAGTREE_COMMENT;
        $this->leaf_contents[] = (string)$content;
    }

    public function script ($location) {
        global $unexpectederrormessage;
        if ( $this->get_current_tag() === 'head' ) {
            $this->leaf( 'script',
                         '',
                         'type="text/javascript" src="'.$location.'"'
                         );
        } else {
            myerror( $unexpectederrormessage,
                     'script() called, but the current tag is not \'head\''
                     );
        }
    }

    public function title_body ($title, $attributes = null) {
        global $unexpectederrormessage;
        if ( $this->get_current_tag() === 'head' ) {
            $this->leaf('title', $title);
            $this->closenode();
            $this->opennode('body', $attributes);
        } else {
            myerror( $unexpectederrormessage,
                     'title_body() called, but the current tag is not \'head\''
                     );
        }
    }

    public function get_level () {
        return $this->level;
    }

    public function get_current_tag () {
        if ( $this->level > 0 ) {
            return $this->open_nodes[count($this->open_nodes)-1];
        }
        return false;
    }

    public function next ($attributes = null) {
        global $unexpectederrormessage;
        if ( $this->level <= 0 ) {
            myerror( $unexpectederrormessage,
                     'next() called, but current tag level is '.$this->level
                     );
        }
        $ct = $this->open_nodes[count($this->open_nodes)-1];
        $this->closenode();
        $this->opennode($ct, $attributes);
    }

    public function append (fragment $that) {
        $that_size = count($that->element_types);
        $j = 0;
        for ($i=0; $i<$that_size; $i++) {
            $current_element_type = $that->element_types[$i];
            if ( $current_element_type == TAGTREE_CLOSE_NODE ) {
                $this->closenode(1, 1);
            } else {
                $this->element_types[] = $current_element_type;
                if ( $current_element_type == TAGTREE_OPEN_NODE ) {
                    $this->open_nodes[] = $that->node_names[$j];
                    $this->level++;
                }
                if ( $current_element_type < TAGTREE_TEXT ) {
                    $j++;
                }
            }
        }
        $this->node_names      = array_merge( $this->node_names      , $that->node_names      );
        $this->node_attributes = array_merge( $this->node_attributes , $that->node_attributes );
        $this->leaf_contents   = array_merge( $this->leaf_contents   , $that->leaf_contents   );
    }

    public function build_output_strict ($debug = false, $nesting = 0) {
        global $unexpectederrormessage;
        if ( $debug ) {
            $this->comment('buildoutput() called here in debug mode. Current tag level is '.$this->level.'.');
            $this->closenode($this->level);
        } else if ( is_a($this, 'fragment') ) {
            myerror($unexpectederrormessage, 'You are trying to build output for a fragment');
        } else if ( $this->level ) {
            myerror( $unexpectederrormessage,
                     'You are trying to build output for an unbalanced tree (current tag level is '.
                         $this->level.
                         ')'
                     );
        }
        $current_level = 0;
        $k1 = 0;
        $k2 = 0;
        $k3 = 0;
        $real_open_tags = array();
        $rtn = DOCTYPE."\n";
        for ($i=0; $i<count($this->element_types); $i++) {
            if ( $this->element_types[$i] == TAGTREE_CLOSE_NODE ) {
                $current_level--;
            }
            $rtn .= "\n";
            if ( $current_level > 0 ) {
                $rtn .= str_repeat(INDENT_BLOCK, $current_level);
            }
            if ( $this->element_types[$i] == TAGTREE_CLOSE_NODE ) {
                $rtn .= '</'.array_pop($real_open_tags).'>';
            } else if ( $this->element_types[$i] == TAGTREE_TEXT ) {
                $rtn .= $this->leaf_contents[$k3];
                $k3++;
            } else if ( $this->element_types[$i] == TAGTREE_COMMENT ) {
                $rtn .= '<!-- '.$this->leaf_contents[$k3].' -->';
                $k3++;
            } else {
                $rtn .= '<'.$this->node_names[$k1];
                if ( !is_null($this->node_attributes[$k2]) ) {
                    $rtn .= ' '.$this->node_attributes[$k2];
                }
                $rtn .= '>';
                if ( $this->element_types[$i] == TAGTREE_LEAF ) {
                    $rtn .= $this->leaf_contents[$k3].'</'.$this->node_names[$k1].'>';
                    $k3++;
                } else if ( $this->element_types[$i] == TAGTREE_OPEN_NODE ) {
                    $real_open_tags[] = $this->node_names[$k1];
                    $current_level++;
                }
                $k1++;
                $k2++;
            }
        }
        return $rtn;
    }

    function loginbox ($redirect_vars = null, $AlwaysShowMessage = false) {
        // If specified, $redirect_vars should be null or an array of redirect
        // variables to be included as hidden fields in the login form. (The
        // names of the fields will be the keys of the array, prefixed by
        // 'red_'.) Passing a non-array gives the same results as passing an
        // empty array. No sanitisation whatsoever is performed by this
        // function; sanitisation is the responsibility of the script calling
        // the function.
        global $Administrator, $Translator;
        $this->opennode('noscript');
        $this->leaf( 'p',
                     'PLEASE ENABLE JAVASCRIPT',
                     'style="font-family: Helvetica,Tahoma,Verdana,Arial,sans-serif; font-size: 130%; font-weight: bold; text-align: center;"'
                     );
        $this->closenode(); // noscript
        if ( $_SESSION['LoggedIn'] ) {
            $this->opennode('div', 'id="loginbox" class="modularbox font_sans_serif mygame"');
            $this->leaf( 'div',
                         'Generated '.date('M-d H:i:s').' GMT',
                         'style="float: right; border: none; margin: 5px 0px; text-align: right; font-size: 75%;"'
                         );
            $this->leaf( 'p',
                         TEST_ENVIRONMENT_NOTICE.
                             str_replace('\username', $_SESSION['MyUserName'], transtext('^LoggedInAs'))
                         );
            $this->opennode('ul', 'class="navlinks"');
            $this->leaf( 'li',
                         '<a href="usersgames.php?UserID='.
                             $_SESSION['MyUserID'].
                             '">'.
                             transtext('^LkYourGames').
                             '</a>'
                         );
            $this->leaf( 'li',
                         '<a href="userdetails.php?UserID='.
                             $_SESSION['MyUserID'].
                             '">'.
                             transtext('^LkUserDetails').
                             '</a>'
                         );
            $this->leaf( 'li',
                         '<a href="emails.php">Emails</a>'
                         );
            if ( $Administrator or $Translator ) {
                $this->leaf( 'li',
                             '<a href="translatea.php">Translation interface</a>'
                             );
            }
            $this->leaf( 'li',
                         '<a href="logout.php">'.
                             transtext('^LkLogOut').
                             '</a>'
                         );
            $this->leaf( 'li',
                         '<a href="index.php">'.
                             transtext('^LkMainPage').
                             '</a>'
                         );
            $this->closenode();
        } else {
            $this->opennode('div', 'id="loginbox" class="modularbox font_sans_serif myattn"');
            $this->leaf( 'div',
                         'Generated '.date('M-d H:i:s').' GMT',
                         'style="float: right; border: none; margin: 5px 0px; text-align: right; font-size: 75%;"'
                         );
            $NotLoggedInStatement = TEST_ENVIRONMENT_NOTICE.
                                    transtext('^NotLoggedIn');
            if ( LOGIN_DISABLED ) {
                $NotLoggedInStatement .= ' <b>(Login is currently disabled except for Administrators.)</b>';
            }
            $this->leaf( 'p',
                         $NotLoggedInStatement
                         );
            $this->opennode('form', 'action="login.php" method="POST"');
            $this->opennode('p');
            $this->text('Name:');
            $this->emptyleaf( 'input',
                              'type="text" name="Name" size=28 maxlength=20'
                              );
            $this->text('Password:');
            $this->emptyleaf( 'input',
                              'type="password" name="Password" size=28 maxlength=20'
                              );
            $this->emptyleaf('input', 'type="submit" value="Log in"');
            $this->closenode(); // p
            if ( is_array($redirect_vars) ) {
                foreach ( $redirect_vars as $key => $value ) {
                    $this->emptyleaf( 'input',
                                      'type="hidden" name="red_'.
                                          $key.
                                          '" value="'.
                                          $value.
                                          '"'
                                      );
                }
            }
            $this->closenode(); // form
            $this->opennode('ul', 'class="navlinks"');
            $this->leaf( 'li',
                         '<a href="recoveraccount.php">Recover Account (Forgotten Password)</a>'
                         );
            if ( LOGIN_DISABLED or REGISTRATION_DISABLED ) {
                $this->leaf( 'li',
                             '<del>Register new user</del>'
                             );
                $this->leaf( 'li',
                             '<del>Re-send validation email</del>'
                             );
            } else {
                $this->leaf( 'li',
                             '<a href="newuser_language.php">Register new user</a>'
                             );
                $this->leaf( 'li',
                             '<a href="resendvalemail.php">Re-send validation email</a>'
                             );
            }
            $this->leaf( 'li',
                         '<a href="index.php">'.
                             transtext('^LkMainPage').
                             '</a>'
                         );
            $this->closenode();
        }
        if ( MAINTENANCE_DISABLED ) {
            $this->leaf( 'p',
                         transtext('^MaintenanceOff'),
                         'style="color: #FF0000; font-weight: bold;"'
                         );
        }
        if ( DISPLAY_SYSTEM_MESSAGE == 2 ) {
            $this->leaf( 'p',
                         '<span style="color: #FF0000; font-weight: bold;">'.
                             transtext('^ImportantMsg').
                             '</span> '.
                             SYSTEM_MESSAGE
                         );
        } else if ( DISPLAY_SYSTEM_MESSAGE and $AlwaysShowMessage ) {
            $this->leaf( 'p',
                         '<span style="color: #FF0000; font-weight: bold;">'.
                             transtext('^SystemMessage').
                             '</span> '.SYSTEM_MESSAGE
                         );
        }
        $this->closenode();
    }
} // class tagtree

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

class page extends tagtree {
    public static function blank () {
        $me = new self();
        return $me;
    }

    public static function standard () {
        $me = new self();
        $me->opennode('html');
        $me->opennode('head');
        $me->emptyleaf( 'meta',
                        'http-equiv="Content-Type" content="text/html; charset=utf-8"'
                        );
        $me->emptyleaf( 'link',
                        'rel="shortcut icon" type="image/vnd.microsoft.icon" href="'.
                            SITE_ADDRESS.
                            'favicon.ico"'
                        );
        $me->emptyleaf( 'link',
                        'rel="apple-touch-icon" href="'.
                            SITE_ADDRESS.
                            'apple-touch-icon.png"'
                        );
        $me->emptyleaf( 'link',
                        'rel="stylesheet" type="text/css" href="http://orderofthehammer.com/brass.css"'
                        );
        return $me;
    }

    public static function redirect ($seconds, $targeturl, $blurb = 'Thankyou.', $special = false) {
        $seconds = (int)$seconds;
        if ( $seconds <  1 ) { $seconds =  1; }
        if ( $seconds > 20 ) { $seconds = 20; }
        $spl = ( $seconds == 1 ) ? '' : 's';
        $me = page::standard();
        $me->script('redirect.js');
        if ( $targeturl === false ) {
            $me->title_body( 'Brass Online - Closing in '.
                                 $seconds.' second'.$spl,
                             'onLoad="redirect_begin('.
                                 $seconds.
                                 ', false)"'
                             );
            $me->leaf( 'p',
                       $blurb.
                           ' <span id="redirect_notice">This window will automatically close in <span id="num_seconds">'.
                           $seconds.' second'.$spl.
                           '</span>.</span>'
                       );
            $me->leaf( 'p',
                       'If this does not happen, please click <a href="javascript:window.close();">here</a>.'
                       );
        } else {
            $me->title_body( 'Brass Online - Redirecting in '.
                                 $seconds.' second'.$spl,
                             'onLoad="redirect_begin('.
                                 $seconds.
                                 ', \''.
                                 $targeturl.
                                 '\')"'
                             );
            $me->leaf( 'p',
                       $blurb.
                           ' <span id="redirect_notice">You should be automatically redirected in <span id="num_seconds">'.
                           $seconds.' second'.$spl.
                           '</span>.</span>'
                       );
            $me->leaf( 'p',
                       'If this does not happen, please click <a href="'.
                           $targeturl.
                           '">here</a>.'
                       );
        }
        if ( $special ) { return $me; }
        $me->finish();
    }

    public function closenode ($how_many = 1, $nesting = 0) {
        global $unexpectederrormessage;
        $how_many = (int)$how_many;
        for ($i=0; $i<$how_many; $i++) {
            if ( !$this->level ) {
                if ( $nesting ) {
                    $function_name = 'append';
                    $argument_number = '';
                    $backtrace_position = 1;
                } else {
                    $function_name = 'closenode';
                    $argument_number = ' at argument number '.($i+1);
                    $backtrace_position = 0;
                }
                myerror( $unexpectederrormessage,
                         'You are using '.
                             $function_name.
                             '() and you are attempting to go into negative tags with a non-fragment'.
                             $argument_number,
                         $backtrace_position
                         );
            }
            $this->element_types[] = TAGTREE_CLOSE_NODE;
            array_pop($this->open_nodes);
            $this->level--;
        }
    }

    public function finish () {
        global $cxn;
        if ( isset($cxn) and $cxn !== false ) {
            // if mysqli_connect fails, then $cxn will be Boolean false
            mysqli_close($cxn);
        }
        $this->closenode($this->level);
        echo $this->build_output_strict(false, 1);
        die();
    }
} // class page

class fragment extends tagtree {
    public static function blank () {
        $me = new self();
        return $me;
    }

    public function closenode ($how_many = 1, $nesting = 0) {
        $how_many = (int)$how_many;
        for ($i=0; $i<$how_many; $i++) {
            $this->element_types[] = TAGTREE_CLOSE_NODE;
            array_pop($this->open_nodes);
            $this->level--;
        }
    }
} // class fragment

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

if ( !isset($TrivialPage) and !isset($NoSession) ) {
    session_start();
    if ( !isset($SpecialFlag) ) {
        $_SESSION['AllowUse'] = 0;
    }
}

if ( isset($NoLoginStuff) or
     isset($TrivialPage) or
     isset($NoSession)
     ) {
    $Language = 0;
    $Translator = 0;
    $Administrator = 0;
    $Banned = 0;
} else {
    if ( @$_SESSION['LoggedIn'] ) {
        $_SESSION['MyUserID'] = (int)$_SESSION['MyUserID'];
        $rowY = dbquery( DBQUERY_READ_SINGLEROW,
                         'SELECT * FROM "User" WHERE "UserID" = :user:',
                         'user' , $_SESSION['MyUserID']
                         );
        if ( $rowY === 'NONE' ) {
            $_SESSION = array('LoggedIn' => 0);
            session_destroy();
            $mypage = page::standard();
            $mypage->title_body('Error');
            $mypage->leaf( 'p',
                           'Something appears to be wrong with your session data. You have been logged out. Please click <a href="index.php">here</a> to return to the Main Page.'
                           );
            $mypage->finish();
        }
        extract($rowY);
        if ( $DenyAccess ) {
            $_SESSION = array('LoggedIn' => 0);
            session_destroy();
            $mypage = page::standard();
            $mypage->title_body('Access denied');
            $mypage->leaf( 'p',
                           'An Administrator has denied access to your user account. You have been logged out. Please click <a href="index.php">here</a> to return to the Main Page.'
                           );
            $mypage->finish();
        }
    } else {
        $_SESSION['LoggedIn'] = 0;
        $Administrator = 0;
        $Translator = 0;
        $Banned = 0;
        $AlwaysShowCanals = 1;
        $AlwaysShowRails = 1;
        $ShowInaccessiblePlaces = 1;
        $ShowVirtualConnection = 1;
        $ProjIncludeMoney = 0;
        $CanalProj = 0;
        $BSIncomeTrack = 1;
        $ReverseTicker = 0;
        $CheckForMoves = 1;
        $MessagesPosted = 0;
        $AlwaysHideMsgs = 0;
        $ReverseMessages = 0;
        $CompactBoard = 1;
        $Language = 0;
        $Rating = 0;
        $PreferredCurrencySymbol = 0;
        $CurrencySymbolLocation = 0;
    }
    if ( LOGIN_DISABLED and !$Administrator ) {
        if ( @$_SESSION['LoggedIn'] ) {
            dbquery( DBQUERY_WRITE,
                     'UPDATE "User" SET "HasBeenEmailed" = 0 WHERE "UserID" = :user:',
                     'user' , $_SESSION['MyUserID']
                     );
        }
        $_SESSION['LoggedIn'] = 0;
        $Translator = 0;
        $Administrator = 0;
        $Banned = 0;
        $AlwaysShowCanals = 1;
        $AlwaysShowRails = 1;
        $ShowInaccessiblePlaces = 1;
        $ShowVirtualConnection = 1;
        $ProjIncludeMoney = 0;
        $CanalProj = 0;
        $BSIncomeTrack = 1;
        $ReverseTicker = 0;
        $CheckForMoves = 1;
        $MessagesPosted = 0;
        $AlwaysHideMsgs = 0;
        $ReverseMessages = 0;
        $CompactBoard = 1;
        $Language = 0;
        $Rating = 0;
        $PreferredCurrencySymbol = 0;
        $CurrencySymbolLocation = 0;
    }
}

if ( !isset($TrivialPage) ) {
    get_translation_module(1);
    get_translation_module(2);
}

?>