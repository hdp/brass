<?php

if ( $_POST['TheUserID'] != $_SESSION['MyUserID'] ) {
    $mypage = page::standard();
    $mypage->title_body('Not logged in as this user');
    $mypage->leaf( 'p',
                   'You can only change your own settings, not those of other users. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$EscapedEmail = sanitise_str( @$_POST['Email'],
                              STR_GPC | STR_ESCAPE_HTML | STR_TO_LOWERCASE
                              );
$SPronoun = sanitise_enum( @$_POST['Pronoun'],
                           array('He','She','It')
                           );
$STimeLimitAUnits = sanitise_enum( @$_POST['TimeLimitAUnits'],
                                   array('minutes','hours','days')
                                   );
$STimeLimitBUnits = sanitise_enum( @$_POST['TimeLimitBUnits'],
                                   array('minutes','hours','days')
                                   );
switch ( $SPronoun ) {
    case 'He':  $_SESSION['MyGenderCode'] = 0; break;
    case 'She': $_SESSION['MyGenderCode'] = 1; break;
    case 'It':  $_SESSION['MyGenderCode'] = 2;
}
$EscapedStatement = sanitise_str_fancy( @$_POST['Statement'],
                                        1,
                                        50000,
                                        STR_GPC |
                                            STR_PERMIT_FORMATTING |
                                            STR_HANDLE_IMAGES |
                                            STR_PERMIT_ADMIN_HTML |
                                            STR_DISREGARD_GAME_STATUS
                                        );
$errors = false;
$errorlist = fragment::blank();
if ( $EscapedStatement[1] == 1 ) {
    $SetPSString = '';
    $errors = true;
    $errorlist->opennode('li');
    $errorlist->text('That personal statement is too long. The limit is around 50,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before). Here is the text you entered:');
    $errorlist->emptyleaf('br');
    $errorlist->emptyleaf('br');
    $errorlist->leaf( 'textarea',
                      sanitise_str( $_POST['Statement'],
                                    STR_GPC | STR_ESCAPE_HTML
                                    ),
                      'cols=80 rows=20'
                      );
    $errorlist->closenode();
} else if ( $EscapedStatement[1] == -1 ) {
    $SetPSString = 'PersonalStatement = NULL, ';
} else {
    $SetPSString = 'PersonalStatement = :ps:, ';
}
$SetEmailString = '';
$EscapedEmail = sanitise_str( @$_POST['Email'],
                              STR_GPC |
                                  STR_ENSURE_ASCII |
                                  STR_TO_LOWERCASE
                              );
if ( strlen($EscapedEmail) > 50 ) {
    $errors = true;
    $errorlist->leaf( 'li',
                      'Failed to change email address: New address is too long.'
                      );
} else if ( $EscapedEmail == '' ) {
    $SetEmailString = 'Email = NULL, ';
} else {
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
        } else {
            $QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                                    'SELECT "UserID" FROM "User" WHERE "UserID" <> :userid: AND "Email" = :email:',
                                    'userid' , $_SESSION['MyUserID'] ,
                                    'email'  , $EscapedEmail
                                    );
            if ( $QueryResult === 'NONE' ) {
                $SetEmailString = '"Email" = :email:, ';
            } else {
                $errors = true;
                $errorlist->leaf( 'li',
                                  'Failed to change email address: Another user is already using that email address.'
                                  );
            }
        }
    } else {
        $errors = true;
        $errorlist->leaf( 'li',
                          'The email address you entered either is not valid, or is of a form not presently supported. Please refer to the paragraph underneath the &quot;Email address&quot; field.'
                          );
    }
}
$SPublicWatch            = sanitise_bool( @$_POST['PublicWatch']            );
$SEmailPrompt            = sanitise_bool( @$_POST['EmailPrompt']            );
$SEmailPromptAgain       = sanitise_bool( @$_POST['EmailPromptAgain']       );
$SEmailAtEnd             = sanitise_bool( @$_POST['EmailAtEnd']             );
$SAlwaysShowCanals       = sanitise_bool( @$_POST['AlwaysShowCanals']       );
$SAlwaysShowRails        = sanitise_bool( @$_POST['AlwaysShowRails']        );
$SShowInaccessiblePlaces = sanitise_bool( @$_POST['ShowInaccessiblePlaces'] );
$SShowVirtualConnection  = sanitise_bool( @$_POST['ShowVirtualConnection']  );
$SBSIncomeTrack          = sanitise_bool( @$_POST['BSIncomeTrack']          );
$SAlertDisplay           = sanitise_bool( @$_POST['AlertDisplay']           );
$SReverseTicker          = sanitise_bool( @$_POST['ReverseTicker']          );
$SShowAsBlue             = sanitise_bool( @$_POST['ShowAsBlue']             );
$SCheckForMoves          = sanitise_bool( @$_POST['CheckForMoves']          );
$SAllowContact           = sanitise_bool( @$_POST['AllowContact']           );
$SDefaultFriendly        = sanitise_bool( @$_POST['DefaultFriendly']        );
$SDefaultNoSC            = sanitise_bool( @$_POST['DefaultNoSC']            );
$SDefaultRVC             = sanitise_bool( @$_POST['DefaultRVC']             );
$SDefaultORAW            = sanitise_bool( @$_POST['DefaultORAW']            );
$SAlwaysHideMsgs         = sanitise_bool( @$_POST['AlwaysHideMsgs']         );
$SReverseMessages        = sanitise_bool( @$_POST['ReverseMessages']        );
$SNoSwapCards            = sanitise_bool( @$_POST['NoSwapCards']            );
$SColouredArrows         = sanitise_bool( @$_POST['ColouredArrows']         );
$SCanalProj              = sanitise_bool( @$_POST['CanalProj']              );
$SAutoSort               = sanitise_bool( @$_POST['AutoSort']               );
$SCompactBoard           = sanitise_bool( @$_POST['CompactBoard']           );
$SEmptyStackEmptySet     = sanitise_bool( @$_POST['EmptyStackEmptySet']     );
$SProjIncludeMoney = sanitise_int( @$_POST['ProjIncludeMoney']       , INT_OUT_OF_RANGE_SET_LIMIT , 0 ,   2 );
$SCurrSymbLocation = sanitise_int( @$_POST['CurrencySymbolLocation'] , INT_OUT_OF_RANGE_SET_LIMIT , 0 ,   2 );
$SPrefBehaviourAtB = sanitise_int( @$_POST['PrefBatB']               , INT_OUT_OF_RANGE_SET_LIMIT , 0 ,   3 );
$SBlinkRate        = sanitise_int( @$_POST['BlinkRate']              , INT_OUT_OF_RANGE_SET_LIMIT , 0 ,   3 );
$STimeZone         = sanitise_int( @$_POST['TimeZone']               , INT_OUT_OF_RANGE_SET_LIMIT , 0 ,  48 );
$STimeLimitA       = sanitise_int( @$_POST['TimeLimitANumber']       , INT_OUT_OF_RANGE_SET_LIMIT , 1 , 120 );
$STimeLimitB       = sanitise_int( @$_POST['TimeLimitBNumber']       , INT_OUT_OF_RANGE_SET_LIMIT , 1 , 120 );
$SLanguage = sanitise_int( @$_POST['Language'],
                           INT_OUT_OF_RANGE_SET_DEFAULT,
                           0,
                           NUM_FOREIGN_LANGUAGES,
                           0
                           );
