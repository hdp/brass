<?php
$SpecialFlag  = true;
$NoLoginStuff = true;
require('_std-include.php');

$mypage = page::standard();
if ( @$_SESSION['LoggedIn'] ) {
    $_SESSION['AllowUse'] = 0;
    $mypage->title_body('Logged in');
    $mypage->leaf( 'p',
                   'You cannot access this page while logged in. Please either <a href="logout.php">log out</a> first, or return to the <a href="index.php">Main Page</a>.'
                   );
    $mypage->finish();
} else if ( isset($_POST['FormSubmit']) ) {
    if ( !isset($_SESSION['AllowUseUID']) ) {
        die($unexpectederrormessage);
    }
    $errors    = false;
    $errorlist = fragment::blank();
    $EscapedUserID = sanitise_int(@$_POST['UserID']);
    if ( $EscapedUserID != $_SESSION['AllowUseUID'] ) {
        die($unexpectederrormessage);
    }
    $EscapedAnswer          = trim(@$_POST['Answer']);
    $EscapedPassword        = trim(@$_POST['Password']);
    $EscapedConfirmPassword = trim(@$_POST['ConfirmPassword']);
    $row = dbquery( DBQUERY_READ_SINGLEROW,
                    'SELECT "SecretQuestion", "SecretAnswer", "UserValidated" FROM "User" WHERE "UserID" = :user:',
                    'user' , $EscapedUserID
                    );
    if ( $row === 'NONE' or !$row['UserValidated'] ) {
        die($unexpectederrormessage);
    }
    if ( $row['SecretAnswer'] != crypt($EscapedAnswer, $row['SecretAnswer']) ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The Secret Answer you entered was not correct.'
                          );
    }
    if ( strlen($EscapedPassword) < 3 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The new password you entered is too short.'
                          );
    }
    if ( strlen($EscapedPassword) > 20 ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The new password you entered is too long.'
                          );
    }
    if ( $EscapedPassword != $EscapedConfirmPassword ) {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The passwords in the two fields do not match.'
                          );
    }
    if ( !$errors ) {
        $CharArray = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $thevstring = '';
        for ($i=0; $i<20; $i++) {
            $j = rand(0, 35);
            $thevstring .= $CharArray[$j];
        }
        $encryptedthevstring = crypt($thevstring, generateSalt());
        $EscapedPassword     = crypt($EscapedPassword, generateSalt());
        dbquery( DBQUERY_WRITE,
                 'UPDATE "User" SET "ScrambleKey" = :scramblekey:, "Password" = :password: WHERE "UserID" = :user:',
                 'scramblekey' , $encryptedthevstring ,
                 'password'    , $EscapedPassword     ,
                 'user'        , $EscapedUserID
                 );
        $_SESSION['AllowUse'] = 0;
        $mypage->title_body('Password successfully changed');
        $mypage->leaf( 'p',
                       'Your password has been successfully changed. Please click <a href="index.php">here</a> to go to the main page.'
                       );
        $mypage->finish();
    }
} else if ( !isset($_GET['UserID']) or !isset($_GET['VString']) ) {
    $_SESSION['AllowUse'] = 0;
    $mypage->title_body('Invalid URL');
    $mypage->leaf( 'p',
                   'The URL you used to get here is not valid. If you copied and pasted the URL from the email, you may not have copied the entire line. Please try again.'
                   );
    $mypage->finish();
} else {
    $EscapedUserID = sanitise_int($_GET['UserID']);
    $row = dbquery( DBQUERY_READ_SINGLEROW,
                    'SELECT "ScrambleKey", "SecretQuestion", "UserValidated" FROM "User" WHERE "UserID" = :user:',
                    'user' , $EscapedUserID
                    );
    if ( $row === 'NONE' or !$row['UserValidated'] ) {
        $_SESSION['AllowUse'] = 0;
        $mypage->title_body('Error');
        $mypage->leaf( 'p',
                       'It looks like you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    } else {
        if ( $row['ScrambleKey'] == crypt(trim($_GET['VString']), $row['ScrambleKey']) ) {
            $_SESSION['AllowUse'] = 1;
            $_SESSION['AllowUseUID'] = $EscapedUserID;
            $errors = false;
        } else {
            $_SESSION['AllowUse'] = 0;
            $mypage->title_body('Incorrect validation string');
            $mypage->leaf( 'p',
                           'The validation string in the URL is incorrect. If you copied and pasted the line from the email, you may not have copied the entire line. Please try again.'
                           );
            $mypage->finish();
        }
    }
}

if ( $_SESSION['AllowUse'] ) {
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
    $mypage->title_body('Account recovery');
    $mypage->leaf('h1', 'Account recovery');
    if ( $errors ) {
        $mypage->opennode('ul');
        $mypage->append($errorlist);
        $mypage->closenode();
    }
    $mypage->leaf( 'p',
                   'Please answer your secret question and then specify what you want your new password to be.'
                   );
    $mypage->opennode('p');
    $mypage->text('Secret Question:');
    $mypage->leaf('b', $Question[$row['SecretQuestion']]);
    $mypage->closenode(); // p
    $mypage->opennode( 'form',
                       'action="recoveraccountb.php" method="POST"'
                       );
    $mypage->opennode( 'table',
                       'class="table_no_borders" style="text-align: left;"'
                       );
    $mypage->opennode('tr');
    $mypage->leaf( 'td',
                   'Your Answer:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="Answer" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'New Password:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="Password" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf( 'td',
                   'Confirm New Password:',
                   'align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="ConfirmPassword" size=20 maxlength=20>'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '<input type="submit" name="FormSubmit" value="Submit">'
                   );
    $mypage->closenode(2); // tr, table
    $mypage->emptyleaf( 'input',
                        'type="hidden" name="UserID" value='.$EscapedUserID
                        );
    $mypage->closenode(); // form
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

?>