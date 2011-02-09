<?php
require('_std-include.php');

if ( isset($_GET['Language']) ) {
    $Language = sanitise_int( $_GET['Language'],
                              INT_OUT_OF_RANGE_SET_DEFAULT,
                              0,
                              NUM_FOREIGN_LANGUAGES - 1,
                              0
                              );
} else {
    $Language = sanitise_int( @$_POST['Language'],
                              INT_OUT_OF_RANGE_SET_DEFAULT,
                              0,
                              NUM_FOREIGN_LANGUAGES - 1,
                              0
                              );
}

$mypage = page::standard();
if ( REGISTRATION_DISABLED ) {
    $mypage->title_body('Registration disabled');
    $mypage->leaf( 'p',
                   'New user registration and validation are currently disabled by an Administrator. Please wait until registration and validation are re-enabled.'
                   );
    $mypage->leaf( 'p',
                   'You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
} else if ( $_SESSION['LoggedIn'] ) {
    $mypage->title_body('Logged in');
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
    $mypage->finish();
}

$TZStrings = array( 'Not Saying',
                    'GMT - 11:30', 'GMT - 11:00', 'GMT - 10:30',
                    'GMT - 10:00', 'GMT - 9:30' , 'GMT - 9:00' ,
                    'GMT - 8:30' , 'GMT - 8:00' , 'GMT - 7:30' ,
                    'GMT - 7:00' , 'GMT - 6:30' , 'GMT - 6:00' ,
                    'GMT - 5:30' , 'GMT - 5:00' , 'GMT - 4:30' ,
                    'GMT - 4:00' , 'GMT - 3:30' , 'GMT - 3:00' ,
                    'GMT - 2:30' , 'GMT - 2:00' , 'GMT - 1:30' ,
                    'GMT - 1:00' , 'GMT - 0:30' , 'GMT'        ,
                    'GMT + 0:30' , 'GMT + 1:00' , 'GMT + 1:30' ,
                    'GMT + 2:00' , 'GMT + 2:30' , 'GMT + 3:00' ,
                    'GMT + 3:30' , 'GMT + 4:00' , 'GMT + 4:30' ,
                    'GMT + 5:00' , 'GMT + 5:30' , 'GMT + 6:00' ,
                    'GMT + 6:30' , 'GMT + 7:00' , 'GMT + 7:30' ,
                    'GMT + 8:00' , 'GMT + 8:30' , 'GMT + 9:00' ,
                    'GMT + 9:30' , 'GMT + 10:00', 'GMT + 10:30',
                    'GMT + 11:00', 'GMT + 11:30', 'GMT &plusmn; 12:00'
                    );
$TZSelectedStrings = array( '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', '', '',
                            '', '', '', ''
                            );
if ( @$_POST['FormSubmitted'] ) {
    $errors    = false;
    $errorlist = fragment::blank();
    $ShowForm  = false;
    $FirstShow = false;
    $EscapedUserName = sanitise_str( @$_POST['UserName'],
                                     STR_GPC | STR_STRIP_TAB_AND_NEWLINE
                                     );
        // html escaping and length checking occur below
    $EscapedEmail = sanitise_str( @$_POST['Email'],
                                  STR_GPC |
                                      STR_ENSURE_ASCII |
                                      STR_TO_LOWERCASE
                                  );
    $EmailChunks = explode('@', $EscapedEmail);
    if ( count($EmailChunks) == 2 and
         strlen($EmailChunks[0]) and
         strlen($EmailChunks[1])
         ) {
        $EmailChunksA = explode('.', $EmailChunks[0]);
        $EmailChunksB = explode('.', $EmailChunks[1]);
        $FoundInvalidChunk = false;
        do {
            for ($i=0; $i<count($EmailChunksA); $i++) {
                if ( !preg_match('/\\A[a-z0-9!\\$&\\*\\-=\\^`\\|~#%\'\\+\\/\\?_\\{\\}]+\\Z/', $EmailChunksA[$i]) ) {
                    $FoundInvalidChunk = true;
                    break 2;
                }
            }
            for ($i=0; $i<count($EmailChunksB); $i++) {
                if ( !preg_match('/\\A[a-z0-9!\\$&\\*\\-=\\^`\\|~#%\'\\+\\/\\?_\\{\\}]+\\Z/', $EmailChunksB[$i]) ) {
                    $FoundInvalidChunk = true;
                    break 2;
                }
            }
        } while ( false );
        if ( $FoundInvalidChunk ) {
            $errors = true;
            $errorlist->leaf( 'li',
                              'The email address you entered either is not valid, or is of a form not presently supported. Please refer to the paragraph underneath the &quot;Email address&quot; field.'
                              );
        }
    } else {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The email address you entered either is not valid, or is of a form not presently supported. Please refer to the paragraph underneath the &quot;Email address&quot; field.'
                          );
    }
    $barcounter   = 0;
    $quotecounter = 0;
    str_replace('|', '', $EscapedUserName, $barcounter  );
    str_replace('"', '', $EscapedUserName, $quotecounter);
    $EscapedUserName        = htmlspecialchars($EscapedUserName);
    $EscapedPassword        = trim(@$_POST['Password']);
    $EscapedConfirmPassword = trim(@$_POST['ConfirmPassword']);
    $EscapedAnswer          = trim(@$_POST['Answer']);
    $EscapedConfirmAnswer   = trim(@$_POST['ConfirmAnswer']);
    $EscapedQuestion = sanitise_int( @$_POST['Question'],
                                     SANITISE_NO_FLAGS,
                                     0,
                                     19
                                     );
    if ( dbquery( DBQUERY_READ_RESULTSET,
                  'SELECT "UserID" FROM "User" WHERE "Name" = :name:',
                  'name' , $EscapedUserName
                  ) !== 'NONE' ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Someone else is already using that user name.'
                          );
    }
    if ( $EscapedEmail == '' ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'You must enter a working email address in order to register.'
                          );
    } else if ( dbquery( DBQUERY_READ_RESULTSET,
                         'SELECT "UserID" FROM "User" WHERE "Email" = :email:',
                         'email' , $EscapedEmail
                         ) !== 'NONE' ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Someone else is already using that email address.'
                          );
    }
    if ( $barcounter or $quotecounter ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'User name may not contain <tt>|</tt> or <tt>&quot;</tt>'
                          );
    }
    if ( mb_strlen($EscapedUserName, 'UTF-8') < 3 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'User name is too short.'
                          );
    }
    if ( mb_strlen($EscapedUserName, 'UTF-8') > 20 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'User name is too long.'
                          );
    }
    if ( strlen($EscapedPassword) < 3 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Password is too short.'
                          );
    }
    if ( strlen($EscapedPassword) > 20 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Password is too long.'
                          );
    }
    if ( strlen($EscapedAnswer) < 3 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Secret Answer is too short.'
                          );
    }
    if ( strlen($EscapedAnswer) > 20 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Secret Answer is too long.'
                          );
    }
    if ( strlen($EscapedEmail) > 50 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'Email is too long. Maximum 50 characters.'
                          );
    }
    if ( $EscapedPassword != $EscapedConfirmPassword ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The passwords in the two fields do not match. Please make sure that you type the same password in each field.'
                          );
    }
    if ( $EscapedAnswer != $EscapedConfirmAnswer ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The Secret Answers in the two fields do not match. Please make sure that you type the same Secret Answer in each field.'
                          );
    }
    if ( $_POST['Pronoun'] != 'He' and
         $_POST['Pronoun'] != 'She' and
         $_POST['Pronoun'] != 'It'
         ) {
        die($unexpectederrormessage);
    }
    if ( $_POST['Pronoun'] == 'He'  ) { $hetext  = ' selected'; }
    else                              { $hetext  = '';          }
    if ( $_POST['Pronoun'] == 'She' ) { $shetext = ' selected'; }
    else                              { $shetext = '';          }
    if ( $_POST['Pronoun'] == 'It'  ) { $ittext  = ' selected'; }
    else                              { $ittext  = '';          }
    $STimeZone = sanitise_int(@$_POST['TimeZone'], SANITISE_NO_FLAGS, 0, 48);
    $TZSelectedStrings[$STimeZone] = ' selected';
    for ($i=0; $i<20; $i++) {
        if ( $EscapedQuestion == $i ) { $QArray[$i] = ' selected'; }
        else                          { $QArray[$i] = '';          }
    }
    if ( @$_POST['EPrompt'] ) { $EPromptVal = 1; }
    else                      { $EPromptVal = 0; }
    if ( $errors ) {
        $ShowForm = true;
    }
} else {
    $errors    = false;
    $ShowForm  = true;
    $FirstShow = true;
    $QArray = array( '', '', '', '', '',
                     '', '', '', '', '',
                     '', '', '', '', '',
                     '', '', '', '', ''
                     );
    $hetext  = ' selected';
    $shetext = '';
    $ittext  = '';
}