$SPrefCurrSymbol = sanitise_int( @$_POST['PreferredCurrencySymbol'],
                                 INT_OUT_OF_RANGE_SET_DEFAULT,
                                 0,
                                 count($Currencies) - 1,
                                 0
                                 );
if ( $STimeLimitAUnits == 'hours' )     { $STimeLimitA *=   60; }
else if ( $STimeLimitAUnits == 'days' ) { $STimeLimitA *= 1440; }
if ( $STimeLimitBUnits == 'hours' )     { $STimeLimitB *=   60; }
else if ( $STimeLimitBUnits == 'days' ) { $STimeLimitB *= 1440; }
if ( $STimeLimitA <    15 ) { $STimeLimitA =    15; }
if ( $STimeLimitA > 57600 ) { $STimeLimitA = 57600; }
if ( $STimeLimitB <    30 ) { $STimeLimitB =    30; }
if ( $STimeLimitB > 57600 ) { $STimeLimitB = 57600; }
dbquery( DBQUERY_WRITE,
         'UPDATE "User" SET '.$SetEmailString.$SetPSString.'"Pronoun" = :pronoun:, "EmailPrompt" = :emailprompt:, "EmailPromptAgain" = :emailpromptagain:, "AlwaysShowCanals" = :alwaysshowcanals:, "AlwaysShowRails" = :alwaysshowrails:, "ShowInaccessiblePlaces" = :showinaccessibleplaces:, "ShowVirtualConnection" = :showvirtualconnection:, "BSIncomeTrack" = :bsincometrack:, "AlertDisplay" = :alertdisplay:, "ReverseTicker" = :reverseticker:, "CheckForMoves" = :checkformoves:, "AllowContact" = :allowcontact:, "DefaultFriendly" = :defaultfriendly:, "DefaultNoSC" = :defaultnosc:, "DefaultRVC" = :defaultrvc:, "DefaultORAW" = :defaultoraw:, "DefaultTimeLimitA" = :defaulttimelimita:, "DefaultTimeLimitB" = :defaulttimelimitb:, "ShowAsBlue" = :showasblue:, "AlwaysHideMsgs" = :alwayshidemsgs:, "ReverseMessages" = :reversemessages:, "NoSwapCards" = :noswapcards:, "ColouredArrows" = :colouredarrows:, "BlinkRate" = :blinkrate:, "EmailAtEnd" = :emailatend:, "ProjIncludeMoney" = :projincludemoney:, "CanalProj" = :canalproj:, "TimeZone" = :timezone:, "AutoSort" = :autosort:, "CompactBoard" = :compactboard:, "EmptyStackEmptySet" = :emptystackemptyset:, "Language" = :language:, "PublicWatch" = :publicwatch:, "PreferredDoAtB" = :preferreddoatb:, "PreferredCurrencySymbol" = :preferredcurrencysymbol:, "CurrencySymbolLocation" = :currencysymbollocation: WHERE "UserID" = :userid:',
         'pronoun'                 , $SPronoun                ,
         'email'                   , $EscapedEmail            ,
         'ps'                      , $EscapedStatement[0]     ,
         'emailprompt'             , $SEmailPrompt            ,
         'emailpromptagain'        , $SEmailPromptAgain       ,
         'alwaysshowcanals'        , $SAlwaysShowCanals       ,
         'alwaysshowrails'         , $SAlwaysShowRails        ,
         'showinaccessibleplaces'  , $SShowInaccessiblePlaces ,
         'showvirtualconnection'   , $SShowVirtualConnection  ,
         'bsincometrack'           , $SBSIncomeTrack          ,
         'alertdisplay'            , $SAlertDisplay           ,
         'reverseticker'           , $SReverseTicker          ,
         'checkformoves'           , $SCheckForMoves          ,
         'allowcontact'            , $SAllowContact           ,
         'defaultfriendly'         , $SDefaultFriendly        ,
         'defaultnosc'             , $SDefaultNoSC            ,
         'defaultrvc'              , $SDefaultRVC             ,
         'defaultoraw'             , $SDefaultORAW            ,
         'defaulttimelimita'       , $STimeLimitA             ,
         'defaulttimelimitb'       , $STimeLimitB             ,
         'showasblue'              , $SShowAsBlue             ,
         'alwayshidemsgs'          , $SAlwaysHideMsgs         ,
         'reversemessages'         , $SReverseMessages        ,
         'noswapcards'             , $SNoSwapCards            ,
         'colouredarrows'          , $SColouredArrows         ,
         'blinkrate'               , $SBlinkRate              ,
         'emailatend'              , $SEmailAtEnd             ,
         'projincludemoney'        , $SProjIncludeMoney       ,
         'canalproj'               , $SCanalProj              ,
         'timezone'                , $STimeZone               ,
         'autosort'                , $SAutoSort               ,
         'compactboard'            , $SCompactBoard           ,
         'emptystackemptyset'      , $SEmptyStackEmptySet     ,
         'language'                , $SLanguage               ,
         'publicwatch'             , $SPublicWatch            ,
         'preferreddoatb'          , $SPrefBehaviourAtB       ,
         'preferredcurrencysymbol' , $SPrefCurrSymbol         ,
         'currencysymbollocation'  , $SCurrSymbLocation       ,
         'userid'                  , $_SESSION['MyUserID']
         );
if ( $errors ) {
    $mypage = page::standard();
    $mypage->title_body('Some changes were unsuccessful');
    $mypage->leaf('h3', 'Some changes were unsuccessful');
    $mypage->opennode('ul');
    $mypage->append($errorlist);
    $mypage->closenode();
    $mypage->leaf( 'p',
                   'The specified changes - or a subset of them - could not be made, as shown above. Click <a href="userdetails.php?UserID='.
                       $_SESSION['MyUserID'].
                       '">here</a> to return to your User Details page.'
                   );
    $mypage->finish();
} else {
    page::redirect( 3,
                    'userdetails.php?UserID='.$_SESSION['MyUserID'],
                    'Changes made successfully.'
                    );
}

?>