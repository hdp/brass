function popup (mylink, windowname, wd, ht, scr) {
    if ( !window.focus ) {
        return true;
    }
    var href;
    if ( typeof(mylink) == 'string' ) {
        href = mylink;
    } else {
        href = mylink.href;
    }
    window.open( href,
                 windowname,
                 "width=" + wd + ",height=" + ht + ",scrollbars=" + scr
                 );
    return false;
}