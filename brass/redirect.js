var waitlength;
var url;
var myinterval;

function cw_countdown () {
    if ( waitlength == 1 ) {
        clearInterval(myinterval);
        document.title = 'Brass Online - Closing...';
        document.getElementById('redirect_notice').innerHTML = 'This window will now close.';
        window.close();
    } else {
        waitlength--;
        var desc;
        if ( waitlength == 1 ) { desc = '1 second';              }
        else                   { desc = waitlength + ' seconds'; }
        document.title = 'Brass Online - Closing in ' + desc;
        document.getElementById('num_seconds').innerHTML = desc;
    }
}

function redirect_countdown () {
    if ( waitlength == 1 ) {
        clearInterval(myinterval);
        document.title = 'Brass Online - Redirecting...';
        document.getElementById('redirect_notice').innerHTML = 'You are now being redirected. Please wait while the new page loads.';
        window.location = url;
    } else {
        waitlength--;
        var desc;
        if ( waitlength == 1 ) { desc = '1 second';              }
        else                   { desc = waitlength + ' seconds'; }
        document.title = 'Brass Online - Redirecting in ' + desc;
        document.getElementById('num_seconds').innerHTML = desc;
    }
}

function redirect_begin (seconds, targeturl) {
    waitlength = seconds;
    url = targeturl;
    if ( url === false ) {
        myinterval = setInterval(cw_countdown, 1000);
    } else {
        myinterval = setInterval(redirect_countdown, 1000);
    }
}