$(document).ready(function(){

    // Mixer Sidebar
    var mixerProductOffcanvas = $('#mixer-product-offcanvas');
    var mixerProductOffcanvasInner = $('#mixer-product-offcanvas .inner');
    var headerMain = $('.header-main');
    var footerMain = $('.footer-main');

    if (mixerProductOffcanvas.length) {
        var sidebarStickyTop = mixerProductOffcanvas.offset().top;
        var footerMainTop = footerMain.offset().top;

        $(window).scroll(function (event) {
            invMakeMixerSidebarSticky(sidebarStickyTop);
        });
    }

    function invMakeMixerSidebarSticky(sidebarStickyTop) {
        var scroll = $(window).scrollTop();

        if (scroll + mixerProductOffcanvasInner.height() > footerMain.offset().top) {
            mixerProductOffcanvas.removeClass("sticky");
            mixerProductOffcanvas.addClass("sticky-bottom");
        } else if (scroll > sidebarStickyTop) {
            mixerProductOffcanvas.addClass("sticky");
            mixerProductOffcanvas.removeClass("sticky-bottom");
        } else {
            mixerProductOffcanvas.removeClass("sticky");
            mixerProductOffcanvas.removeClass("sticky-bottom");
        }
    }
});
