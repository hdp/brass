<?php

$CPublicWatch      = sanitise_bool( $therow['PublicWatch']            , BOOL_CHECKED );
$CEmailPrompt      = sanitise_bool( $therow['EmailPrompt']            , BOOL_CHECKED );
$CEmailPromptAgain = sanitise_bool( $therow['EmailPromptAgain']       , BOOL_CHECKED );
$CEmailAtEnd       = sanitise_bool( $therow['EmailAtEnd']             , BOOL_CHECKED );
$CAlwaysShowCanals = sanitise_bool( $therow['AlwaysShowCanals']       , BOOL_CHECKED );
$CAlwaysShowRails  = sanitise_bool( $therow['AlwaysShowRails']        , BOOL_CHECKED );
$CShowInacPlaces   = sanitise_bool( $therow['ShowInaccessiblePlaces'] , BOOL_CHECKED );
$CShowVConnection  = sanitise_bool( $therow['ShowVirtualConnection']  , BOOL_CHECKED );
$CBSIncomeTrack    = sanitise_bool( $therow['BSIncomeTrack']          , BOOL_CHECKED );
$CAlertDisplay     = sanitise_bool( $therow['AlertDisplay']           , BOOL_CHECKED );
$CReverseTicker    = sanitise_bool( $therow['ReverseTicker']          , BOOL_CHECKED );
$CShowAsBlue       = sanitise_bool( $therow['ShowAsBlue']             , BOOL_CHECKED );
$CCheckForMoves    = sanitise_bool( $therow['CheckForMoves']          , BOOL_CHECKED );
$CAllowContact     = sanitise_bool( $therow['AllowContact']           , BOOL_CHECKED );
$CDefaultFriendly  = sanitise_bool( $therow['DefaultFriendly']        , BOOL_CHECKED );
$CDefaultNoSC      = sanitise_bool( $therow['DefaultNoSC']            , BOOL_CHECKED );
$CDefaultRVC       = sanitise_bool( $therow['DefaultRVC']             , BOOL_CHECKED );
$CDefaultORAW      = sanitise_bool( $therow['DefaultORAW']            , BOOL_CHECKED );
$CAlwaysHideMsgs   = sanitise_bool( $therow['AlwaysHideMsgs']         , BOOL_CHECKED );
$CReverseMessages  = sanitise_bool( $therow['ReverseMessages']        , BOOL_CHECKED );
$CNoSwapCards      = sanitise_bool( $therow['NoSwapCards']            , BOOL_CHECKED );
$CColouredArrows   = sanitise_bool( $therow['ColouredArrows']         , BOOL_CHECKED );
$CCanalProj        = sanitise_bool( $therow['CanalProj']              , BOOL_CHECKED );
$CAutoSort         = sanitise_bool( $therow['AutoSort']               , BOOL_CHECKED );
$CCompactBoard     = sanitise_bool( $therow['CompactBoard']           , BOOL_CHECKED );
$CEmptySetSymbol   = sanitise_bool( $therow['EmptyStackEmptySet']     , BOOL_CHECKED );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$FormTimeLimitAMins  = '';
$FormTimeLimitAHours = '';
$FormTimeLimitADays  = '';
$FormTimeLimitBMins  = '';
$FormTimeLimitBHours = '';
$FormTimeLimitBDays  = '';

