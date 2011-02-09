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

function roman ($x) {
    $x = (int)$x;
    if ( $x <  0 ) { return '';       }
    if ( $x > 19 ) { return '&bull;'; }
    $roman_numerals = array( 'i', 'ii', 'iii', 'iv', 'v',
                             'vi', 'vii', 'viii', 'ix', 'x',
                             'xi', 'xii', 'xiii', 'xiv', 'xv',
                             'xvi', 'xvii', 'xviii', 'xix', 'xx'
                             );
    return '('.$roman_numerals[$x].')';
}

if ( @$_GET['renumber'] ) {
    dbquery( DBQUERY_WRITE,
             'CALL "RenumberToDoListItems"(10)'
             );
    page::redirect( 3,
                    'todolist.php',
                    'Successfully renumbered list items.'
                    );
}

$QR = dbquery( DBQUERY_READ_RESULTSET,
               'SELECT "ToDoListItem"."ItemID", "ToDoListItem"."OrderingNumber", "ToDoListItem"."Description", "SubQueryA"."Item" FROM "ToDoListItem" LEFT JOIN (SELECT DISTINCT "Item" FROM "ToDoListSubItem") AS "SubQueryA" ON "ToDoListItem"."ItemID" = "SubQueryA"."Item" ORDER BY "ToDoListItem"."OrderingNumber"'
               );

if ( @$_GET['printable'] ) {
    $mypage->title_body('To Do list - printable');
    $mypage->leaf('h3', 'To Do list - printable');
    if ( $QR === 'NONE' ) {
        $mypage->leaf('p', 'There are no To Do list items to display.');
    } else {
        $mypage->opennode('ul');
        while ( $row = db_fetch_assoc($QR) ) {
            $mypage->leaf( 'li',
                           $row['Description'],
                           'style="margin-top: 1px;"'
                           );
            if ( !is_null($row['Item']) ) {
                $QRX = dbquery( DBQUERY_READ_RESULTSET,
                                'SELECT "Description" FROM "ToDoListSubItem" WHERE "Item" = :item: ORDER BY "OrderingNumber"',
                                'item' , $row['ItemID']
                                );
                if ( $QRX !== 'NONE' ) {
                    $mypage->opennode( 'table',
                                       'class="table_no_borders" style="text-align: right;"'
                                       );
                    $i = 0;
                    while ( $rowx = db_fetch_assoc($QRX) ) {
                        $mypage->opennode('tr', 'style="height: 1em;"');
                        $mypage->leaf('td', roman($i), 'width=30 class="font_serif" style="vertical-align: top; margin-top: 0px; margin-bottom: 0px; padding-top: 0px; padding-bottom: 0px;"');
                        $mypage->leaf( 'td',
                                       $rowx['Description'],
                                       'align=left class="font_serif" style="margin-top: 0px; margin-bottom: 0px; padding-top: 0px; padding-bottom: 0px; padding-left: 2px;"'
                                       );
                        $mypage->closenode(); // tr
                        $i++;
                    }
                    $mypage->closenode(); // ol
                }
            }
        }
        $mypage->closenode(); // ul
    }
    $mypage->finish();
}

$mypage->script('popup.js');
$mypage->title_body('To Do list');
$mypage->loginbox();
$mypage->leaf('h1', 'To Do list');
if ( $QR === 'NONE' ) {
    $mypage->leaf('p', 'There are no To Do list items to display.');
} else {
    $mypage->opennode('table', 'class="table_extra_horizontal_padding"');
    $mypage->opennode('thead');
    $mypage->opennode('tr');
    $mypage->leaf('th', 'Ordering', 'width=60');
    $mypage->leaf('th', 'ID #', 'width=30');
    $mypage->leaf('th', 'Item description', 'colspan=2');
    $mypage->leaf('th', '', 'width=40');
    $mypage->closenode(2); // tr, thead
    $mypage->opennode('tbody');
    while ( $row = db_fetch_assoc($QR) ) {
        $mypage->opennode('tr');
        $mypage->leaf( 'td',
                       $row['OrderingNumber'],
                       'style="font-weight: bold;"'
                       );
        $mypage->leaf('td', $row['ItemID']);
        $mypage->leaf( 'td',
                       $row['Description'],
                       'colspan=2 align=left class="font_serif" '
                       );
        $mypage->leaf( 'td',
                       '<a href="todolist_edit.php?Parent=0&amp;Item='.
                           $row['ItemID'].
                           '" onClick="return popup(this,\'translatepopup'.
                           $row['ItemID'].
                           '\',700,500,\'yes\')">Edit</a>'
                       );
        if ( !is_null($row['Item']) ) {
            $QRX = dbquery( DBQUERY_READ_RESULTSET,
                            'SELECT "SubItemNumber", "Description", "OrderingNumber" FROM "ToDoListSubItem" WHERE "Item" = :item: ORDER BY "OrderingNumber"',
                            'item' , $row['ItemID']
                            );
            if ( $QRX !== 'NONE' ) {
                while ( $rowx = db_fetch_assoc($QRX) ) {
                    $mypage->next(); // tr
                    $mypage->leaf( 'td',
                                   '',
                                   'colspan=2 class="mygame"'
                                   );
                    $mypage->leaf( 'td',
                                   $rowx['OrderingNumber'],
                                   'width=60 style="font-weight: bold;"'
                                   );
                    $mypage->leaf( 'td',
                                   $rowx['Description'],
                                   'align=left class="font_serif"'
                                   );
                    $mypage->leaf( 'td',
                                   '<a href="todolist_edit.php?Parent='.
                                       $row['ItemID'].
                                       '&amp;Item='.
                                       $rowx['SubItemNumber'].
                                       '" onClick="return popup(this,\'todolistpopup'.
                                       $row['ItemID'].
                                       '-'.
                                       $rowx['SubItemNumber'].
                                       '\',700,500,\'yes\')">Edit</a>'
                                   );
                }
            }
        }
        $mypage->closenode(); // tr
    }
    $mypage->closenode(2); // tbody, table
}
$mypage->leaf( 'p',
               '<a href="todolist.php?printable=1">Printable version</a>'
               );
$mypage->leaf( 'p',
               '<a href="todolist.php?renumber=1">Renumber items</a>'
               );
$mypage->leaf('h3', 'Add a new item or sub-item');
$mypage->opennode( 'form',
                   'action="todolist_action.php" method="POST"'
                   );
$mypage->opennode( 'table',
                   'class="table_no_borders table_extra_horizontal_padding" style="text-align: left;"'
                   );
$mypage->opennode('tr');
$mypage->leaf( 'td',
               'Parent item ID #:<br>(If top-level then leave blank)',
               'align=right'
               );
$mypage->leaf( 'td',
               '<input type="text" name="parentitem" size=14 maxlength=6>'
               );
$mypage->next();
$mypage->leaf( 'td',
               'Ordering number:<br>(optional)',
               'align=right'
               );
$mypage->leaf( 'td',
               '<input type="text" name="orderingnumber" size=14 maxlength=6>'
               );
$mypage->next();
$mypage->leaf( 'td',
               'Item description:',
               'align=right'
               );
$mypage->leaf( 'td',
               '<textarea name="description" rows=8 cols=80></textarea>'
               );
$mypage->next();
$mypage->leaf('td', '');
$mypage->leaf( 'td',
               '<input type="submit" name="FormSubmit" value="Add item">'
               );
$mypage->closenode(3); // tr, table, form
$mypage->leaf( 'p',
               'You can return to the Main Page by clicking <a href="index.php">here</a>.'
               );
$mypage->finish();

?>