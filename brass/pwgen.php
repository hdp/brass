<?php
define('TEST_MODE', true);
require('_std-include.php');

define( 'MIN_PASSWORD_LENGTH'     ,  6                          );
define( 'MAX_PASSWORD_LENGTH'     , 24                          );
define( 'DEFAULT_PASSWORD_LENGTH' , 15                          );
define( 'CHAR_LETTERS'            , 'abcdefghijkmnopqrstuvwxyz' );
    // lowercase L looks like numeral 1, so I exclude it
define( 'CHAR_DIGITS'             , '0123456789'                );
define( 'CHAR_PUNCTUATION'        , '_-~,.!#&/$'                );
define( 'PLACEHOLDER_CHARACTER'   , '@'                         );
    // This character should not appear in any of the strings CHAR_LETTERS,
    // CHAR_DIGITS or CHAR_PUNCTUATION.
$password_type_names = array( 'CA'                  ,
                              'CP'                  ,
                              'MU'                  ,
                              'SU'                  ,
                              'E&thinsp;/&thinsp;A' ,
                              'E&thinsp;/&thinsp;P' ,
                              'E&thinsp;/&thinsp;M' ,
                              'L'                   ,
                              'M'
                              );
$myprimes = array(  29,  31,  37,  41,  43,  47,  53,  59,  61,  67,
                    71,  73,  79,  83,  89,  97, 101, 103, 107, 109,
                   113, 127, 131, 137, 139, 149, 151, 157, 163, 167
                   );
    // This script does not check that the elements of the array are actually
    // primes, so remember to check this yourself if you edit the array.

define( 'CHAR_ALL'           , CHAR_LETTERS.CHAR_DIGITS.CHAR_PUNCTUATION );
define( 'NUM_PASSWORD_TYPES' , count($password_type_names)               );
define( 'NUM_LETTERS'        , strlen(CHAR_LETTERS)                      );
define( 'NUM_DIGITS'         , strlen(CHAR_DIGITS)                       );
define( 'NUM_PUNCTUATION'    , strlen(CHAR_PUNCTUATION)                  );
define( 'NUM_CHARS'          , strlen(CHAR_ALL)                          );
define( 'NUM_PRIMES'         , count($myprimes)                          );
define( 'LOWEST_PRIME'       , min($myprimes)                            );

function makepasswords ($PasswordLength, $StepLength) {
    $char_all = CHAR_ALL;
        // You cannot specify an offset for a constant string using square
        // brackets (i.e. you cannot do CHAR_ALL[5]), which is annoying
    $rtn = array_fill( 0,
                       12,
                       array_fill( 0,
                                   NUM_PASSWORD_TYPES,
                                   array_fill( 0,
                                               $PasswordLength,
                                               PLACEHOLDER_CHARACTER
                                               )
                                   )
                       );
    $specials = array_fill( 0,
                            12,
                            array_fill( 0,
                                        NUM_PASSWORD_TYPES,
                                        array(false, false)
                                        )
                            );
    $remaining_chars = array_fill( 0,
                                   12,
                                   array_fill( 0,
                                               NUM_PASSWORD_TYPES,
                                               $PasswordLength
                                               )
                                   );
    $divisorB = $PasswordLength * NUM_PASSWORD_TYPES;
    $limit = $divisorB * 12;
    $offset = mt_rand(0, $limit);
    for ($q=0; $q<$limit; $q++) {
        $r = $StepLength * $q + $offset;
        $c = $r % $PasswordLength;
        $b = (($r - $c) / $PasswordLength) % NUM_PASSWORD_TYPES;
        $a = (($r - $c - NUM_PASSWORD_TYPES*$b) / $divisorB) % 12;
        if ( $c == 0 ) {
            $k = mt_rand(0, NUM_LETTERS - 1);
        } else if ( ( $remaining_chars[$a][$b] == 3 and
                      !$specials[$a][$b][0] and
                      !$specials[$a][$b][1]
                      ) or
                    ( $remaining_chars[$a][$b] == 2 and
                      !$specials[$a][$b][0]
                      )
                    ) {
            $k = mt_rand(NUM_LETTERS, NUM_LETTERS + NUM_DIGITS - 1);
        } else if ( $remaining_chars[$a][$b] == 2 and
                    !$specials[$a][$b][1]
                    ) {
            $k = mt_rand(NUM_LETTERS + NUM_DIGITS, NUM_CHARS - 1);
        } else {
            $k = mt_rand(0, NUM_CHARS - 1);
        }
        if ( $k >= NUM_LETTERS + NUM_DIGITS ) {
            $specials[$a][$b][1] = true;
        } else if ( $k >= NUM_LETTERS ) {
            $specials[$a][$b][0] = true;
        }
        $remaining_chars[$a][$b]--;
        $rtn[$a][$b][$c] = $char_all[$k];
    }
    for ($a=0; $a<12; $a++) {
        for ($b=0; $b<NUM_PASSWORD_TYPES; $b++) {
            $rtn[$a][$b] = implode('', $rtn[$a][$b]);
            if ( strpos($rtn[$a][$b], PLACEHOLDER_CHARACTER) !== false ) {
                return false;
            }
            $rtn[$a][$b] = htmlspecialchars($rtn[$a][$b]);
        }
    }
    return $rtn;
}

