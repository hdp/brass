<?php

function formatusername ($name, $number, $badges, $supporterbadges) {
    if ( $_SESSION['LoggedIn'] ) {
        $name = '<a href="userdetails.php?UserID='.$number.'">'.$name.'</a>';
    }
    if ( $badges & 3 ) {
        $name .= ' <img src="gfx/badge-administrator.png" alt="This user is an administrator." title="This user is an administrator." style="vertical-align: text-bottom;">';
    }
    if ( $badges & 4 ) {
        $name .= ' <img src="gfx/badge-translator.png" alt="This user is a translator." title="This user is a translator." style="vertical-align: text-bottom;">';
    }
    if ( $badges & 8 ) {
        $name .= ' <img src="gfx/badge-banned.png" alt="This user is banned." title="This user is banned." style="vertical-align: text-bottom;">';
    }
    if ( $supporterbadges > 0 ) {
        // $supporterbadges = explode('|',$supporterbadges);
        // for ($i=0;$i<count($supporterbadges);$i++) {
        //     $name .= ' <img src="gfx/badge-supporter-'.
        //              $supporterbadges[$i].
        //              '.png" alt="This user is a supporter of the site." title="This user is a supporter of the site." style="vertical-align: text-bottom;">';
        // }
        $name .= ' <img src="gfx/badge-supporter-14.png" alt="This user is a supporter of the site." title="This user is a supporter of the site." style="vertical-align: text-bottom;">';
    }
    return $name;
}

