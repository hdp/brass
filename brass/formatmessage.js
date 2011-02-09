function tilepic (arg) {
    var myarray   = arg.split(/ /);
    var colour    = 7;
    var tiletype  = 0;
    var techlevel = 1;
    var flipped   = 0;
    var numcubes  = 0;
    var cubetype  = 'coal';
    for (var i=0; i<myarray.length; i++) {
        switch ( myarray[i] ) {
            case '0':      techlevel = 0;                    break;
            case '2':      techlevel = 2;                    break;
            case '3':      techlevel = 3;                    break;
            case '4':      techlevel = 4;                    break;
            case 'coal':   tiletype  = 1;                    break;
            case 'iron':   tiletype  = 2; cubetype = 'iron'; break;
            case 'port':   tiletype  = 3;                    break;
            case 'ship':   tiletype  = 4;                    break;
            case 'red':    colour    = 0;                    break;
            case 'yellow': colour    = 1;                    break;
            case 'green':  colour    = 2;                    break;
            case 'purple': colour    = 3;                    break;
            case 'grey':   colour    = 4;                    break;
            case 'brown':  colour    = 8;                    break;
            case 'flip':   flipped   = 1;                    break;
            case 'c1':     numcubes  = 1;                    break;
            case 'c2':     numcubes  = 2;                    break;
            case 'c3':     numcubes  = 3;                    break;
            case 'c4':     numcubes  = 4;                    break;
            case 'c5':     numcubes  = 5;                    break;
            case 'c6':     numcubes  = 6;
        }
    }
    var yadjust = -47 * tiletype;
    var xadjust = -47 * techlevel - 188 * flipped;
    var alttext;
    if ( flipped ) { alttext = 'A flipped ';    }
    else           { alttext = 'An unflipped '; }
    switch ( colour ) {
        case 0: alttext += 'red';    break;
        case 1: alttext += 'yellow'; break;
        case 2: alttext += 'green';  break;
        case 3: alttext += 'purple'; break;
        case 4: alttext += 'grey';   break;
        case 7: alttext += 'blue';   break;
        case 8: alttext += 'orphan';
    }
    alttext += ' Tech Level ' + techlevel;
    switch ( tiletype ) {
        case 0: alttext += ' Cotton Mill'; break;
        case 1: alttext += ' Coal Mine';   break;
        case 2: alttext += ' Iron Works';  break;
        case 3: alttext += ' Port';        break;
        case 4: alttext += ' Shipyard';
    }
    if ( numcubes == 1 ) { alttext += ' with one ' + cubetype + ' cube on it';               }
    else if ( numcubes ) { alttext += ' with ' + numcubes + ' ' + cubetype + ' cubes on it'; }
    var rtnvar = '<span style="background: transparent url(gfx/t1' +
                 colour + 
                 '.png) ' +
                 xadjust +
                 'px ' +
                 yadjust +
                 'px no-repeat; position: relative; display: inline-block; z-index: 1; width: 48px; height: 48px">';
    switch ( numcubes ) {
        case 1:
            rtnvar += '<img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 12px; left: 14px; z-index: 2">';
            break;
        case 2:
            rtnvar += '<img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 11px; left: 23px; z-index: 2"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 15px; left: 7px; z-index: 3">';
            break;
        case 3:
            rtnvar += '<img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 4px; left: 11px; z-index: 2"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 15px; left: 23px; z-index: 3"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 19px; left: 7px; z-index: 4">';
            break;
        case 4:
            rtnvar += '<img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 4px; left: 19px; z-index: 2"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 8px; left: 3px; z-index: 3"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 15px; left: 26px; z-index: 4"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 19px; left: 10px; z-index: 5">';
            break;
        case 5:
            rtnvar += '<img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 6px; left: 19px; z-index: 2"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 10px; left: 3px; z-index: 3"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 17px; left: 26px; z-index: 4"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 21px; left: 10px; z-index: 5"><img src="gfx/' +
                      cubetype +
                      'cube.png" border=0 alt="" style="position: absolute; top: 3px; left: 13px; z-index: 6">';
            break;
        case 6:
            rtnvar += '<img src="gfx/ironcube6.png" border=0 alt="" style="position: absolute; top: 7px; left: 10px; z-index: 2">';
    }
    return rtnvar +
           '<img src="gfx/trans1.png" border=0 alt="' +
           alttext +
           '" style="vertical-align: middle; z-index: 8"></span>';
}