if ( $therow['DefaultTimeLimitA'] % 1440 == 0 ) {
    $FormTimeLimitA = $therow['DefaultTimeLimitA'] / 1440;
    $FormTimeLimitADays = ' selected';
} else if ( $therow['DefaultTimeLimitA'] % 60 == 0 ) {
    $FormTimeLimitA = $therow['DefaultTimeLimitA'] / 60;
    $FormTimeLimitAHours = ' selected';
} else {
    $FormTimeLimitA = $therow['DefaultTimeLimitA'];
    $FormTimeLimitAMins = ' selected';
}
if ( $therow['DefaultTimeLimitB'] % 1440 == 0 ) {
    $FormTimeLimitB = $therow['DefaultTimeLimitB'] / 1440;
    $FormTimeLimitBDays = ' selected';
} else if ( $therow['DefaultTimeLimitB'] % 60 == 0 ) {
    $FormTimeLimitB = $therow['DefaultTimeLimitB'] / 60;
    $FormTimeLimitBHours = ' selected';
} else {
    $FormTimeLimitB = $therow['DefaultTimeLimitB'];
    $FormTimeLimitBMins = ' selected';
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$hetext  = '';
$shetext = '';
$ittext  = '';
switch ( $therow['Pronoun'] ) {
    case 'He':  $hetext  = ' selected'; break;
    case 'She': $shetext = ' selected'; break;
    case 'It':  $ittext  = ' selected'; break;
}

$downsizetext     = '';
$aborttext        = '';
$kickdownsizetext = '';
$kickaborttext    = '';
switch ( $therow['PreferredDoAtB'] ) {
    case 0: $downsizetext     = ' selected'; break;
    case 1: $aborttext        = ' selected'; break;
    case 2: $kickdownsizetext = ' selected'; break;
    case 3: $kickaborttext    = ' selected'; break;
}

$ProjIMSelectedText = array('', '', '');
$ProjIMSelectedText[$therow['ProjIncludeMoney']] = ' selected';

$CSLOptionsSelected = array('', '', '');
$CSLOptionsSelected[$therow['CurrencySymbolLocation']] = ' selected';

$BRText = array('', '', '', '');
$BRText[$therow['BlinkRate']] = ' selected';

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$mymatch = array();
if ( is_null($therow['PersonalStatement']) ) {
    $TruncPS = '';
} else if ( preg_match( '/\\A([01]+;[0-9,]+:)/',
                        $therow['PersonalStatement'],
                        $mymatch
                        )
            ) {
    $TruncPS = substr($therow['PersonalStatement'], strlen($mymatch[0]));
    $mymatch = explode(';', substr($mymatch[0], 0, -1));
    $types   = str_split($mymatch[0]);
    $offsets = explode(',', $mymatch[1]);
    if ( count($types) != count($offsets) ) {
        $types   = array(0);
        $offsets = array(0);
    }
    if ( $types[count($types)-1] === '1' ) {
        $TruncPS .= '[/code]';
    }
    for ($i=count($types)-1; $i>0; $i--) {
        $middle_component = ( $types[$i-1] === '1' ) ?
                            "[/code]\n\n" :
                            "\n\n";
        if ( $types[$i] === '1' ) {
            $middle_component .= '[code]';
        }
        $TruncPS = substr($TruncPS, 0, $offsets[$i]).
                   $middle_component.
                   substr($TruncPS, $offsets[$i]);
    }
    if ( $types[0] === '1' ) {
        $TruncPS = '[code]'.$realstatement;
    }
} else {
    $TruncPS = $therow['PersonalStatement'];
}
$my_matches = 0;
str_replace(array('<', '>'), '', $TruncPS, $my_matches);
if ( $my_matches ) {
    // Administrators can put arbitrary HTML in their personal statements.
    // If there are unescaped tags in the statement, then they must be
    // escaped for the editing box.
    $TruncPS = htmlspecialchars($TruncPS, ENT_COMPAT, 'UTF-8');
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

get_translation_module(25);
$mypage->opennode('form', 'action="userdetails.php" method="POST"');
$mypage->leaf('h2', transtext('optHeading'));
    // "You may make the following changes to your settings:"
$mypage->leaf( 'p',
               '<a href="changepassword.php">'.
                   transtext('optLkChangePW').
                   '</a> '.
                   transtext('optChgPWProviso')
                       // "(must be done separately from all other changes)"
               );
$mypage->leaf( 'p',
               '<b><i>Please note:</i></b> Clicking any of the "Make Changes" buttons below will cause <b><i>all</i></b> of the changes you have made to take effect, <b><i>not</i></b> just those that appear in the group of settings immediately above the button clicked.'
               );
$mypage->opennode( 'table',
                   'class="table_no_borders font_serif" style="text-align: left;"'
                   );

$mypage->opennode('tr');
$mypage->leaf( 'td',
               transtext('optEmailAddress'),
               'width=90 align=right'
               );
$mypage->leaf( 'td',
               '<input type="text" name="Email" size=50 maxlength=50 value="'.
                   $therow['Email'].
                   '">'
               );
$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<b><i>Please note:</i></b> if, after you change your email address, you find that you are no longer receiving notification emails, it might be because your email provider is marking the emails as spam. Check your spam folder and if you find the messages there, either mark them as "not spam" or add <b>automated@orderofthehammer.com</b> to your "trusted senders" list (or equivalent).'
               );
$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<b><i>Please note:</i></b> not all valid email addresses are supported at this time. However, <b>most users\' email addresses are highly likely to be usable.</b> The requirements for an address to be usable are as follows (most users need pay no attention to this): your email address needs to be a series of characters from the following list:<ul><li>the lowercase* ASCII letters (i.e. the lowercase unaccented Latin letters &quot;a&quot; to &quot;z&quot;)</li><li>the digits 0 to 9</li><li>the ASCII characters <tt>@ . ! $ &amp; * - = ^ ` | ~ # % &#39; + / ? _ { }</tt></li></ul>In addition, there must be precisely one @ character; the @ character and the &quot;dot&quot; (full stop/period) character may not appear at the very beginning or the very end of the address; and the &quot;dot&quot; character must not appear next to another &quot;dot&quot; character or the @ character. The length of your email address may not be greater than 50 characters.<br>* If you enter an address containing uppercase letters, they will be converted to lowercase ones.<br>** Non-ASCII characters will be stripped from the address.'
               );

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Make Changes">'
               );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

