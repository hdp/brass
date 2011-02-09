<?php
require('_std-include.php');

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You are not logged in. Please log in and then return to this page. You can return to the Main Page by clicking <a href="index.php">here</a>.'
                   );
    $mypage->finish();
}
if ( $Administrator < 2 ) {
    $mypage->title_body('Not authorised');
    $mypage->leaf( 'p',
                   'You are not authorised to make use of this page. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}

if ( !isset($_GET['Parent']) or
     !isset($_GET['Item'])
     ) {
    myerror( $unexpectederrormessage,
             'Expected variables missing from GET request'
             );
}
$Parent = sanitise_int($_GET['Parent']);
$Item   = sanitise_int($_GET['Item']  );
if ( $Parent > 0 ) {
    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "OrderingNumber", "Description" FROM "ToDoListSubItem" WHERE "SubItemNumber" = :item: AND "Item" = :parent:',
                   'parent' , $Parent ,
                   'item'   , $Item
                   );
    if ( $QR === 'NONE' ) {
        myerror( $unexpectederrormessage,
                 'Item with the given sub-item number and parent item ID number was not found'
                 );
    }
} else {
    $QR = dbquery( DBQUERY_READ_SINGLEROW,
                   'SELECT "OrderingNumber", "Description" FROM "ToDoListItem" WHERE "ItemID" = :item:',
                   'item' , $Item
                   );
    if ( $QR === 'NONE' ) {
        myerror( $unexpectederrormessage,
                 'Item with the given item ID number was not found'
                 );
    }
}

$mypage->title_body('Edit item');
$mypage->leaf('h3', 'Edit item');
$mypage->opennode( 'form',
                   'action="todolist_action.php" method="POST"'
                   );
$mypage->opennode( 'table',
                   'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                   );
$mypage->opennode('tr');
$mypage->leaf( 'td',
               'Ordering number:',
               'align=right'
               );
$mypage->leaf( 'td',
               '<input type="text" name="orderingnumber" size=14 maxlength=6 value='.
               $QR['OrderingNumber'].
               '>'
               );
$mypage->next();
$mypage->leaf( 'td',
               'Item description:',
               'align=right'
               );
$mypage->leaf( 'td',
               '<textarea name="description" rows=8 cols=70>'.
               htmlspecialchars($QR['Description']).
               '</textarea>'
               );
$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Make changes">'
               );
$mypage->closenode(2); // tr, table
$mypage->emptyleaf( 'input',
                    'type="hidden" name="parentitem" value="'.$Parent.'"'
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" name="itemid" value="'.$Item.'"'
                    );
$mypage->finish(); // form, body, html

?>