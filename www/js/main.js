let $a = $(".alert");
$a.hide();
$(function(){
    $.nette.init();
    $a.slideToggle();
    setTimeout(function () {
        $a.slideToggle();
    }, 3000)
});
