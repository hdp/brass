<?php
require('_std-include.php');

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You must be logged in to view this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
} else if ( !isset($_GET['UserID']) ) {
    $mypage->title_body('Error');
    $mypage->leaf( 'p',
                   'It looks as though you have been sent to the wrong page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

$EscapedUserID = (int)$_GET['UserID'];
$therow = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "Name", "Pronoun", "UserValidated", "PublicWatch" FROM "User" WHERE "UserID" = :user:',
                   'user' , $EscapedUserID
                   );
if ( $therow === 'NONE' or
     ( !$therow['UserValidated'] and !$Administrator )
     ) {
    $mypage->title_body('No such user');
    $mypage->leaf( 'p',
                   'There is no user with that user ID number. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
if ( !$therow['UserValidated'] ) {
    $mypage->title_body('User not validated');
    $mypage->leaf( 'p',
                   'The user with that user ID number is not validated. Click <a href="userdetails.php?UserID='.
                       $EscapedUserID.
                       '">here</a> to visit this user\'s User Details page, or <a href="index.php">here</a> to return to the Main Page.'
                   );
}

switch ( $therow['Pronoun'] ) {
    case 'He':  $lowercasepronoun = 'he';  break;
    case 'She': $lowercasepronoun = 'she'; break;
    default:    $lowercasepronoun = 'it';  break;
}
get_translation_module(19);
require(HIDDEN_FILES_PATH.'gamelistdisplayu.php');
require(HIDDEN_FILES_PATH.'gamelistdisplayuf.php');
$pagetitle = str_replace( '\username',
                          $therow['Name'],
                          transtext('ugPageTitle')
                          );
$mypage->title_body($pagetitle);
$mypage->loginbox(false);
$mypage->leaf('h1', $pagetitle);
$mypage->leaf( 'p',
               '<a href="userdetails.php?UserID='.
                   $EscapedUserID.
                   '">'.
                   transtext('ugLkUserDetails').
                   '</a>'
               );
$mypage->leaf('h3', transtext('_ugInProgress'));
gamelistdisplayup($mypage, $EscapedUserID, $therow['Name']);
if ( $therow['PublicWatch'] or $Administrator or $_SESSION['MyUserID'] == $EscapedUserID ) {
    $WatchedGamesHeader = transtext('_ugWatching');
    if ( !$therow['PublicWatch'] ) {
        $WatchedGamesHeader .= ( $_SESSION['MyUserID'] == $EscapedUserID ) ?
                                   // "(NB. You have barred other users from seeing this list)"
                               ' <span style="font-weight: normal;">'.
                                   transtext('_ugPrivWatchlist').
                                   '</span>' :
                               ' <span style="font-weight: normal;">(This list has been made invisible to non-admins)</span>';
    }
    $mypage->leaf('h3', $WatchedGamesHeader);
    gamelistdisplayuw($mypage, $EscapedUserID, 1);
}
$mypage->leaf('h3', transtext('_ugRR'));
gamelistdisplayux($mypage, $EscapedUserID, $therow['Name']);
$mypage->leaf('h3', transtext('_ugRecruiting'));
gamelistdisplayur($mypage, $EscapedUserID, $therow['Name']);
$mypage->leaf('h3', transtext('_ugFinished'));
gamelistdisplayuf($mypage, $EscapedUserID, $therow['Name'], $lowercasepronoun, true, 0, 20, null);
$mypage->leaf('p', 'Click <a href="index.php">here</a> to return to the Main Page.');
$mypage->finish();

?>