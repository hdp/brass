<?php
require('_std-include.php');

$mypage = page::standard();
if ( !$_SESSION['LoggedIn'] ) {
    $mypage->title_body('Not logged in');
    $mypage->leaf( 'p',
                   'You must be logged in in order to see your emails. Please click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
$months = array( '',
                 'January'   , 'February' , 'March'    , 'April'    ,
                 'May'       , 'June'     , 'July'     , 'August'   ,
                 'September' , 'October'  , 'November' , 'December'
                 );
$Page = sanitise_int(@$_GET['Page'], INT_TOO_LOW_SET_MIN, 1);
$StartPoint = 20 * ($Page-1);

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$message_type = sanitise_int( @$_GET['emailtype'],
                              INT_OUT_OF_RANGE_SET_LIMIT,
                              0,
                              3
                              );
if ( !$Administrator and $message_type == 3 ) {
    $message_type = 0;
}
$message_type_selected = array('', '', '', '');
$message_type_selected[$message_type] = ' selected';
if ( $message_type == 3 ) {
    $EmailLimits = dbquery( DBQUERY_READ_SINGLEROW,
                            'SELECT MIN("DateSent") AS "FirstEmail", MAX("DateSent") AS "LastEmail" FROM "Email"'
                            );
} else {
    $EmailLimits = dbquery( DBQUERY_READ_SINGLEROW,
                            'SELECT MIN("DateSent") AS "FirstEmail", MAX("DateSent") AS "LastEmail" FROM "Email" WHERE "Recipient" = :userid: OR "Sender" = :userid:',
                            'userid' , $_SESSION['MyUserID']
                            );
}

if ( is_null($EmailLimits['FirstEmail']) ) {
    $mypage->title_body('No emails to display');
    $mypage->loginbox();
    if ( $message_type == 3 ) {
        $mypage->leaf( 'p',
                       'There are no emails to display. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
    } else {
        $mypage->leaf( 'p',
                       'You have neither sent nor received any emails, so this page does not have any function for you. Please click <a href="index.php">here</a> to return to the Main Page.'
                       );
    }
    $mypage->finish();
}
$FirstEmail = strtotime($EmailLimits['FirstEmail']);
$LastEmail  = strtotime($EmailLimits['LastEmail']);
$FirstEmailYear  = (int)date('Y', $FirstEmail);
$FirstEmailMonth = (int)date('n', $FirstEmail);
$LastEmailYear   = (int)date('Y', $LastEmail);
$LastEmailMonth  = (int)date('n', $LastEmail);
$FirstEmail = 12 * $FirstEmailYear + $FirstEmailMonth;
$LastEmail  = 12 * $LastEmailYear  + $LastEmailMonth;

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$current_year = (int)date('Y');
if ( isset($_GET['ys']) and isset($_GET['ms']) ) {
    $StartYear = sanitise_int( $_GET['ys'],
                               INT_OUT_OF_RANGE_SET_LIMIT,
                               2008,
                               $current_year + 1
                               );
    $StartMonth = sanitise_int( $_GET['ms'],
                                INT_OUT_OF_RANGE_SET_LIMIT,
                                1,
                                12
                                );
} else {
    $StartYear  = $current_year;
    $StartMonth = (int)date('n');
    if ( $StartMonth > 3 ) {
        $StartMonth -= 3;
    } else {
        $StartYear--;
        $StartMonth += 9;
    }
}
if ( isset($_GET['ye']) and isset($_GET['me']) ) {
    $EndYear = sanitise_int( $_GET['ye'],
                             INT_OUT_OF_RANGE_SET_LIMIT,
                             2008,
                             $current_year + 1
                             );
    $EndMonth = sanitise_int( $_GET['me'],
                              INT_OUT_OF_RANGE_SET_LIMIT,
                              1,
                              12
                              );
} else {
    $EndYear  = $current_year;
    $EndMonth = (int)date('n');
}
$Start = 12 * $StartYear + $StartMonth;
$End   = 12 * $EndYear   + $EndMonth;

if ( $Start < $FirstEmail ) {
    $StartYear  = $FirstEmailYear;
    $StartMonth = $FirstEmailMonth;
    $Start      = $FirstEmail;
}
if ( $Start > $LastEmail ) {
    $StartYear  = $LastEmailYear;
    $StartMonth = $LastEmailMonth;
    $Start      = $LastEmail;
}
if ( $End < $FirstEmail ) {
    $EndYear  = $FirstEmailYear;
    $EndMonth = $FirstEmailMonth;
    $End      = $FirstEmail;
}
if ( $End > $LastEmail ) {
    $EndYear  = $LastEmailYear;
    $EndMonth = $LastEmailMonth;
    $End      = $LastEmail;
}
if ( $Start > $End ) {
    $swap        = $StartYear;
    $StartYear  = $EndYear;
    $EndYear    = $swap;
    $swap        = $StartMonth;
    $StartMonth = $EndMonth;
    $EndMonth   = $swap;
    $swap        = $Start;
    $Start       = $End;
    $End         = $swap;
}

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$days = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
if ( $EndYear % 4 == 0 and
     ( $EndYear % 100 or $EndYear % 400 == 0 )
     ) {
    $days[2] = 29;
}
$last_day = $days[$EndMonth];
if ( $StartMonth < 10 ) {
    $SQL_Start_Month = '0'.$StartMonth;
} else {
    $SQL_Start_Month = $StartMonth;
}
if ( $EndMonth   < 10 ) {
    $SQL_End_Month   = '0'.$EndMonth;
} else {
    $SQL_End_Month = $EndMonth;
}
$SQL_Start = $StartYear.'-'.
             $SQL_Start_Month.
             '-01 00:00:00';
$SQL_End = $EndYear.'-'.
           $SQL_End_Month.'-'.
           $last_day.
           ' 23:59:59';

if ( $Start == $End ) {
    $timedescription = 'during '.$months[$StartMonth].
                       ' '.$StartYear;
} else if ( $StartYear == $EndYear and
            $StartMonth == 1 and
            $EndMonth == 12
            ) {
    $timedescription = 'during '.$StartYear;
} else if ( $StartYear == $EndYear ) {
    $timedescription = 'from '.$months[$StartMonth].
                       ' to '.$months[$EndMonth].
                       ' '.$EndYear;
} else {
    $timedescription = 'from '.$months[$StartMonth].
                       ' '.$StartYear.
                       ' to '.$months[$EndMonth].
                       ' '.$EndYear;
}
switch ( $message_type ) {
    case 0:
        $title = 'Emails sent and received '.$timedescription;
        $query = 'SELECT SQL_CALC_FOUND_ROWS "Email"."EmailID", "Email"."Sender", "UserS"."Name" AS "SenderName", "Email"."Recipient", "UserR"."Name" AS "RecipientName", "Email"."DateSent", "Email"."EmailText" FROM "Email" LEFT JOIN "User" AS "UserS" ON "Email"."Sender" = "UserS"."UserID" LEFT JOIN "User" AS "UserR" ON "Email"."Recipient" = "UserR"."UserID" WHERE ("Email"."Recipient" = :userid: OR "Email"."Sender" = :userid:) AND "Email"."DateSent" BETWEEN :start: AND :end: ORDER BY "Email"."DateSent" DESC LIMIT :startpoint:, 20';
        break;
    case 1:
        $title = 'Emails received '.$timedescription;
        $query = 'SELECT SQL_CALC_FOUND_ROWS "Email"."EmailID", "Email"."Sender", "User"."Name", "Email"."DateSent", "Email"."EmailText" FROM "Email" LEFT JOIN "User" ON "Email"."Sender" = "User"."UserID" WHERE "Email"."Recipient" = :userid: AND "Email"."DateSent" BETWEEN :start: AND :end: ORDER BY "Email"."DateSent" DESC LIMIT :startpoint:, 20';
        break;
    case 2:
        $title = 'Emails sent '.$timedescription;
        $query = 'SELECT SQL_CALC_FOUND_ROWS "Email"."EmailID", "Email"."Recipient", "User"."Name", "Email"."DateSent", "Email"."EmailText" FROM "Email" LEFT JOIN "User" ON "Email"."Recipient" = "User"."UserID" WHERE "Email"."Sender" = :userid: AND "Email"."DateSent" BETWEEN :start: AND :end: ORDER BY "Email"."DateSent" DESC LIMIT :startpoint:, 20';
        break;
    case 3:
        $title = 'All users\' emails &ndash; '.$timedescription;
        $query = 'SELECT SQL_CALC_FOUND_ROWS "Email"."EmailID", "Email"."Sender", "UserS"."Name" AS "SenderName", "Email"."Recipient", "UserR"."Name" AS "RecipientName", "Email"."DateSent", "Email"."EmailText" FROM "Email" LEFT JOIN "User" AS "UserS" ON "Email"."Sender" = "UserS"."UserID" LEFT JOIN "User" AS "UserR" ON "Email"."Recipient" = "UserR"."UserID" WHERE "Email"."DateSent" BETWEEN :start: AND :end: ORDER BY "Email"."DateSent" DESC LIMIT :startpoint:, 20';
        break;
    default:
        myerror( $unexpectederrormessage,
                 'Bad "message_type"'
                 );
}
$QueryResult = dbquery( DBQUERY_READ_RESULTSET,
                        $query,
                        'userid'     , $_SESSION['MyUserID'] ,
                        'start'      , $SQL_Start            ,
                        'end'        , $SQL_End              ,
                        'startpoint' , $StartPoint
                        );
$CountQueryResult = dbquery( DBQUERY_READ_INTEGER,
                             'SELECT FOUND_ROWS() AS "Co"'
                             );

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

$mypage->script('email_constraints_form.js');
$mypage->title_body($title);
$mypage->loginbox();
$mypage->leaf('h1', $title);
$mypage->opennode('span');

$mypage->text('Show emails');
$mypage->opennode('select', 'id="emailtype_form"');
$mypage->leaf( 'option',
               'sent and received',
               'value=0'.$message_type_selected[0]
               );
$mypage->leaf( 'option',
               'received',
               'value=1'.$message_type_selected[1]
               );
$mypage->leaf( 'option',
               'sent',
               'value=2'.$message_type_selected[2]
               );
if ( $Administrator ) {
    $mypage->leaf( 'option',
                   'for all users',
                   'value=3'.$message_type_selected[3]
                   );
}
$mypage->closenode();

$mypage->text('from');
$mypage->opennode('select', 'id="startdate"');
$i = $FirstEmailYear;
$j = $FirstEmailMonth;
$k = 0;
while ( true ) {
    $sel = ( 12 * $i + $j == $Start ) ?
           ' selected' :
           '';
    $mypage->leaf( 'option',
                   $months[$j].' '.$i,
                   'value="'.$j.'-'.$i.'"'.$sel
                   );
    if ( 12 * $i + $j >= $LastEmail or $k >= 600 ) {
        break;
    }
    $k++;
    $j++;
    if ( $j == 13 ) {
        $j = 1;
        $i++;
    }
}
$mypage->closenode();

$mypage->text('to');
$mypage->opennode('select', 'id="enddate"');
$i = $FirstEmailYear;
$j = $FirstEmailMonth;
$k = 0;
while ( true ) {
    $sel = ( 12 * $i + $j == $End ) ?
           ' selected' :
           '';
    $mypage->leaf( 'option',
                   $months[$j].' '.$i,
                   'value="'.$j.'-'.$i.'"'.$sel
                   );
    if ( 12 * $i + $j >= $LastEmail or $k >= 600 ) {
        break;
    }
    $k++;
    $j++;
    if ( $j == 13 ) {
        $j = 1;
        $i++;
    }
}
$mypage->closenode(2); // select, span

$mypage->opennode('form', 'action="emails.php" method="GET" style="display: inline;"');
$mypage->emptyleaf( 'input',
                    'type="submit" value="Go" onClick="set_submit_vars();"'
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" id="emailtype_id" name="emailtype" value=0'
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" id="ys_id" name="ys" value='.$StartYear
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" id="ms_id" name="ms" value='.$StartMonth
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" id="ye_id" name="ye" value='.$EndYear
                    );
$mypage->emptyleaf( 'input',
                    'type="hidden" id="me_id" name="me" value='.$EndMonth
                    );
$mypage->closenode();

/////////////////////////////////////////////////////
/////////////////////////////////////////////////////
/////////////////////////////////////////////////////

if ( !$CountQueryResult ) {
    $mypage->leaf( 'p',
                   'There aren\'t any emails to display that satisfy the currently selected constraints. You can refine the constraints using the controls above.'
                   );
    $mypage->leaf( 'p',
                   'Click <a href="index.php">here</a> to return to the Main Page.'
                   );
    $mypage->finish();
}
require(HIDDEN_FILES_PATH.'paginate.php');
$PaginationBar = paginationbar( 'email',
                                'emails',
                                SITE_ADDRESS.'emails.php',
                                array( 'ys'        => $StartYear    ,
                                       'ms'        => $StartMonth   ,
                                       'ye'        => $EndYear      ,
                                       'me'        => $EndMonth     ,
                                       'emailtype' => $message_type
                                       ),
                                20,
                                $Page,
                                $CountQueryResult
                                );
$mypage->append($PaginationBar[0]);

if ( $QueryResult !== 'NONE' ) {
    while ( $row = db_fetch_assoc($QueryResult) ) {
        $colourclass = 'messagebox';
        switch ( $message_type ) {
            case 0:
                if ( $row['Sender'] == $_SESSION['MyUserID'] ) {
                    $colourclass = 'mygame';
                    $legend = '<b>Sent</b> to <a href="userdetails.php?UserID='.
                              $row['Recipient'].
                              '">'.
                              $row['RecipientName'].
                              '</a> ';
                } else {
                    $legend = '<b>Received</b> from <a href="userdetails.php?UserID='.
                              $row['Sender'].
                              '">'.
                              $row['SenderName'].
                              '</a> ';
                }
                break;
            case 1:
                $legend = '<b>Received</b> from <a href="userdetails.php?UserID='.
                          $row['Sender'].
                          '">'.
                          $row['Name'].
                          '</a> ';
                break;
            case 2:
                $colourclass = 'mygame';
                $legend = '<b>Sent</b> to <a href="userdetails.php?UserID='.
                          $row['Recipient'].
                          '">'.
                          $row['Name'].
                          '</a> ';
                break;
            default:
                if ( $row['Sender'] == $_SESSION['MyUserID'] ) {
                    $colourclass = 'mygame';
                } else if ( $row['Recipient'] != $_SESSION['MyUserID'] ) {
                    $colourclass = 'myattn';
                }
                $legend = 'Sent from <a href="userdetails.php?UserID='.
                          $row['Sender'].
                          '">'.
                          $row['SenderName'].
                          '</a> to <a href="userdetails.php?UserID='.
                          $row['Recipient'].
                          '">'.
                          $row['RecipientName'].
                          '</a> ';
        }
        $mypage->opennode( 'div',
                           'class="modularbox '.$colourclass.'"'
                           );
        $mypage->leaf( 'p',
                       '(#'.$row['EmailID'].') '.
                           $legend.
                           $row['DateSent'].' GMT',
                       'class="postheading"'
                       );
        $mypage->emptyleaf('hr');
        $mymatch = array();
        if ( preg_match('/\\A([0-9,]+:)/', $row['EmailText'], $mymatch) ) {
            $offsets = explode(',', substr($mymatch[0], 0, -1));
            $MsgText = substr($row['EmailText'], strlen($mymatch[0]));
            $leafcontents = array();
            for ($i=count($offsets)-1; $i>=0; $i--) {
                $leafcontents[] = substr($MsgText, $offsets[$i]);
                $MsgText = substr($MsgText, 0, $offsets[$i]);
            }
            for ($i=count($offsets)-1; $i>=0; $i--) {
                $mypage->leaf('p', $leafcontents[$i]);
            }
        } else {
            $mypage->leaf( 'div',
                           '(Email is in an unexpected format!)',
                           'class="internalmodularbox font_sans_serif" style="background-color: #FFCF9F; text-align: center;"'
                           );
        }
        $mypage->closenode();
    }
    $mypage->append($PaginationBar[1]);
}

$mypage->leaf( 'p',
               'Click <a href="index.php">here</a> to return to the Main Page.'
               );
$mypage->finish();

?>