let $a = $(".alert");

$(function(){
    $.nette.init();
    setTimeout(function () {
        $a.slideToggle();
    }, 2500)
});

$(document).ready(function () {

    $('#frm-courseForm-room').multiSelect();
    $('#frm-courseForm-lectors').multiSelect();

    $('#frm-eventForm-date').on('change', function () {
        $('#changeFlag').val('1');
    });

    $('#frm-eventForm-time_from').on('change', function () {
        $('#changeFlag').val('1');
    });

    $('#frm-eventForm-time_to').on('change', function () {
        $('#changeFlag').val('1');
    });

    $('#frm-eventForm-room').on('change', function () {
        $('#changeFlag').val('1');
    });

    CKEDITOR.replace("frm-courseForm-description");
    CKEDITOR.replace("frm-eventForm-description");
    CKEDITOR.replace("frm-courseForm-equipment");

    for (let i in CKEDITOR.instances) {

        CKEDITOR.instances[i].on('change', function() { CKEDITOR.instances[i].updateElement() });

    }

});
