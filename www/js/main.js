let $a = $(".alert");
$a.hide();

$(function(){
    $.nette.init();
    $a.slideToggle();
    setTimeout(function () {
        $a.slideToggle();
    }, 3000)
});

$(document).ready(function () {

    $('#frm-courseForm-room').multiSelect();
    $('#frm-courseForm-lectors').multiSelect();
    $('#frm-eventForm-room').multiSelect();

    $('#frm-eventForm-date').on('change', function () {
        $('#changeFlag').val('1');
    });

    $('#frm-eventForm-time_from').on('change', function () {
        $('#changeFlag').val('1');
    });

    $('#frm-eventForm-time_to').on('change', function () {
        $('#changeFlag').val('1');
    });

});
