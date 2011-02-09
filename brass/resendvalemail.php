<?php
require('_std-include.php');

$mypage = page::standard();
$mypage->title_body('Re-send validation email');
if ( @$_SESSION['LoggedIn'] ) {
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
    $mypage->finish();
} else if ( REGISTRATION_DISABLED ) {
    $mypage->leaf( 'p',
                   'New user registration and validation are currently disabled by an Administrator. Please wait until registration and validation are re-enabled.'
                   );
    $mypage->leaf( 'p',
                   'You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}

$errors   = false;
$ShowForm = false;
if ( isset($_POST['UserName']) ) {
    $errorlist = fragment::blank();
    $EscapedUserName = sanitise_str( $_POST['UserName'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     );
    $EscapedEmail = sanitise_str( @$_POST['Email'],
                                  STR_GPC |
                                      STR_ENSURE_ASCII |
                                      STR_TO_LOWERCASE
                                  );
    $EscapedPassword = trim(@$_POST['Password']);
    $row = dbquery( DBQUERY_READ_SINGLEROW,
                    'SELECT "UserID", "Password", "Email", "UserValidated" FROM "User" WHERE "Name" = :name:',
                    'name' , $EscapedUserName
                    );
    if ( $row === 'NONE' ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Couldn\'t find a user named '.
                              $EscapedUserName.
                              '. Please check that you spelled the account name correctly.'
                          );
    } else if ( $row['Password'] != crypt($EscapedPassword, $row['Password']) ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Incorrect password.'
                          );
    } else {
        if ( $row['UserValidated'] ) {
            $mypage->leaf( 'p',
                           'Your account is already validated. Please return to the Main Page by clicking <a href="index.php">here</a>. You should then find that you can log in.'
                           );
            $mypage->finish();
        }
        if ( $EscapedEmail === '' ) {
            $EmailToUse = $row['Email'];
        } else {
            $EmailChunks = explode('@', $EscapedEmail);
            if ( strlen($EscapedEmail) > 50 ) {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'Email address is too long. Maximum 50 characters.'
                                  );
            } else if ( count($EmailChunks) == 2 and
                        strlen($EmailChunks[0]) and
                        strlen($EmailChunks[1])
                        ) {
                $EmailChunksA = explode('.', $EmailChunks[0]);
                $EmailChunksB = explode('.', $EmailChunks[1]);
                $FoundInvalidChunk = false;
                do {
                    for ($i=0; $i<count($EmailChunksA); $i++) {
                        if ( !preg_match( '/\\A[a-z0-9!\\$&\\*\\-=\\^`\\|~#%\'\\+\\/\\?_\\{\\}]+\\Z/',
                                          $EmailChunksA[$i]
                                          ) ) {
                            $FoundInvalidChunk = true;
                            break 2;
                        }
                    }
                    for ($i=0; $i<count($EmailChunksB); $i++) {
                        if ( !preg_match( '/\\A[a-z0-9!\\$&\\*\\-=\\^`\\|~#%\'\\+\\/\\?_\\{\\}]+\\Z/',
                                          $EmailChunksB[$i]
                                          ) ) {
                            $FoundInvalidChunk = true;
                            break 2;
                        }
                    }
                } while ( false );
                if ( $FoundInvalidChunk ) {
                    $errors = true;
                    $errorlist->leaf( 'li',
                                      'The email address you entered either is not valid, or is of a form not presently supported.'
                                      );
                } else if ( dbquery( DBQUERY_READ_RESULTSET,
                                     'SELECT "UserID" FROM "User" WHERE "Email" = :email: AND "UserID" <> :user:',
                                     'email' , $EscapedEmail  ,
                                     'user'  , $row['UserID']
                                     ) !== 'NONE' ) {
                    $errors = true;
                    $errorlist->leaf( 'li',
                                      'Someone else is already using that email address.'
                                      );
                } else {
                    $EmailToUse = $EscapedEmail;
                    dbquery( DBQUERY_WRITE,
                             'UPDATE "User" SET "Email" = :newemail: WHERE "UserID" = :user:',
                             'newemail' , $EmailToUse    ,
                             'user'     , $row['UserID']
                             );
                }
            } else {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'The email address you entered either is not valid, or is of a form not presently supported.'
                                  );
            }
        }
    }
    if ( $errors ) {
        $ShowForm = true;
    }
} else {
    $ShowForm        = true;
    $EscapedUserName = '';
    $EscapedEmail    = '';
}

