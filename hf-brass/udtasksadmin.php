<?php
function UDTasksAdmin() {
    global $Administrator,$cxn,$unexpectederrormessage,$writeerrormessage;
    if ( !isset($_POST['TheUserID']) ) {
        echo '<html><head><title>No User ID specified</title><body>Error: This page does not appear to have been called in the correct fashion; no User ID is specified in the _POST array.</body></html>';
        die('');
    }
    $EscapedUserID = (int)$_POST['TheUserID'];
    $EscapedNewUserName = clean(@$_POST['NewUserName']);
    $EscapedNewUserName = htmlspecialchars($EscapedNewUserName);
    $URow = getdata($cxn,1,'SELECT `Administrator` FROM `User` WHERE `UserID` = '.$EscapedUserID) or die($readerrormessage);
    if ( $URow === 'NONE' ) { die($unexpectederrormessage); }
    else if ( $URow['Administrator'] == 2 and $Administrator == 1 ) {
        $QueryResult = mysqli_query($cxn,'UPDATE `User` SET `Administrator` = 0, `Banned` = 1, `LockPS` = 1 WHERE `UserID` = '.$_SESSION['MyUserID']) or die($readerrormessage);
        $subject = 'An Administrator attempted to alter your account and was de-admin\'d and banned';
        $thetime = date('Y-m-d H:i:s',strtotime(now));
        $body = 'At '.
                $thetime.
                ', Administrator '.
                $_SESSION['MyUserName'].
                ' (User ID '.
                $_SESSION['MyUserID'].
                ') attempted to alter your account and was de-admin\'d and banned.';
        // send_email($subject,$body,???????????????????,null);
        die('lol i dont think so. owned scrub.');
    } else if ( $Administrator ) {
        if ( strlen($EscapedNewUserName) > 20 and $_POST['ChangeUsername'] ) {
            echo '<html><head><title>New user name too long</title><body>Error: New user name is too long.<p>Click <a href="index.php">here</a> to return to the Main Page.</body></html>';
            die();
        }
        if ( strlen($EscapedNewUserName) < 3 and $_POST['ChangeUsername'] ) {
            echo '<html><head><title>New user name too short</title><body>Error: New user name is too short.<p>Click <a href="index.php">here</a> to return to the Main Page.</body></html>';
            die();
        }
        if ( @$_POST['BanUser'] )   { $QueryResult = mysqli_query ($cxn,'UPDATE User SET Banned = 1 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST['UnbanUser'] ) { $QueryResult = mysqli_query ($cxn,'UPDATE User SET Banned = 0 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST['LockPS'] )    { $QueryResult = mysqli_query ($cxn,'UPDATE User SET LockPS = 1 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST['UnlockPS'] )  { $QueryResult = mysqli_query ($cxn,'UPDATE User SET LockPS = 0 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST['ClearPS'] )   { $QueryResult = mysqli_query ($cxn,'UPDATE User SET PersonalStatement = \'</i></b>\' WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
/*        if ( @$_POST['AllKick'] ) {
            $QueryResult = getdata ($cxn,0,'SELECT Game.*, UserR.UserID AS UserIDR, UserR.Name AS NameR, UserR.Pronoun AS PronounR, UserR.Email AS EmailR, UserR.EmailPrompt AS EmailPromptR, UserR.EmailPromptAgain AS EmailPromptAgainR, UserR.HasBeenEmailed AS HasBeenEmailedR, UserY.UserID AS UserIDY, UserY.Name AS NameY, UserY.Pronoun AS PronounY, UserY.Email AS EmailY, UserY.EmailPrompt AS EmailPromptY, UserY.EmailPromptAgain AS EmailPromptAgainY, UserY.HasBeenEmailed AS HasBeenEmailedY, UserG.UserID AS UserIDG, UserG.Name AS NameG, UserG.Pronoun AS PronounG, UserG.Email AS EmailG, UserG.EmailPrompt AS EmailPromptG, UserG.EmailPromptAgain AS EmailPromptAgainG, UserG.HasBeenEmailed AS HasBeenEmailedG, UserP.UserID AS UserIDP, UserP.Name AS NameP, UserP.Pronoun AS PronounP, UserP.Email AS EmailP, UserP.EmailPrompt AS EmailPromptP, UserP.EmailPromptAgain AS EmailPromptAgainP, UserP.HasBeenEmailed AS HasBeenEmailedP, UserF.UserID AS UserIDF, UserF.Name AS NameF, UserF.Pronoun AS PronounF, UserF.Email AS EmailF, UserF.EmailPrompt AS EmailPromptF, UserF.EmailPromptAgain AS EmailPromptAgainF, UserF.HasBeenEmailed AS HasBeenEmailedF FROM Game LEFT JOIN User AS UserR ON Game.UserR = UserR.UserID LEFT JOIN User AS UserY ON Game.UserY = UserY.UserID LEFT JOIN User AS UserG ON Game.UserG = UserG.UserID LEFT JOIN User AS UserP ON Game.UserP = UserP.UserID LEFT JOIN User AS UserF ON Game.UserF = UserF.UserID WHERE ( UserR = '.$EscapedUserID.' OR UserY = '.$EscapedUserID.' OR UserG = '.$EscapedUserID.' OR UserP = '.$EscapedUserID.' OR UserF = '.$EscapedUserID.' ) AND Game.GameStatus = \'In Progress\'');
            if ( $QueryResult != 'NONE' ) {
                require(HIDDEN_FILES_PATH.'getgamedata.php');
                require(HIDDEN_FILES_PATH.'kraftwerk.php');
                while ( $row = mysqli_fetch_assoc( $QueryResult ) ) {
                    $row[GameTicker] = '';
                    extract(providegamedata($row,0));
                    for ($i=0;$i<5;$i++) {
                        if ( $PlayerUserID[$i] == $EscapedUserID ) { $ThePlayerToKick = $i; }
                        if ( $ThePlayerToKick == $PlayerToMove ) { $Delayed = 0; }
                        else                                     { $Delayed = 1; }
                    }
                    KickPlayer($ThePlayerToKick,1,1,$Delayed);
                    dbformatgamedata();
                }
            }
        } This is really outdated and should be redone. */
        if ( @$_POST[AllRemove] ) {
            $QueryResult = mysqli_query($cxn,'UPDATE `Game` SET `GameStatus` = \'Cancelled\' WHERE `GameCreator` = '.$EscapedUserID.' AND GameStatus = \'Recruiting\'') or die($writeerrormessage);
            $QueryResult = getdata ($cxn,0,'SELECT GameID, PlayerExists, UserR, UserY, UserG, UserP, UserF FROM Game WHERE ( UserR = '.$EscapedUserID.' OR UserY = '.$EscapedUserID.' OR UserG = '.$EscapedUserID.' OR UserP = '.$EscapedUserID.' OR UserF = '.$EscapedUserID.' ) AND GameStatus = \'Recruiting\'');
            if ( $QueryResult != 'NONE' ) {
                while ( $row = mysqli_fetch_assoc( $QueryResult ) ) {
                    $PlayerExists = explode('|',$row[PlayerExists]);
                    if ( $row[UserR] == $EscapedUserID ) { $PlayerExists[0] = 0; $ColourLetter = 'R'; }
                    if ( $row[UserY] == $EscapedUserID ) { $PlayerExists[1] = 0; $ColourLetter = 'Y'; }
                    if ( $row[UserG] == $EscapedUserID ) { $PlayerExists[2] = 0; $ColourLetter = 'G'; }
                    if ( $row[UserP] == $EscapedUserID ) { $PlayerExists[3] = 0; $ColourLetter = 'P'; }
                    if ( $row[UserF] == $EscapedUserID ) { $PlayerExists[4] = 0; $ColourLetter = 'F'; }
                    $PlayerExists = implode('|',$PlayerExists);
                    $QueryResult = mysqli_query($cxn,'UPDATE Game SET PlayerExists = \''.$PlayerExists.'\', User'.$ColourLetter.' = NULL, CurrentPlayers = CurrentPlayers - 1 WHERE GameID = '.$row[GameID]) or die($writeerrormessage);
                }
            }
        }
        if ( @$_POST[ClearMsgs] ) {
            $QueryResult = mysqli_query ($cxn,'UPDATE Message SET DeletedByAdmin = 1 WHERE User = '.$EscapedUserID) or die($writeerrormessage);
            $QueryResult = mysqli_query ($cxn,'UPDATE Thread SET TitleDeletedByAdmin = 1 WHERE OriginalPoster = '.$EscapedUserID) or die($writeerrormessage);
            $QueryResult = mysqli_query ($cxn,'UPDATE Game SET GTitleDeletedByAdmin = 1 WHERE GameCreator = '.$EscapedUserID) or die($writeerrormessage);
        }
        if ( @$_POST[ClearEmail] )       { $QueryResult = mysqli_query ($cxn,'UPDATE User SET Email = \'\', EmailPrompt = 0 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST[DenyUserAccess] )   { $QueryResult = mysqli_query ($cxn,'UPDATE User SET DenyAccess = 1 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST[UndenyUserAccess] ) { $QueryResult = mysqli_query ($cxn,'UPDATE User SET DenyAccess = 0 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST[ChangeUsername] )   { $QueryResult = mysqli_query ($cxn,'UPDATE User SET Name = \''.$EscapedNewUserName.'\' WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST[MakeAdmin] )        { $QueryResult = mysqli_query ($cxn,'UPDATE User SET Administrator = 1 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST[UnmakeAdmin] )      { $QueryResult = mysqli_query ($cxn,'UPDATE User SET Administrator = 0 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        if ( @$_POST[ValidateUser] )     { $QueryResult = mysqli_query ($cxn,'UPDATE User SET UserValidated = 1 WHERE UserID = '.$EscapedUserID) or die($writeerrormessage); }
        echo "<html><head><title>Changes successful</title><script type=\"text/javascript\"><!--\nfunction delayer(){\nwindow.location =\"userdetails.php?UserID={$EscapedUserID}\"\n}\n//-->\n</script>\n</head><body onLoad=\"setTimeout('delayer()', 2000)\">Successfully executed changes. This page will redirect to the User Details page in 2 seconds. (If you do not have JavaScript enabled for this site, this will not happen; <a href=\"userdetails.php?UserID={$EscapedUserID}\">click here</a> instead to return to the User Details page.)</body></html>";
    } else {
        echo 'What\'s this? You are not an Administrator! <a href="index.php">Begone with you!</a></body></html>';
    }
}
?>