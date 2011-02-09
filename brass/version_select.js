function alter_link_0 (current_href, target_version_value) {
    var j;
    var workingarray;
    workingarray = current_href.split("&");
    for (j=0; j<workingarray.length; j++) {
        if ( workingarray[j].substr(0,6) == "Board=" ) {
            workingarray[j] = "Board=" + target_version_value;
        }
    }
    return workingarray.join("&");
}

function alter_link_1 (current_href, target_version_value, target_players_value) {
    var j;
    var workingarray;
    workingarray = current_href.split("&");
    for (j=0; j<workingarray.length; j++) {
        if ( workingarray[j].substr(0,6) == "Board=" ) {
            workingarray[j] = "Board=" + target_version_value;
        }
        if ( workingarray[j].substr(0,8) == "Players=" ) {
            workingarray[j] = "Players=" + target_players_value;
        }
    }
    return workingarray.join("&");
}

function alter_link_2 (current_href, target_version_value, target_players_value, target_mode_value) {
    var j;
    var workingarray;
    var url;
    workingarray = current_href.split("?");
    url = workingarray[0];
    workingarray = workingarray[1].split("&");
    for (j=0; j<workingarray.length; j++) {
        if ( workingarray[j].substr(0,6) == "Board=" ) {
            workingarray[j] = "Board=" + target_version_value;
        }
        if ( workingarray[j].substr(0,8) == "Players=" ) {
            workingarray[j] = "Players=" + target_players_value;
        }
        if ( workingarray[j].substr(0,5) == "Mode=" ) {
            workingarray[j] = "Mode=" + target_mode_value;
        }
    }
    return url + "?" + workingarray.join("&");
}

function alter_all_links (parameters) {
    var target_version_value = document.getElementById('boardselect').value;
    var target_players_value;
    var target_mode_value;
    if ( parameters > 0 ) {
        target_players_value = document.getElementById('playersselect').value;
    }
    if ( parameters > 1 ) {
        target_mode_value = document.getElementById('modeselect').value;
    }
    var current_link = null;
    var i = 0;
    while ( true ) {
        current_link = document.getElementById("link_" + i);
        if ( current_link === null ) { break; }
        switch ( parameters ) {
            case 2:
                current_link.href = alter_link_2( current_link.href,
                                                  target_version_value,
                                                  target_players_value,
                                                  target_mode_value
                                                  );
                break;
            case 1:
                current_link.href = alter_link_1( current_link.href,
                                                  target_version_value,
                                                  target_players_value
                                                  );
                break;
            default:
                current_link.href = alter_link_0( current_link.href,
                                                 target_version_value
                                                 );
        }
        i++;
    }
}