if ( $ShowForm ) {
    $Question = array( 'What is your mother\'s maiden name?',
                       'What primary/elementary school did you go to?',
                       'What was the name of your first pet?',
                       'What is your favourite fruit or vegetable?',
                       'Who was your role model when you were growing up?',
                       'Which famous person from the past do you most admire?',
                       'Which famous living person do you most admire?',
                       'What is your favourite mathematical theorem?',
                       'What is the first board game you ever bought (excluding non-proprietary games like Chess)?',
                       'What is the name of the street that you grew up on?',
                       'What was your favourite holiday destination as a child?',
                       'What is the first great work of literature you ever read?',
                       'What is your all-time LEAST favourite television show?',
                       'What was the name of the first album you ever bought?',
                       'What is your all-time LEAST favourite German-style board game/Eurogame?',
                       'What is your favourite classic film?',
                       'What is your favourite Agricola Occupation or Improvement?',
                       'Who is your all-time LEAST favourite fictional character?',
                       'Who is your favourite character from The Simpsons?',
                       'What place would you most like to live when you retire?'
                       );
    $FormUserName = sanitise_str(@$_POST['UserName'], STR_GPC | STR_ESCAPE_HTML);
    $FormEmail    = sanitise_str(@$_POST['Email']   , STR_GPC | STR_ESCAPE_HTML);
    $EPromptText = ( $FirstShow or isset($_POST['EPrompt']) ) ?
                   ' checked' :
                   '';
    $mypage->title_body('New user registration');
    $mypage->leaf('h1', 'New user registration');
    if ( $errors ) {
        $mypage->opennode('ul');
        $mypage->append($errorlist);
        $mypage->closenode();
    }
    $mypage->opennode( 'form',
                       'action="newuser.php" method="POST"'
                       );
    $mypage->opennode( 'table',
                       'class="table_no_borders" style="text-align: left;"'
                       );
    $mypage->opennode('tr');
    $mypage->leaf( 'td',
                   'Name:',
                   'width=165 align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="text" name="UserName" size=20 maxlength=20 value="'.
                       $FormUserName.
                       '">'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Password:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="Password" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Confirm Password:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="ConfirmPassword" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Secret Question:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="Question"');
    for ($i=0; $i<20; $i++) {
        $mypage->leaf( 'option',
                       $Question[$i],
                       'value='.$i.$QArray[$i]
                       );
    }
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Secret Answer:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="Answer" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Confirm Secret Answer:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="ConfirmAnswer" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   'Your Secret Question and Secret Answer are used to allow you to regain access to your account if you forget your password. If you don\'t like this type of feature for whatever reason, then you are perfectly free to enter gibberish into these two fields (must be the same gibberish in each field). Obviously if you do this, though, you lose the right to complain if you do lose your password. Administrators will NOT give you access to accounts to which you have forgotten the password and the Secret Answer. (Note that you also need access to your email address, which you will enter below, for account recovery.)',
                   'class="font_serif" style="font-style: italic;"'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Email:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="text" name="Email" size=50 maxlength=50 value="'.
                       $FormEmail.
                       '">'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '<b><i>Please note:</i></b> if you find that you do not receive the account validation email, it might be because your email provider is marking the email as spam. Check your spam folder and if you find the message there, either mark it as "not spam" or add <b>automated@orderofthehammer.com</b> to your "trusted senders" list (or equivalent).'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '<b><i>Please note:</i></b> not all valid email addresses are supported at this time. However, <b>most users\' email addresses are highly likely to be usable.</b> The requirements for an address to be usable are as follows (most users need pay no attention to this): your email address needs to be a series of characters from the following list:<ul><li>the lowercase* ASCII letters (i.e. the lowercase unaccented Latin letters &quot;a&quot; to &quot;z&quot;)</li><li>the digits 0 to 9</li><li>the ASCII characters <tt>@ . ! $ &amp; * - = ^ ` | ~ # % &#39; + / ? _ { }</tt></li></ul>In addition, there must be precisely one @ character; the @ character and the &quot;dot&quot; (full stop/period) character may not appear at the very beginning or the very end of the address; and the &quot;dot&quot; character must not appear next to another &quot;dot&quot; character or the @ character. The length of your email address may not be greater than 50 characters.<br>* If you enter an address containing uppercase letters, they will be converted to lowercase ones.<br>** Non-ASCII characters will be stripped from the address.'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   '<input type="checkbox" name="EPrompt" value=1'.
                       $EPromptText.
                       '>',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   'Email me to let me know when it\'s my turn in a game I\'m playing'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Pronoun:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode('select', 'name="Pronoun"');
    $mypage->leaf('option', 'He' , 'value="He"'.$hetext  );
    $mypage->leaf('option', 'She', 'value="She"'.$shetext);
    $mypage->leaf('option', 'It' , 'value="It"'.$ittext  );
    $mypage->closenode(2); // select, td
    $mypage->next();
    $mypage->leaf( 'td',
                   'Time Zone:',
                   'align=right'
                   );
    $mypage->opennode('td');
    $mypage->opennode( 'select',
                       'name="TimeZone"'
                       );
    for ($i=0; $i<49; $i++) {
        $mypage->leaf( 'option',
                       $TZStrings[$i],
                       'value='.$i.$TZSelectedStrings[$i]
                       );
    }
    $mypage->closenode(); // select
    $mypage->text('(Please disregard Daylight Saving Time.)');
    $mypage->closenode(); // td
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf('td', '<input type="submit" value="Register">');
    $mypage->closenode(2); // tr, table
    $mypage->emptyleaf( 'input',
                        'type="hidden" name="FormSubmitted" value=1'
                        );
    $mypage->closenode(); // form
    $mypage->opennode('ul');
    $mypage->leaf( 'li',
                   'It is recommended to restrict user names to combinations of letters, numbers and spaces. Certain other text characters, such as hyphens and underscores, are also fine.'
                   );
    $mypage->leaf( 'li',
                   'Using some other characters may cause your name to be rejected, or it may cause odd behaviour (a username that is longer than it appears) due to the need to escape certain text characters for display on-screen.'
                   );
    $mypage->leaf( 'li',
                   'Your user name may not contain the vertical bar character | or the double quotation marks character ".'
                   );
    $mypage->leaf( 'li',
                   'Spaces at the beginning and end of your user name and password will be stripped.'
                   );
    $mypage->leaf( 'li',
                   'Your user name and password must both be between 3 and 20 characters (inclusive) in length.'
                   );
    $mypage->leaf( 'li',
                   'You will need to receive a validation email in order to complete registration, so do not leave the Email field blank or enter gibberish.'
                   );
    $mypage->leaf( 'li',
                   'Make sure you spell your user name correctly, as there is no option to change your user name later.'
                   );
    $mypage->closenode(); // ul
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
} else {
    $CharArray = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $thevstring = '';
    for ($i=0; $i<20; $i++) {
        $j = rand(0, 35);
        $thevstring .= $CharArray[$j];
    }
    $encryptedthevstring = crypt($thevstring, generateSalt());
    $EscapedPassword     = crypt($EscapedPassword, generateSalt());
    $EscapedAnswer       = crypt($EscapedAnswer, generateSalt());
    $NewUserID = dbquery( DBQUERY_INSERT_ID,
                          'INSERT INTO "User" ("Name", "Password", "SecretQuestion", "SecretAnswer", "Email", "EmailPrompt", "RegistrationDate", "LastLogin", "BecomesAccessible", "Pronoun", "TimeZone", "Language", "ScrambleKey") VALUES (:name:, :password:, :question:, :answer:, :email:, :emailprompt:, UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP(), :pronoun:, :timezone:, :language:, :scramblekey:)',
                          'name'        , $EscapedUserName     ,
                          'password'    , $EscapedPassword     ,
                          'question'    , $EscapedQuestion     ,
                          'answer'      , $EscapedAnswer       ,
                          'email'       , $EscapedEmail        ,
                          'emailprompt' , $EPromptVal          ,
                          'pronoun'     , $_POST['Pronoun']    ,
                          'timezone'    , $STimeZone           ,
                          'language'    , $Language            ,
                          'scramblekey' , $encryptedthevstring
                          );
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "RecentEventLog" ("EventType", "EventTime", "ExtraData") VALUES (\'User\', UTC_TIMESTAMP(), :idno:)',
             'idno' , $NewUserID
             );
    $body = '<p>In order to finish registering to play Brass, please click on the url on the next line, or copy and paste it into your browser\'s address bar.</p><p><a href="'.SITE_ADDRESS.'validate.php?UserID='.$NewUserID.'&amp;VString='.$thevstring.'">'.SITE_ADDRESS.'validate.php?UserID='.$NewUserID.'&amp;VString='.$thevstring.'</a></p>'.EMAIL_FOOTER;
    $emresult = send_email( 'Validation Email for Brass',
                            $body,
                            $EscapedEmail,
                            null
                            );
    $mypage->title_body('User successfully created');
    $mypage->leaf( 'p',
                   'User '.
                       $EscapedUserName.
                       ' successfully created.'
                   );
    if ( $emresult ) {
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
                       'But it seems there was a problem sending you a validation email. Please visit <a href="resendvalemail.php">this page</a> to reattempt sending the email.'
                       );
    }
}
$mypage->finish();

?>