// "Settings controlling when you will be emailed"

$mypage->next('style="height: 40px; vertical-align: middle;"');
$mypage->leaf( 'td',
               transtext('optShEmails'),
               'colspan=2 style="font-weight: bold;"'
               );

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="EmailPrompt" value=1'.
                   $CEmailPrompt.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optEmailOnTurn'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="EmailPromptAgain" value=1'.
                   $CEmailPromptAgain.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optEmailAlways'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="EmailAtEnd" value=1'.
                   $CEmailAtEnd.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optEmailAtEnd'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="AllowContact" value=1'.
                   $CAllowContact.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optAllowContact'));
    // "Allow users to send you email"

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Make Changes">'
               );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

// "Settings related to your profile"

$mypage->next('style="height: 40px; vertical-align: middle;"');
$mypage->leaf( 'td',
               transtext('optShProfile'),
               'colspan=2 style="font-weight: bold;"'
               );

$QR = dbquery( DBQUERY_READ_RESULTSET,
               'SELECT "LanguageNameInLanguage" FROM "TranslationLanguage"'
               );
if ( $QR === 'NONE' ) { die($unexpectederrormessage); }
$i = 0;
$mypage->next();
$mypage->leaf('td', transtext('optLanguage'), 'align=right');
$mypage->opennode('td');
$mypage->opennode('select', 'name="Language"');
$mypage->leaf( 'option',
               'English',
               'value=0'.
                   ( ( $therow['Language'] == 0 ) ? ' selected' : '' )
               );
while ( $row = db_fetch_assoc($QR) ) {
    $i++;
    $mypage->leaf( 'option',
                   $row['LanguageNameInLanguage'],
                   'value='.$i.
                       ( ( $therow['Language'] == $i ) ? ' selected' : '' )
                   );
}
$mypage->closenode();
$mypage->text(transtext('optLangProviso'));
$mypage->closenode();

$mypage->next();
$mypage->leaf('td', transtext('optPronoun'), 'align=right');
$mypage->opennode('td');
$mypage->opennode('select', 'name="Pronoun"');
$mypage->leaf('option', 'He' , 'value="He"'.$hetext  );
$mypage->leaf('option', 'She', 'value="She"'.$shetext);
$mypage->leaf('option', 'It' , 'value="It"'.$ittext  );
$mypage->closenode(2);

$TZStrings[0] = 'Not Saying';
$mypage->next();
$mypage->leaf('td', 'Time Zone:', 'align=right');
$mypage->opennode('td');
$mypage->opennode('select', 'name="TimeZone"');
for ($i=0; $i<49; $i++) {
    $mypage->leaf( 'option',
                   $TZStrings[$i],
                   'value='.$i.
                       ( ( $therow['TimeZone'] == $i ) ? ' selected' : '' )
                   );
}
$mypage->closenode();
$mypage->text(transtext('optDisregardDST'));
$mypage->closenode();

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="PublicWatch" value=1'.
                   $CPublicWatch.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optPrivWatchlist'));
    // Allow other users to see the games I'm watching

$mypage->next();
$mypage->leaf('td', transtext('optPStatement'), 'align=right');
$mypage->leaf( 'td',
               '<textarea cols=100 rows=14 name="Statement">'.
                   $TruncPS.
                   '</textarea>'
               );

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               'The available formatting options are detailed <a href="http://orderofthehammer.com/credits.htm#formatting">here</a>. The limit is around 50,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before).'
               );

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Make Changes">'
               );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

