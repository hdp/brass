function set_submit_vars () {
    var start = document.getElementById('startdate').value.split('-');
    var end   = document.getElementById('enddate').value.split('-');
    if ( start.length < 2 || end.length < 2 ) {
        return false;
    }
    document.getElementById('ys_id').value = start[1];
    document.getElementById('ms_id').value = start[0];
    document.getElementById('ye_id').value = end[1];
    document.getElementById('me_id').value = end[0];
    document.getElementById('emailtype_id').value = document.getElementById('emailtype_form').value;
    return true;
}