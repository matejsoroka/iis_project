let $a = $(".alert");
$a.hide();
$(function(){
    $a.slideToggle();
    setTimeout(function () {
        $a.slideToggle();
    }, 3000)
});
