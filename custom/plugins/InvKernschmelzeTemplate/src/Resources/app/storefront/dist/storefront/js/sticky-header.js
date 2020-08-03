$(document).ready(function(){
    var header = $('.header-wrapper');
    var padder = $('.sticky-padder');

    var sticky = header.offset();
    var stickyTop = sticky.top;

    $(window).scroll( function(event) {
        if (!$(".mixer-product-list").length) {
            invMakeDivSticky(stickyTop);
        }
    });

    padder.height(header.height());

    $( window ).resize(function() {
        padder.height(header.height());
    });

    function invMakeDivSticky(stickyTop) {
        var scroll = $(window).scrollTop();

        if (scroll > stickyTop) {
            header.addClass("sticky");
            padder.addClass("show");
        } else {
            header.removeClass("sticky");
            padder.removeClass("show");
        }
    }
});