if ( !$Administrator ) {
    $mypage = page::standard();
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'This page can only be accessed by administrators. Please return to the main page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}

if ( isset($_POST['PasswordLength']) ) {
    $PasswordLength = (int)$_POST['PasswordLength'];
    if ( $PasswordLength < MIN_PASSWORD_LENGTH ) {
        $PasswordLength = MIN_PASSWORD_LENGTH;
    }
    if ( $PasswordLength > MAX_PASSWORD_LENGTH ) {
        $PasswordLength = MAX_PASSWORD_LENGTH;
    }
} else {
    $PasswordLength = DEFAULT_PASSWORD_LENGTH;
}

$GeneratePasswords = false;
$ErrorMsg = '';
if ( !defined('PHP_INT_SIZE') ) {
    $ErrorMsg = 'Can\'t determine PHP_INT_SIZE.';
} else if ( PHP_INT_SIZE < 8 ) {
    $ErrorMsg = 'PHP_INT_SIZE is too small.';
} else if ( NUM_PASSWORD_TYPES >= LOWEST_PRIME ) {
    $ErrorMsg = 'Too many password-types. Please edit file and remove password-types, or modify list of prime numbers to suit more password-types.';
} else if ( isset($_POST['InitialCalls']) and isset($_POST['StepLength']) ) {
    $InitialCalls = (int)$_POST['InitialCalls'];
    $StepLength   = (int)$_POST['StepLength'];
    if ( $InitialCalls < 0 ) {
        $ErrorMsg = 'Initial Calls value is negative.';
    } else if ( $InitialCalls > 99999 ) {
        $ErrorMsg = 'Initial Calls value is too large.';
    } else if ( $InitialCalls > 99989 or
                strlen(count_chars($InitialCalls, 3)) <= 1
                    // every digit is the same
                ) {
        $ErrorMsg = 'Initial Calls value is not random enough. Please supply 5 random digits.';
    } else {
        $GeneratePasswords = true;
        $seed = 1000000 * microtime(true);
        $seed = (int)$seed;
        mt_srand($seed);
        for ($i=0; $i<$InitialCalls; $i++) {
            mt_rand();
        }
    }
    $StepLength = $myprimes[$StepLength % NUM_PRIMES];
}

$mypage = page::standard();
$mypage->script('pwgen.js');
$mypage->title_body('Password Generator', 'onLoad="start_timing();"');
$mypage->leaf('h2', 'Password Generator', 'style="text-align: center;"');
$mypage->opennode('form', 'action="pwgen.php" method="POST"');
$mypage->opennode('p', 'style="text-align: center"');
$mypage->text('Password Length:');
$mypage->opennode('select', 'name="PasswordLength"');
for ($i=MIN_PASSWORD_LENGTH; $i<=MAX_PASSWORD_LENGTH; $i++) {
    $mypage->leaf( 'option',
                   $i,
                   'value='.$i.
                       ( ( $i == $PasswordLength ) ?
                         ' selected' :
                         ''
                         )
                   );
}
$mypage->closenode(); // select
$mypage->text('---');
$mypage->text('Initial Calls (supply 5 random digits):');
$mypage->emptyleaf( 'input',
                    'type="text" name="InitialCalls" size=7 maxlength=5'
                    );
$mypage->text('---');
$mypage->emptyleaf( 'input',
                    'type="submit" value="Go" onClick="my_click_function();"'
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" name="StepLength" id="StepLength_id" value=0'
                    );
$mypage->closenode(2); // p, form

if ( $GeneratePasswords ) {
    $months = array( 'January' , 'February' , 'March'     ,
                     'April'   , 'May'      , 'June'      ,
                     'July'    , 'August'   , 'September' ,
                     'October' , 'November' , 'December'
                     );
    $passwords = makepasswords($PasswordLength, $StepLength);
    if ( $passwords === false ) {
        $ErrorMsg = 'Bad prime numbers. Please edit file and check array of prime numbers.';
    } else {
        $mypage->opennode( 'table',
                           'class="table_no_borders" style="margin-left: auto; margin-right: auto"'
                           );
        for ($i=0; $i<4; $i++) {
            $padding_top = $i ? 25 : 15;
            $mypage->opennode('tr');
            $mypage->leaf('td', '');
            $mypage->leaf( 'td',
                           $months[3*$i],
                           'style="text-align: center; font-weight: bold; padding-top: '.
                               $padding_top.
                               'px;"'
                           );
            $mypage->leaf( 'td',
                           $months[3*$i+1],
                           'style="text-align: center; font-weight: bold; padding-top: '.
                               $padding_top.
                               'px;"'
                           );
            $mypage->leaf( 'td',
                           $months[3*$i+2],
                           'style="text-align: center; font-weight: bold; padding-top: '.
                               $padding_top.
                               'px;"'
                           );
            $mypage->closenode();
            for ($j=0; $j<NUM_PASSWORD_TYPES; $j++) {
                $mypage->opennode('tr');
                $mypage->leaf( 'td',
                               $password_type_names[$j],
                               'style="text-align: right; padding-right: 25px;"'
                               );
                $mypage->leaf( 'td',
                               $passwords[3*$i][$j],
                               'style="text-align: center; font-family: monospace; font-size: 110%; padding-left: 25px; padding-right: 25px;"'
                               );
                $mypage->leaf( 'td',
                               $passwords[3*$i+1][$j],
                               'style="text-align: center; font-family: monospace; font-size: 110%; padding-left: 25px; padding-right: 25px;"'
                               );
                $mypage->leaf( 'td',
                               $passwords[3*$i+2][$j],
                               'style="text-align: center; font-family: monospace; font-size: 110%; padding-left: 25px; padding-right: 25px;"'
                               );
                $mypage->closenode();
            }
        }
        $mypage->finish();
    }
}

if ( $ErrorMsg != '' ) {
    $mypage->leaf('h3', $ErrorMsg, 'style="text-align: center;"');
}

$mypage->leaf( 'p',
               '<a href="index.php">Back to the Main Page</a>',
               'style="text-align: center"'
               );
$mypage->finish();

?>