if ( $ShowForm ) {
    $mypage->loginbox();
    if ( $errors ) {
        $mypage->opennode('ul');
        $mypage->append($errorlist);
        $mypage->closenode(); // ul
    }
    $mypage->leaf( 'p',
                   'This page may be used to re-send the validation email you need to complete registration. Please fill in the user name and password you entered when you filled out the New User form. If you want to have the email sent to a different email address this time (or just make sure the spelling is correct), then fill in the email field - otherwise leave this field blank. Email addresses may be at most 50 characters long.'
                   );
    $mypage->opennode( 'form',
                       'action="resendvalemail.php" method="POST"'
                       );
    $mypage->opennode( 'table',
                       'class="table_no_borders" style="text-align: left;"'
                       );
    $mypage->opennode('tr');
    $mypage->leaf('td', 'Name:', 'align=right');
    $mypage->leaf( 'td',
                   '<input type="text" name="UserName" size=20 maxlength=20 value="'.
                       $EscapedUserName.
                       '">'
                   );
    $mypage->next();
    $mypage->leaf('td', 'Password:', 'align=right');
    $mypage->leaf( 'td',
                   '<input type="password" name="Password" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf('td', 'Email:', 'align=right');
    $mypage->leaf( 'td',
                   '<input type="text" name="Email" size=50 maxlength=50 value="'.
                       $EscapedEmail.
                       '">'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '<input type="submit" value="Re-Send">'
                   );
    $mypage->closenode(3); // tr, table, form
} else {
    $CharArray = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
                        'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
                        's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
                        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                        '-', '~', ',', '!'
                        );
    $thevstring = '';
    for ($i=0; $i<20; $i++) {
        $j = rand(0, 39);
        $thevstring .= $CharArray[$j];
    }
    $encryptedthevstring = crypt($thevstring, generateSalt());
    $QueryResult = dbquery( DBQUERY_WRITE,
                            'UPDATE "User" SET "ScrambleKey" = :scramblekey: WHERE "UserID" = :user:',
                            'scramblekey' , $encryptedthevstring ,
                            'user'        , $row['UserID']
                            );
    $subject = 'Validation Email for Brass (re-sent)';
    $body = '<p>In order to finish registering to play Brass, please click on the url on the next line, or copy and paste it into your browser\'s address bar.</p><p><a href="'.
            SITE_ADDRESS.
            'validate.php?UserID='.
            $row['UserID'].
            '&amp;VString='.
            $thevstring.
            '">'.
            SITE_ADDRESS.
            'validate.php?UserID='.
            $row['UserID'].
            '&amp;VString='.
            $thevstring.
            '</a></p>'.
            EMAIL_FOOTER;
    if ( send_email($subject, $body, $EmailToUse, null) ) {
        $mypage->leaf( 'p',
                       'You have been sent an email containing instructions for validating your account. Accounts that are not validated within two weeks are deleted automatically.'
                       );
        $mypage->leaf( 'p',
                       '<b><font color="#FF0000">If you do not appear to have received the validation email</font> after waiting several minutes,</b> please take the following steps:'
                       );
        $mypage->opennode('ul');
        $mypage->leaf( 'li',
                       'Check your spam folder for email from <b>automated@orderofthehammer.com</b>. If you find the validation email in your spam folder, you should mark the email as "not spam", or add <b>automated@orderofthehammer.com</b> to your list of trusted senders (or equivalent). This is so that further emails from the site don\'t end up in your spam folder (e.g. emails telling you that it\'s your turn in a game you\'re playing).'
                       );
        $mypage->leaf( 'li',
                       'If you don\'t find the email in your spam folder, your email provider might still have blocked it as spam. If your email provider gives you a way to add email addresses to a whitelist of "trusted senders", you should add <b>automated@orderofthehammer.com</b> to this list. Then proceed to the next step.'
                       );
        $mypage->leaf( 'li',
                       'Visit <a href="resendvalemail.php">this page</a> to re-send the email.'
                       );
        $mypage->closenode(); // ul
    } else {
        $mypage->leaf( 'p',
                       'It appears that there is still a problem sending you a validation email. Sorry!'
                       );
        $mypage->leaf( 'p',
                       'Are you certain that you typed the email address you are using correctly when you filled in the registration form? Please use this page again by clicking <a href="resendvalemail.php">here</a>, and re-enter the email address <b>as well as</b> the name of the account you created. Alternatively, you might choose to try again later, when hopefully things will work better.'
                       );
    }
}
$mypage->leaf( 'p',
               'You can return to the Main Page by clicking <a href="index.php">here</a>.'
               );
$mypage->finish();

?>