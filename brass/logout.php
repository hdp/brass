<?php
require('_std-include.php');

if ( $_SESSION['LoggedIn'] ) {
    dbquery( DBQUERY_WRITE,
             'UPDATE "User" SET "HasBeenEmailed" = 0 WHERE "UserID" = :user:',
             'user' , $_SESSION['MyUserID']
             );
}

$_SESSION = array('LoggedIn' => 0);
session_destroy();

page::redirect( 3,
                'index.php',
                'Successfully logged out.'
                );

?>