function displaythread ($Thread, $Closed, $Postable, $Administrator, $Banned) {
    global $MessagesPosted, $ReverseMessages;
    if ( $ReverseMessages ) { $SQL = 'CALL "Discussion_ListMessages_Reverse"(:thread:)'; }
    else                    { $SQL = 'CALL "Discussion_ListMessages"(:thread:)';         }
    $QR = dbquery( DBQUERY_READ_RESULTSET,
                   $SQL,
                   'thread' , $Thread
                   );
    if ( $QR === 'NONE' ) {
        echo '<h3>There are no messages to display.</h3>';
    } else {
        $j = 0;
        $k = 0;
        while( $row = db_fetch_assoc( $QR ) ) {
            if ( $row['Administrator'] ) { $admintext = ' (Administrator)'; }
            else                         { $admintext = '';                 }
            if ( $row['Banned'] ) { $bannedtext = ' (Banned)'; }
            else                  { $bannedtext = '';          }
            $displayable = true;
            if ( $row['DeletedByAdmin'] ) {
                $MsgNotifyBox = '<div class="internalmodularbox font_sans_serif" style="background-color: #FFCF9F; text-align: center;"><p style="margin: 6px 0px;">The content of this message has been cleared by an Administrator.';
                if ( $Administrator ) {
                    $MsgNotifyBox .= '<br><b>Since you are an Administrator, you can see the text of the message anyway.</b></p></div>';
                } else if ( @$_SESSION['MyUserID'] == $row['User'] ) {
                    $MsgNotifyBox .= '<br><b>Since you are the person who posted the message, you can see the text of the message anyway.</b></p></div>';
                } else {
                    $MsgNotifyBox .= '</p></div>';
                    $displayable = false;
                }
            } else if ( $row['DeletedByUser'] ) {
                $MsgNotifyBox = '<div class="internalmodularbox font_sans_serif" style="background-color: #FFCF9F; text-align: center;"><p style="margin: 6px 0px;">The content of this message has been cleared by the person who posted it.';
                if ( $Administrator ) {
                    $MsgNotifyBox .= '<br><b>Since you are an Administrator, you can see the text of the message anyway.</b></p></div>';
                } else if ( @$_SESSION['MyUserID'] == $row['User'] ) {
                    $MsgNotifyBox .= '<br><b>Since you are the person who posted the message, you can see the text of the message anyway.</b></p></div>';
                } else {
                    $MsgNotifyBox .= '</p></div>';
                    $displayable = false;
                }
            } else {
                $MsgNotifyBox = '';
            }
            if ( $displayable ) {
                $mymatch = array();
                if ( preg_match('/\\A([01]+;[0-9,]+:)/', $row['MessageText'], $mymatch) ) {
                    $MsgText = substr($row['MessageText'], strlen($mymatch[0]));
                    $mymatch = explode(';', substr($mymatch[0], 0, -1));
                    $types   = str_split($mymatch[0]);
                    $offsets = explode(',', $mymatch[1]);
                    if ( count($types) != count($offsets) ) {
                        $types   = array(0);
                        $offsets = array(0);
                    }
                    $j += count($types) - array_sum($types);
                    $k += array_sum($types);
                    $top_j = $j;
                    $top_k = $k;
                    $MsgText .= ( $types[count($types)-1] === '1' ) ?
                                '</pre>' :
                                '</p>';
                    for ($i=count($types)-1; $i>0; $i--) {
                        $middle_component = ( $types[$i-1] === '1' ) ?
                                            '</pre>' :
                                            '</p>';
                        if ( $types[$i] === '1' ) {
                            $k--;
                            $middle_component .= '<pre id="msgcode_'.$k.'">';
                        } else {
                            $j--;
                            $middle_component .= '<p id="message_'.$j.'">';
                        }
                        $MsgText = substr($MsgText, 0, $offsets[$i]).
                                   $middle_component.
                                   substr($MsgText, $offsets[$i]);
                    }
                    if ( $types[0] === '1' ) {
                        $k--;
                        $MsgText = '<pre id="msgcode_'.$k.'">'.$MsgText;
                    } else {
                        $j--;
                        $MsgText = '<p id="message_'.$j.'">'.$MsgText;
                    }
                    $j = $top_j;
                    $k = $top_k;
                } else {
                    $MsgText = '<div class="internalmodularbox font_sans_serif" style="background-color: #FFCF9F; text-align: center;">(Message is in an unexpected format!)</div>';
                }
            }
            $colourclass = 'messagebox';
            if ( $_SESSION['LoggedIn'] and $row['User'] == $_SESSION['MyUserID'] ) {
                    $colourclass = 'mygame';
                if ( $row['DeletedByUser'] ) {
                    $postoption = ' <a href="threadview.php?TheMessage='.$row['MessageID'].'&amp;AsAdmin=0&amp;ThreadID='.$Thread.'">Unclear</a>';
                } else {
                    $postoption = ' <a href="threadview.php?TheMessage='.$row['MessageID'].'&amp;AsAdmin=0&amp;ThreadID='.$Thread.'">Clear</a>';
                }
            } else if ( $Administrator ) {
                if ( $row['DeletedByAdmin'] ) {
                    $postoption = ' <a href="threadview.php?TheMessage='.$row['MessageID'].'&amp;AsAdmin=1&amp;ThreadID='.$Thread.'">Unclear</a>';
                } else {
                    $postoption = ' <a href="threadview.php?TheMessage='.$row['MessageID'].'&amp;AsAdmin=1&amp;ThreadID='.$Thread.'">Clear</a>';
                }
            } else {
                $postoption = '';
            }
            if ( $_SESSION['LoggedIn'] ) { $PosterText = '<a href="userdetails.php?UserID='.$row['User'].'">'.$row['Name'].'</a>'; }
            else                         { $PosterText = $row['Name']; }
            echo '<div class="modularbox '.
                 $colourclass.
                 '"><p class="postheading">Posted by <b>'.
                 formatusername( $row['Name'],
                                 $row['User'],
                                 $row['Administrator'] + 4 * $row['Translator'] + 8 * $row['Banned'],
                                 $row['Badge']
                                 ).
                 '</b> '.
                 $row['PostDate'].
                 ' GMT'.
                 $postoption.
                 '</p><hr>'.
                 $MsgNotifyBox;
            if ( $displayable ) { echo $MsgText; }
            echo '</div>';
        }
    }
    if ( !$Postable and !$Administrator ) {
        echo '<h3>You cannot post a message here.';
        if ( $Banned ) {
            echo ' Additionally, banned users may not post messages.';
        } else if ( !$_SESSION['LoggedIn'] ) {
            echo ' Additionally, you must log in in order to post messages anywhere.';
        }
        echo '</h3>';
    } else if ( ( $Closed == 'Closed' or $Closed == 'Forced Closed' ) and !$Administrator ) {
        echo '<h3>You cannot post messages in threads that are closed.';
        if ( $Banned ) {
            echo ' Additionally, banned users may not post messages.';
        } else if ( @$_SESSION['LoggedIn'] ) {} else {
            echo ' Additionally, you must log in in order to post messages in any thread.';
        }
        echo '</h3>';
    } else if ( $Banned ) {
        echo '<h3>Banned users may not post messages.</h3>';
    } else if ( @$_SESSION['LoggedIn'] ) {
        echo '<h3>Post a new message</h3><form action="threadview.php" method="POST"><p><textarea cols="100" rows="16" name="ThePost"></textarea><p><input type="submit" name="FormSubmitA" value="Post"><p>The available formatting options are detailed <a href="http://orderofthehammer.com/credits.htm#formatting">here</a>. The limit is around 50,&thinsp;000 characters (proviso: depending on the content you enter, the number of characters after the content is processed may vary slightly from that before).</p><input type="hidden" name="WhichThread" value="'.$Thread.'"><input type="hidden" name="MessagesPostedCounter" value="'.$MessagesPosted.'"></form>';
    } else {
        echo '<h3>You must log on in order to post messages.</h3>';
    }
}

?>