<?php
require('_std-include.php');

$mypage = page::standard();
$mypage->title_body('Change Password and/or Secret Question / Secret Answer');
if ( @$_SESSION['LoggedIn'] ) {
    $UserID = sanitise_int($_SESSION['MyUserID']);
    $row = dbquery( DBQUERY_READ_SINGLEROW,
                    'SELECT "Password", "SecretQuestion" FROM "User" WHERE "UserID" = :user:',
                    'user' , $UserID
                    );
    if ( $row === 'NONE' ) { die($unexpectederrormessage); }
    $WhatChanges = false;
    $QArray = array( '', '', '', '', '',
                     '', '', '', '', '',
                     '', '', '', '', '',
                     '', '', '', '', ''
                     );
    if ( @$_POST['FormSubmitted'] ) {
        $SendDataPW  = false;
        $SendDataAns = false;
        $errors      = false;
        $errorlist   = fragment::blank();
        $EscapedOldPassword     = trim(@$_POST['OldPassword']);
        $EscapedPassword        = trim(@$_POST['Password']);
        $EscapedConfirmPassword = trim(@$_POST['ConfirmPassword']);
        $EscapedAnswer          = trim(@$_POST['Answer']);
        $EscapedConfirmAnswer   = trim(@$_POST['ConfirmAnswer']);
        $EscapedQuestion = sanitise_int( $EscapedQuestion,
                                         SANITISE_NO_FLAGS,
                                         0,
                                         19
                                         );
        if ( $row['Password'] != crypt($EscapedOldPassword, $row['Password']) ) {
            $errors = true;
            $errorlist->leaf( 'li',
                              'The old password was incorrect.'
                              );
        }
        if ( $EscapedPassword != '' ) {
            $SendDataPW = true;
            if ( strlen($EscapedPassword) > 20 ) {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'New password is too long. Maximum 20 characters.'
                                  );
            }
            if ( strlen($EscapedPassword) < 3 ) {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'New password is too short. Minimum 3 characters.'
                                  );
            }
            if ( $EscapedPassword != $EscapedConfirmPassword ) {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'The passwords in the two fields do not match.'
                                  );
            }
        }
        if ( $EscapedAnswer != '' ) {
            $SendDataAns = true;
            if ( strlen($EscapedAnswer) > 20 ) {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'New Secret Answer is too long. Maximum 20 characters.'
                                  );
            }
            if ( strlen($EscapedAnswer) < 3 ) {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'New Secret Answer is too short. Minimum 3 characters.'
                                  );
            }
            if ( $EscapedAnswer != $EscapedConfirmAnswer ) {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'The Secret Answers in the two fields do not match.'
                                  );
            }
        }
        if ( !$errors and
             ($SendDataPW or $SendDataAns)
             ) {
            if ( $SendDataPW and $SendDataAns ) {
                $Query = 'UPDATE "User" SET "Password" = :password:, "SecretAnswer" = :answer:, "SecretQuestion" = :question: WHERE "UserID" = :user:';
                $WhatChanges = 'Password and Secret Question / Secret Answer';
            } else if ( $SendDataPW ) {
                $Query = 'UPDATE "User" SET "Password" = :password: WHERE "UserID" = :user:';
                $WhatChanges = 'Password';
            } else {
                $Query = 'UPDATE "User" SET "SecretAnswer" = :answer:, "SecretQuestion" = :question: WHERE "UserID" = :user:';
                $WhatChanges = 'Secret Question / Secret Answer';
            }
            $EscapedPassword = crypt($EscapedPassword, generateSalt());
            $EscapedAnswer   = crypt($EscapedAnswer, generateSalt());
            $QueryResult = dbquery( DBQUERY_WRITE,
                                    $Query,
                                    'password' , $EscapedPassword      ,
                                    'answer'   , $EscapedAnswer        ,
                                    'question' , $EscapedQuestion      ,
                                    'user'     , $_SESSION['MyUserID']
                                    );
        } else if ( !$errors ) {
            $errors = true;
            $errorlist->leaf( 'li',
                              'Your old password was correct, but you have not entered a new password nor a new Secret Question / Secret Answer, so nothing has been changed.'
                              );
        }
        $QArray[$EscapedQuestion] = ' selected';
    } else {
        $errors = false;
        for ($i=0; $i<20; $i++) {
            if ( $row['SecretQuestion'] == $i ) {
                $QArray[$i] = ' selected';
            }
        }
    }
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
    $mypage->loginbox();
    if ( $errors ) {
        $mypage->leaf( 'p',
                       'Could not make the requested changes, for the following reasons:'
                       );
        $mypage->opennode('ul');
        $mypage->append($errorlist);
        $mypage->closenode();
    }
    if ( $WhatChanges !== false ) {
        $mypage->leaf( 'p',
                       $WhatChanges.' change successful',
                       'style="font-weight: bold;"'
                       );
    }
    $mypage->opennode( 'form',
                       'action="changepassword.php" method="POST"'
                       );
    $mypage->opennode( 'table',
                       'class="table_no_borders" style="text-align: left;"'
                       );
    $mypage->opennode('tr');
    $mypage->leaf( 'td',
                   'Old Password:',
                   'width=160 align=right'
                   );
    $mypage->leaf( 'td',
                   '<input type="password" name="OldPassword" size=20 maxlength=20>'
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
                   '(Leave the "New Password" and "Confirm New Password" fields blank if you do not want to change your password from what it currently is.)'
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
                       'value="'.$i.'"'.$QArray[$i]
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
                   '(Leave the "Secret Answer" and "Confirm Secret Answer" fields blank if you do not want to change your Secret Question and Secret Answer from what they currently are.)'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   'Your Secret Question and Secret Answer are used to allow you to regain access to your account if you forget your password. If you don\'t like this type of feature for whatever reason, then you are perfectly free to enter gibberish into these two fields (must be the same gibberish in each field). Obviously if you do this, though, you lose the right to complain if you do lose your password. Administrators will NOT give you access to accounts to which you have forgotten both the password and the Secret Answer. (Note that you also need access to your email address, which you can change on the User Details page, for account recovery.)',
                   'class="font_serif" style="font-style: italic;"'
                   );
    $mypage->next();
    $mypage->leaf('td', '');
    $mypage->leaf( 'td',
                   '<input type="submit" value="Submit">'
                   );
    $mypage->closenode(2); // tr, table
    $mypage->emptyleaf( 'input',
                        'type="hidden" name="FormSubmitted" value=1'
                        );
    $mypage->closenode(); // form
    $mypage->leaf( 'p',
                   'Click <a href="userdetails.php?UserID='.
                       $UserID.
                       '">here</a> to return to your User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
} else {
    $mypage->leaf( 'p',
                   'You must log in in order to change your password or Secret Question / Secret Answer. Please click <a href="index.php">here</a> to visit the main page.'
                   );
}
$mypage->finish();

?>