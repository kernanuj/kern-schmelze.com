$(document).ready(function(){
    var header = $('.header-wrapper');

    var sticky = header.offset();
    var stickyTop = sticky.top;

    $(window).scroll( function(event) {
        if (!$(".mixer-product-list").length) {
            invMakeDivSticky(stickyTop);
        }
    });

    function invMakeDivSticky(stickyTop) {
        var scroll = $(window).scrollTop();

        if (scroll > stickyTop) {
            header.addClass("sticky");
        } else {
            header.removeClass("sticky");
        }
    }
});