function gamelink (gameid, label, alwaysdecide) {
    var pagename;
    gameid = parseInt(gameid, 10);
    if ( gameid < 0 ) {
        gameid = -gameid;
        pagename = 'decide.php';
    } else if ( alwaysdecide ) {
        pagename = 'decide.php';
    } else {
        pagename = 'board.php';
    }
    if ( label === '' ) {
        label = location.href.replace( /[a-zA-Z0-9\-_]+\.php(\?.+)?/,
                                       pagename + '?GameID=' + gameid
                                       );
    }
    return '<a href="' + pagename + '?GameID=' + gameid + '">' + label + '</a>';
}

function colourtranslate (whole, colour) {
    var translatedcolour;
    switch (colour) {
        case 'brightred':   translatedcolour = 'F70000'; break;
        case 'darkred':     translatedcolour = '8F0000'; break;
        case 'brightblue':  translatedcolour = '0000FF'; break;
        case 'darkblue':    translatedcolour = '000097'; break;
        case 'brightgreen': translatedcolour = '00EB00'; break;
        case 'darkgreen':   translatedcolour = '00EB00'; break;
        case 'yellow':      translatedcolour = '006B00'; break;
        case 'orange':      translatedcolour = 'D59104'; break;
        case 'purple':      translatedcolour = '87098A'; break;
        case 'pink':        translatedcolour = 'EB17F0'; break;
        case 'gold':        translatedcolour = '987707'; break;
        case 'silver':      translatedcolour = '65605C'; break;
        default:            translatedcolour = '000000';
    }
    return '<span style="color: #' + translatedcolour + ';">';
}

function format_substitute (message) {
    message = message.replace( /\[pounds?\]/g,
                               '&pound;'
                               );
    message = message.replace( /\[francs?\]/g,
                               '&#8355;'
                               );
    message = message.replace( /\[yen\]/g,
                               '&yen;'
                               );
    message = message.replace( /\[euros?\]/g,
                               '&euro;'
                               );
    message = message.replace( /\[currency\]/g,
                               '&curren;'
                               );
    message = message.replace( /\[slash\]/g,
                               '&#47;'
                               );
    message = message.replace( /\[left\]/g,
                               '&#91;'
                               );
    message = message.replace( /\[right\]/g,
                               '&#93;'
                               );
    return message;
}

function format_message (message, alwaysdecide) {
    var myarray = message.split(/\[tilepic\](.*?)\[\/tilepic\]/g);
    if ( myarray.length === 0 ) {
        return 'oh no! internet explorer!';
    }
    message = myarray[0];
    for (var i=1; i<myarray.length; i++) {
        if ( i % 2 ) { message += tilepic(myarray[i]); }
        else         { message += myarray[i];          }
    }
    myarray = message.split(/\[brass=(.*?)\]\s*(.*?)\s*\[\/brass\]/g);
    if ( myarray.length === 0 ) {
        return 'oh no! internet explorer!';
    }
    message = myarray[0];
    for (i=1; i<myarray.length; i++) {
        if ( i % 3 === 1 ) {
            message += gamelink(myarray[i], myarray[i+1], alwaysdecide);
        } else if ( i % 3 === 0 ) {
            message += myarray[i];
        }
    }
    message = message.replace( /\[colour=(.*?)\]/g,
                               colourtranslate
                               );
    message = message.replace( /\[\/colour\]/g,
                               '</span>'
                               );
    message = message.replace( /\[b\]/g,
                               '<b>'
                               );
    message = message.replace( /\[\/b\]/g,
                               '</b>'
                               );
    message = message.replace( /\[i\]/g,
                               '<i>'
                               );
    message = message.replace( /\[\/i\]/g,
                               '</i>'
                               );
    message = message.replace( /\[s\]/g,
                               '<s>'
                               );
    message = message.replace( /\[\/s\]/g,
                               '</s>'
                               );
    message = message.replace( /\[coalcube\]/g,
                               '<img src="gfx/coalcube.png" border=0 alt="A coal cube" style="vertical-align: middle">'
                               );
    message = message.replace( /\[ironcube\]/g,
                               '<img src="gfx/ironcube.png" border=0 alt="A iron cube" style="vertical-align: middle">'
                               );
    return format_substitute(message);
}

function format_all_messages (alwaysdecide) {
    var current_message = null;
    var i = 0;
    while ( true ) {
        current_message = document.getElementById("message_" + i);
        if ( current_message === null ) { break; }
        current_message.innerHTML = format_message(current_message.innerHTML, alwaysdecide);
        i++;
    }
    i = 0;
    while ( true ) {
        current_message = document.getElementById("msgcode_" + i);
        if ( current_message === null ) { break; }
        current_message.innerHTML = format_substitute(current_message.innerHTML);
        i++;
    }
}