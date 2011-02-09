var loadtime;

function start_timing () {
    var loaddate = new Date();
    loadtime = loaddate.getTime();
}

function my_click_function () {
    var clickdate = new Date();
    document.getElementById('StepLength_id').value = clickdate.getTime() - loadtime;
    return true;
}