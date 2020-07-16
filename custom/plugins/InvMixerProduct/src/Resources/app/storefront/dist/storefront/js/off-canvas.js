$(document).ready(function(){
    $("[data-trigger]").on("click", function(e){
        e.preventDefault();
        e.stopPropagation();
        var offcanvas_id =  $(this).attr('data-trigger');
        console.log(offcanvas_id);
        $(offcanvas_id).toggleClass("show");
        $('body').toggleClass("offcanvas-active");
        $(".screen-overlay").toggleClass("show");
    });

    $(".mix-product-offcanvas-close, .screen-overlay").click(function(e){
        $(".screen-overlay").removeClass("show");
        $("#mixer-product-offcanvas").removeClass("show");
        $("body").removeClass("offcanvas-active");
    });
});
