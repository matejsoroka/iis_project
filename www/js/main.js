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

    CKEDITOR.replace("frm-eventForm-description");
    CKEDITOR.replace("frm-courseForm-description");

    let roomSelect = $('#frm-eventForm-room');
    roomSelect.multiSelect();

    $('#frm-eventForm-date').on('change', function () {
        $('#changeFlag').val('1');
    });

    $('#frm-eventForm-time_from').on('change', function () {
        $('#changeFlag').val('1');
    });

    $('#frm-eventForm-time_to').on('change', function () {
        $('#changeFlag').val('1');
    });

    roomSelect.on('change', function () {
        $('#changeFlag').val('1');
    });

});