// "Default settings for games you create"

$mypage->next('style="height: 40px; vertical-align: middle;"');
$mypage->leaf( 'td',
               transtext('optShDefaults'),
               'colspan=2 style="font-weight: bold;"'
               );

$mypage->next();
$mypage->leaf('td', '');
$mypage->opennode('td');
$mypage->text(transtext('optDefaultTLA'));
$mypage->opennode('select', 'name="TimeLimitANumber"');
for ($i=1; $i<121; $i++) {
    $mypage->leaf( 'option',
                   $i,
                   'value='.$i.
                       ( ( $FormTimeLimitA == $i ) ? ' selected' : '' )
                   );
}
$mypage->closenode();
$mypage->opennode('select', 'name="TimeLimitAUnits"');
$mypage->leaf( 'option',
               transtext('_timeMinutePl'),
               'value="minutes"'.$FormTimeLimitAMins
               );
$mypage->leaf( 'option',
               transtext('_timeHourPl'),
               'value="hours"'.$FormTimeLimitAHours
               );
$mypage->leaf( 'option',
               transtext('_timeDayPl'),
               'value="days"'.$FormTimeLimitADays
               );
$mypage->closenode();
$mypage->leaf('i', transtext('_TLAProviso'));
$mypage->closenode();

$mypage->next();
$mypage->leaf('td', '');
$mypage->opennode('td');
$mypage->text(transtext('optDefaultTLB'));
$mypage->opennode('select', 'name="TimeLimitBNumber"');
for ($i=1; $i<121; $i++) {
    $mypage->leaf( 'option',
                   $i,
                   'value='.$i.
                       ( ( $FormTimeLimitB == $i ) ? ' selected' : '' )
                   );
}
$mypage->closenode();
$mypage->opennode('select', 'name="TimeLimitBUnits"');
$mypage->leaf( 'option',
               transtext('_timeMinutePl'),
               'value="minutes"'.$FormTimeLimitBMins
               );
$mypage->leaf( 'option',
               transtext('_timeHourPl'),
               'value="hours"'.$FormTimeLimitBHours
               );
$mypage->leaf( 'option',
               transtext('_timeDayPl'),
               'value="days"'.$FormTimeLimitBDays
               );
$mypage->closenode();
$mypage->leaf('i', transtext('_TLBProviso'));
$mypage->closenode();

$mypage->next();
$mypage->leaf('td', '');
$mypage->opennode('td');
$mypage->text(transtext('opt_TLBBehaviour'));
$mypage->opennode('select', 'name="PrefBatB"');
$mypage->leaf( 'option',
               transtext('_tlbbDownsz'),
               'value=0'.$downsizetext
               );
$mypage->leaf( 'option',
               transtext('_tlbbAbort'),
               'value=1'.$aborttext
               );
$mypage->leaf( 'option',
               transtext('_tlbbKickDownsz'),
               'value=2'.$kickdownsizetext
               );
$mypage->leaf( 'option',
               transtext('_tlbbKickAbort'),
               'value=3'.$kickaborttext
               );
$mypage->closenode(2);

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="DefaultFriendly" value=1'.
                   $CDefaultFriendly.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optDefaultFrdly'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="DefaultNoSC" value=1'.
                   $CDefaultNoSC.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optDefaultNoSC'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="DefaultRVC" value=1'.
                   $CDefaultRVC.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optDefaultRevVC'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="DefaultORAW" value=1'.
                   $CDefaultORAW.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optDefaultMCOR'));

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Make Changes">'
               );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

// "Settings controlling the appearance of the board page"

$mypage->next('style="height: 40px; vertical-align: middle;"');
$mypage->leaf( 'td',
               transtext('optShBoardPage'),
               'colspan=2 style="font-weight: bold;"'
               );

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="CompactBoard" value=1'.
                   $CCompactBoard.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optCompactBoard'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="BSIncomeTrack" value=1'.
                   $CBSIncomeTrack.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optVarIncomeTrk'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="AlwaysShowCanals" value=1'.
                   $CAlwaysShowCanals.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optAlwaysCanals'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="AlwaysShowRails" value=1'.
                   $CAlwaysShowRails.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optAlwaysRails'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="ShowInaccessiblePlaces" value=1'.
                   $CShowInacPlaces.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optSeeInac'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="ShowVirtualConnection" value=1'.
                   $CShowVConnection.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optShowVC'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="ShowAsBlue" value=1'.
                   $CShowAsBlue.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optShowMeAsBlue'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="CanalProj" value=1'.
                   $CCanalProj.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optCanalProjVPs'));

