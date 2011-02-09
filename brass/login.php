<?php
require('_std-include.php');

define('LOGIN_ATTEMPTS_PERMITTED', 3);

$LoginDetailsSupplied = false;
$Method = null;
if ( isset($_POST['Name']) or isset($_POST['Password']) ) {
    $EscapedUserName = sanitise_str( @$_POST['Name'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     );
    $EscapedPassword = trim(@$_POST['Password']);
    $LoginDetailsSupplied = true;
    $Method = 'POST';
    $MustRedirect = false;
} else if ( isset($_GET['Name']) or isset($_GET['Password']) ) {
    $EscapedUserName = sanitise_str( @$_GET['Name'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     );
    $EscapedPassword = trim(@$_GET['Password']);
    $LoginDetailsSupplied = true;
    $Method = 'GET';
    $MustRedirect = true;
}

if ( !$LoginDetailsSupplied ) {
    $ErrorCode = 0;
} else {
    if ( strlen($EscapedPassword) > 20 ) {
        die($unexpectederrormessage);
    }
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   'SELECT "UserID", "Name", "Email", "Pronoun", "Password", "DenyAccess", "UserValidated", "BadAttempts", "BecomesAccessible", "Administrator" FROM "User" WHERE "Name" = :name:',
                   'name' , $EscapedUserName
                   );
    if ( $QR === 'NONE' ) {
        $_SESSION['LoggedIn'] = 0;
        $ErrorCode = 1;
    } else {
        $row = db_fetch_assoc($QR);
        $EscapedUserName = $row['Name'];
        $EscapedPassword = crypt($EscapedPassword, $row['Password']);
        if ( LOGIN_DISABLED and !$row['Administrator'] ) {
            $_SESSION['LoggedIn'] = 0;
            $ErrorCode = 6;
        } else if ( $row['DenyAccess'] ) {
            $_SESSION['LoggedIn'] = 0;
            $ErrorCode = 4;
        } else if ( !$row['UserValidated'] ) {
            $_SESSION['LoggedIn'] = 0;
            $ErrorCode = 5;
        } else if ( strtotime($row['BecomesAccessible']) - strtotime('now') > 0 ) {
            $_SESSION['LoggedIn'] = 0;
            $ErrorCode = 7;
            $BAtime = date( 'Y-m-d H:i:s',
                            strtotime($row['BecomesAccessible'])
                            );
        } else if ( $row['Password'] == $EscapedPassword ) {
            dbquery( DBQUERY_WRITE,
                     'UPDATE "User" SET "LastLogin" = UTC_TIMESTAMP(), "HasBeenEmailed" = 0 WHERE "UserID" = :user:',
                     'user' , $row['UserID']
                     );
            session_regenerate_id();
            $_SESSION = array();
            $_SESSION['LoggedIn']     = 1;
            $_SESSION['MyUserID']     = $row['UserID'];
            $_SESSION['MyUserName']   = $EscapedUserName;
            $_SESSION['MyPronounUC']  = $row['Pronoun'];
            switch ( $row['Pronoun'] ) {
                case 'He':
                    $_SESSION['MyPossessivePronounLC'] = 'his';
                    $_SESSION['MyGenderCode'] = 0;
                    break;
                case 'She':
                    $_SESSION['MyPossessivePronounLC'] = 'her';
                    $_SESSION['MyGenderCode'] = 1;
                    break;
                default:
                    $_SESSION['MyPossessivePronounLC'] = 'its';
                    $_SESSION['MyGenderCode'] = 2;
            }
            if ( $MustRedirect ) {
                $wheretogo       = @$_GET['wheretogo'];
                $wheretogodetail = @$_GET['wheretogodetail'];
                $wheretogo       = (int)$wheretogo;
                $wheretogodetail = (int)$wheretogodetail;
                header( 'Location: '.
                        SITE_ADDRESS.
                        'login.php?wheretogo='.
                        $wheretogo.
                        '&wheretogodetail='.
                        $wheretogodetail
                        );
                die();
            }
        } else if ( $row['BadAttempts'] < LOGIN_ATTEMPTS_PERMITTED ) {
            $NewBA = $row['BadAttempts'] + 1;
            $_SESSION['LoggedIn'] = 0;
            $ErrorCode = 2;
            dbquery( DBQUERY_WRITE,
                     'UPDATE "User" SET "BadAttempts" = :newba: WHERE "UserID" = :user:',
                     'newba' , $NewBA         ,
                     'user'  , $row['UserID']
                     );
        } else {
            $_SESSION['LoggedIn'] = 0;
            $ErrorCode  = 3;
            $CharArray  = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $thevstring = '';
            for ($i=0; $i<20; $i++) {
                $j = rand(0, 35);
                $thevstring .= $CharArray[$j];
            }
            $encryptedthevstring = crypt($thevstring, generateSalt());
            dbquery( DBQUERY_WRITE,
                     'UPDATE "User" SET "BadAttempts" = 0, "BecomesAccessible" = ADDTIME(UTC_TIMESTAMP(), \'1 0:0:0\'), "ScrambleKey" = :scramblekey: WHERE "UserID" = :user:',
                     'scramblekey' , $encryptedthevstring ,
                     'user'        , $row['UserID']
                     );
            $HaveSentEmail = '';
            if ( $row['Email'] != '' ) {
                $body = '<p>Too many bad attempts have been made to access your account in too short a time. Your account has been locked and will remain locked for 24 hours. To unlock it now without having to wait, please click on the url on the next line, or copy and paste it into your browser\'s address bar.</p><p><a href="'.
                        SITE_ADDRESS.
                        'unlock.php?UserID='.
                        $row['UserID'].
                        '&amp;VString='.
                        $thevstring.
                        '">'.
                        SITE_ADDRESS.
                        'unlock.php?UserID='.
                        $row['UserID'].
                        '&amp;VString='.
                        $thevstring.
                        '</a></p>'.
                        EMAIL_FOOTER;
                if ( send_email( 'Brass Account Locked',
                                 $body,
                                 $row['Email'],
                                 null
                                 )
                     ) {
                    $HaveSentEmail = ' (An email has been sent to your email address. You can unlock your account without having to wait by following the instructions in the email.)';
                }
            }
        }
    }
}

$redirect_vars = array();
if ( $Method === 'POST' ) {
    foreach ( $_POST as $key => $value ) {
        $k = (string)$key;
        $v = (int)$value;
        if ( substr($k, 0, 4) == 'red_' ) {
            $k = substr($k, 4);
            if ( strlen($k) and
                 ctype_alnum($k) and
                 !ctype_digit($k[0])
                 ) {
                $redirect_vars[$k] = $v;
            }
        }
    }
}
if ( $Method === 'GET' ) {
    foreach ( $_GET as $key => $value ) {
        $k = (string)$key;
        $v = (int)$value;
        if ( substr($k, 0, 4) == 'red_' ) {
            $k = substr($k, 4);
            if ( strlen($k) and
                 ctype_alnum($k) and
                 !ctype_digit($k[0])
                 ) {
                $redirect_vars[$k] = $v;
            }
        }
    }
}

if ( @$_SESSION['LoggedIn'] ) {
    $PreviousLocation = 'index.php';
    switch ( @$redirect_vars['Location'] ) {
        case 1:
            if ( isset($redirect_vars['GameID']) ) {
                $PreviousLocation = 'board.php?GameID='.
                                    $redirect_vars['GameID'];
            }
            break;
        case 2:
            if ( isset($redirect_vars['BoardID']) and
                 isset($redirect_vars['Page'])
                 ) {
                $PreviousLocation = 'boardview.php?BoardID='.
                                    $redirect_vars['BoardID'].
                                    '&Page='.
                                    $redirect_vars['Page'];
            }
            break;
        case 3:
            if ( isset($redirect_vars['ThreadID']) ) {
                $PreviousLocation = 'threadview.php?ThreadID='.
                                    $redirect_vars['ThreadID'];
            }
            break;
        case 4:
            if ( isset($redirect_vars['Mode']) and
                 isset($redirect_vars['Players']) and
                 isset($redirect_vars['Board']) and
                 isset($redirect_vars['UserID']) and
                 isset($redirect_vars['Page'])
                 ) {
                $PreviousLocation = 'statistics.php?Mode='.
                                    $redirect_vars['Mode'].
                                    '&Players='.
                                    $redirect_vars['Players'].
                                    '&Board='.
                                    $redirect_vars['Board'].
                                    '&UserID='.
                                    $redirect_vars['UserID'].
                                    '&Page='.
                                    $redirect_vars['Page'];
            }
            break;
        case 5:
            if ( isset($redirect_vars['UserID']) and
                 isset($redirect_vars['Page'])
                 ) {
                $PreviousLocation = 'oldgames.php?UserID='.
                                    $redirect_vars['UserID'].
                                    '&Page='.
                                    $redirect_vars['Page'];
            }
            break;
        case 6:
            if ( isset($redirect_vars['GameID']) ) {
                $PreviousLocation = 'lobby.php?GameID='.
                                    $redirect_vars['GameID'];
            }
            break;
        case 7:
            $PreviousLocation = 'usersgames.php?UserID='.
                                $_SESSION['MyUserID'];
            break;
    }
    page::redirect( 3,
                    $PreviousLocation,
                    'Successfully logged in.'
                    );
} else {
    switch ( $ErrorCode ) {
        case 1:
            $msg = 'Couldn\'t find a user named '.
                   $EscapedUserName.
                   '. Please check that you spelled the account name correctly.';
            break;
        case 2:
            $msg = 'Incorrect password for user '.
                   $EscapedUserName.
                   '. Be warned that too many bad attempts to log in in too short a time will result in your account being locked for 24 hours or until you unlock it by responding to an email.';
            break;
        case 3:
            $msg = 'Incorrect password for user '.
                   $EscapedUserName.
                   '. Too many bad attempts have been made to log in to this account in too short a time. Your account has been locked and will remain locked for 24 hours.'.
                   $HaveSentEmail;
            break;
        case 4:
            $msg = 'Cannot login: an Administrator has denied you access to your account.';
            break;
        case 5:
            $msg = 'Cannot login: Your account is not validated yet. Please check your emails and follow the instructions in the validation email you were sent. Accounts that are not validated within two weeks are deleted automatically. You may visit <a href="resendvalemail.php">this page</a> to reattempt sending the email.';
            break;
        case 6:
            $msg = 'Cannot login: Login is currently disabled by an Administrator. Only Administrators can log in at present.';
            break;
        case 7:
            $msg = 'Too many bad attempts have been made to log in to this account in too short a time. Your account has been locked. It will be unlocked at '.
                   $BAtime.
                   '. If you have access to the email address associated to this account, you can unlock the account immediately; please visit <a href="unlocke.php">this page</a> to do this.';
            break;
    }
    $mypage = page::standard();
    $mypage->title_body('Log in');
    $mypage->loginbox($redirect_vars);
    if ( $ErrorCode ) {
        $mypage->leaf('p', $msg);
    }
    $mypage->finish();
}

?>