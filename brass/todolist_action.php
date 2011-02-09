<?php
define('TEST_MODE', true);
require('_std-include.php');
require(HIDDEN_FILES_PATH.'sanitise_str_fancy.php');

if ( !isset($_POST['FormSubmit']) or
     !isset($_POST['parentitem']) or
     !isset($_POST['description'])
     ) {
    die($unexpectederrormessage);
}

$Parent = sanitise_int($_POST['parentitem']);
$Description = sanitise_str_fancy( $_POST['description'],
                                   1,
                                   750,
                                   STR_GPC | STR_STRIP_TAB_AND_NEWLINE
                                   );

if ( $_POST['FormSubmit'] == 'Make changes' ) {
    if ( !isset($_POST['itemid']) or
         !isset($_POST['orderingnumber'])
         ) {
        die($unexpectederrormessage);
    }
    $Item = sanitise_int($_POST['itemid']);
    $OrderingNumber = sanitise_int( $_POST['orderingnumber'],
                                    SANITISE_NO_FLAGS,
                                    0,
                                    65535
                                    );
    if ( $Parent > 0 ) {
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "OrderingNumber" FROM "ToDoListSubItem" WHERE "SubItemNumber" = :item: AND "Item" = :parent:',
                       'item'   , $Item   ,
                       'parent' , $Parent
                       );
    } else {
        $QR = dbquery( DBQUERY_READ_RESULTSET,
                       'SELECT "OrderingNumber" FROM "ToDoListItem" WHERE "ItemID" = :item:',
                       'item'   , $Item
                       );
    }
    if ( $QR === 'NONE' ) {
        $mypage = page::standard();
        $mypage->title_body('Item not found');
        $mypage->leaf( 'p',
                       'Unable to find the specified item. Here is the item description you entered:'
                       );
        $mypage->leaf( 'textarea',
                       sanitise_str( $_POST['description'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     ),
                       'rows=8 cols=80'
                       );
        $mypage->finish();
    }
    if ( $Description[1] == 1 ) {
        $mypage = page::standard();
        $mypage->title_body('Item description too long');
        $mypage->leaf( 'p',
                       'The item description you entered is too long. Here is the item description you entered:'
                       );
        $mypage->leaf( 'textarea',
                       sanitise_str( $_POST['description'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     ),
                       'rows=8 cols=80'
                       );
        $mypage->finish();
    }
    if ( $Description[1] == -1 ) {
        if ( $Parent > 0 ) {
            dbquery( DBQUERY_WRITE,
                     'DELETE FROM "ToDoListSubItem" WHERE "SubItemNumber" = :item: AND "Item" = :parent:',
                     'item'   , $Item   ,
                     'parent' , $Parent
                     );
        } else {
            dbquery( DBQUERY_WRITE,
                     'DELETE FROM "ToDoListItem" WHERE "ItemID" = :item:',
                     'item' , $Item
                     );
        }
        page::redirect( 3,
                        false,
                        'Item successfully deleted.'
                        );
    }
    if ( $Parent > 0 ) {
        dbquery( DBQUERY_WRITE,
                 'UPDATE "ToDoListSubItem" SET "OrderingNumber" = :onumber:, "Description" = :desc: WHERE "SubItemNumber" = :item: AND "Item" = :parent:',
                 'onumber' , $OrderingNumber ,
                 'desc'    , $Description[0] ,
                 'item'    , $Item           ,
                 'parent'  , $Parent
                 );
    } else {
        dbquery( DBQUERY_WRITE,
                 'UPDATE "ToDoListItem" SET "OrderingNumber" = :onumber:, "Description" = :desc: WHERE "ItemID" = :item:',
                 'onumber' , $OrderingNumber ,
                 'desc'    , $Description[0] ,
                 'item'    , $Item
                 );
    }
    page::redirect( 3,
                    false,
                    'Item successfully modified.'
                    );
}

if ( @$_POST['orderingnumber'] ) {
    $OrderingNumber = sanitise_int( $_POST['orderingnumber'],
                                    SANITISE_NO_FLAGS,
                                    0
                                    );
} else if ( $Parent > 0 ) {
    $OrderingNumber = dbquery( DBQUERY_READ_INTEGER,
                               'SELECT IFNULL((SELECT MAX("OrderingNumber") FROM "ToDoListSubItem" WHERE "Item" = :parent:), 0) + 10 AS "NewOrderingNumber"',
                               'parent' , $Parent
                               );
} else {
    $OrderingNumber = dbquery( DBQUERY_READ_INTEGER,
                               'SELECT IFNULL((SELECT MAX("OrderingNumber") FROM "ToDoListItem"), 0) + 10 AS "NewOrderingNumber"'
                               );
}
if ( $OrderingNumber > 65535 ) { $OrderingNumber = 65535; }
if ( $Description[1] == 1 ) {
    $mypage = page::standard();
    $mypage->title_body('Item description too long');
    $mypage->leaf( 'p',
                   'The item description you entered is too long. Here is the item description you entered:'
                   );
    $mypage->leaf( 'textarea',
                   sanitise_str( $_POST['description'],
                                 STR_GPC |
                                     STR_ESCAPE_HTML |
                                     STR_STRIP_TAB_AND_NEWLINE
                                 ),
                   'rows=8 cols=80'
                   );
    $mypage->leaf( 'p',
                   'You can click <a href="todolist.php">here</a> to return to the To Do list, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
if ( $Description[1] == -1 ) {
    $mypage = page::standard();
    $mypage->title_body('Item description missing');
    $mypage->leaf( 'p',
                   'Your item description text is missing.'
                   );
    $mypage->leaf( 'p',
                   'You can click <a href="todolist.php">here</a> to return to the To Do list, or <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
if ( $Parent > 0 ) {
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   'SELECT "OrderingNumber" FROM "ToDoListItem" WHERE "ItemID" = :parent:',
                   'parent'   , $Parent
                   );
    if ( $QR === 'NONE' ) {
        $mypage = page::standard();
        $mypage->title_body('Parent item not found');
        $mypage->leaf( 'p',
                       'Unable to find the specified parent item. Here is the item description you entered:'
                       );
        $mypage->leaf( 'textarea',
                       sanitise_str( $_POST['description'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     ),
                       'rows=8 cols=80'
                       );
        $mypage->leaf( 'p',
                       'You can click <a href="todolist.php">here</a> to return to the To Do list, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    $Item = dbquery( DBQUERY_READ_INTEGER,
                     'SELECT IFNULL((SELECT MAX("SubItemNumber") FROM "ToDoListSubItem" WHERE "Item" = :parent:), -1) + 1',
                     'parent' , $Parent
                     );
    if ( $Item == 256 ) {
        $mypage = page::standard();
        $mypage->title_body('Too many subitems');
        $mypage->leaf( 'p',
                       'There are (or have been) too many subitems to the specified top-item. This script isn\'t sophisticated enough to reorganise the sub-item-numbers. You will need to upgrade the script, manually edit the database to re-organise the sub-item-numbers, or create a new top-item copying the current one and copy across the current sub-items (then delete the current top-item).'
                       );
        $mypage->leaf( 'p',
                       'Here is the item description you entered:'
                       );
        $mypage->leaf( 'textarea',
                       sanitise_str( $_POST['description'],
                                     STR_GPC |
                                         STR_ESCAPE_HTML |
                                         STR_STRIP_TAB_AND_NEWLINE
                                     ),
                       'rows=8 cols=80'
                       );
        $mypage->leaf( 'p',
                       'You can click <a href="todolist.php">here</a> to return to the To Do list, or <a href="index.php">here</a> to return to the Main Page.'
                       );
        $mypage->finish();
    }
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "ToDoListSubItem" ("SubItemNumber", "Item", "OrderingNumber", "Description") VALUES (:item:, :parent:, :onumber:, :desc:)',
             'onumber' , $OrderingNumber ,
             'desc'    , $Description[0] ,
             'parent'  , $Parent         ,
             'item'    , $Item
             );
} else {
    dbquery( DBQUERY_WRITE,
             'INSERT INTO "ToDoListItem" ("OrderingNumber", "Description") VALUES (:onumber:, :desc:)',
             'onumber' , $OrderingNumber ,
             'desc'    , $Description[0]
             );
}
page::redirect( 3,
                'todolist.php',
                'Item successfully added.'
                );

?>