$mypage->next();
$mypage->opennode('td', 'colspan=2');
$mypage->text(transtext('optProjInclMoney'));
$mypage->opennode('select', 'name="ProjIncludeMoney"');
$mypage->leaf( 'option',
               transtext('^Yes'),
               'value=0'.$ProjIMSelectedText[0]
               );
$mypage->leaf( 'option',
               transtext('^No'),
               'value=1'.$ProjIMSelectedText[1]
               );
$mypage->leaf( 'option',
               transtext('optYesIfFinished'),
               'value=2'.$ProjIMSelectedText[2]
               );
$mypage->closenode(2);

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="EmptyStackEmptySet" value=1'.
                   $CEmptySetSymbol.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optEmptySymbol'));

$mypage->next();
$mypage->leaf('td', '');
$mypage->opennode('td');
$mypage->text(transtext('optCurrency'));
$mypage->opennode('select', 'name="PreferredCurrencySymbol"');
for ($i=0; $i<count($Currencies); $i++) {
    $mypage->leaf( 'option',
                   $Currencies[$i],
                   'value='.$i.
                       ( ( $therow['PreferredCurrencySymbol'] == $i ) ?
                         ' selected' :
                         ''
                       )
                   );
}
$mypage->closenode(2);

$mypage->next();
$mypage->leaf('td', '');
$mypage->opennode('td');
$mypage->text(transtext('optCurrencyLocn'));
$mypage->opennode('select', 'name="CurrencySymbolLocation"');
$mypage->leaf( 'option',
               transtext('optAptToCurrency'),
               'value=0'.$CSLOptionsSelected[0]
               );
$mypage->leaf( 'option',
               transtext('optBeforeNumber'),
               'value=1'.$CSLOptionsSelected[1]
               );
$mypage->leaf( 'option',
               transtext('optAfterNumber'),
               'value=2'.$CSLOptionsSelected[2]
               );
$mypage->closenode(2);

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="NoSwapCards" value=1'.
                   $CNoSwapCards.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optNoSwapCards'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="AlertDisplay" value=1'.
                   $CAlertDisplay.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optDisplayTips'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="CheckForMoves" value=1'.
                   $CCheckForMoves.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optCheckForMoves'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="ColouredArrows" value=1'.
                   $CColouredArrows.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optArrowColours'));

$mypage->next();
$mypage->leaf('td', '');
$mypage->opennode('td');
$mypage->text(transtext('optBlinkRate'));
$mypage->opennode('select');
$mypage->leaf( 'option',
               transtext('optBlinkNormal'),
               'value=0'.$BRText[0]
               );
$mypage->leaf( 'option',
               transtext('optBlinkSlow'),
               'value=1'.$BRText[1]
               );
$mypage->leaf( 'option',
               transtext('optBlinkVSlow'),
               'value=2'.$BRText[2]
               );
$mypage->leaf( 'option',
               transtext('optBlinkVVSlow'),
               'value=3'.$BRText[3]
               );
$mypage->closenode(2);

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="ReverseTicker" value=1'.
                   $CReverseTicker.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optReverseTicker'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="AlwaysHideMsgs" value=1'.
                   $CAlwaysHideMsgs.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optAutoHideThrd'));

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Make Changes">'
               );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

// "Miscellaneous settings"

$mypage->next('style="height: 40px; vertical-align: middle;"');
$mypage->leaf( 'td',
               transtext('optShMisc'),
               'colspan=2 style="font-weight: bold;"'
               );

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="AutoSort" value=1'.
                   $CAutoSort.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optAutoSortCards'));

$mypage->next();
$mypage->leaf( 'td',
               '<input type="checkbox" name="ReverseMessages" value=1'.
                   $CReverseMessages.
                   '>',
               'align=right'
               );
$mypage->leaf('td', transtext('optReverseMsgs'));

$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Make Changes">'
               );

$mypage->closenode(2); // tr, table
$mypage->emptyleaf( 'input',
                    'type="hidden" name="TheUserID" value="'.$EscapedUserID.'"'
                    );
$mypage->closenode(); // form

?>