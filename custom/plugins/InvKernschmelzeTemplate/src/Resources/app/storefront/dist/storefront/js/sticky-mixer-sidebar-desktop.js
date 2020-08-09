$(document).ready(function(){

    // Mixer Sidebar
    var mixerProductOffcanvas = $('#mixer-product-offcanvas');
    var mixerProductOffcanvasInner = $('#mixer-product-offcanvas .inner');
    var headerMain = $('.header-main');
    var footerMain = $('.footer-main');
    var stickyWrapper = $('.sticky-wrapper');

    if (mixerProductOffcanvas.length) {
        var sidebarStickyTop = mixerProductOffcanvas.offset().top;
        var footerMainTop = footerMain.offset().top;

        $(window).scroll(function (event) {
            invMakeMixerSidebarSticky(sidebarStickyTop);
        });

        $(window).resize(function (event) {
            invMakeMixerSidebarSticky(sidebarStickyTop);
        });
    }

    function invMakeMixerSidebarSticky(sidebarStickyTop) {
        var scroll = $(window).scrollTop();

        var parentwidth = stickyWrapper.width();

        if (scroll + mixerProductOffcanvasInner.height() > footerMain.offset().top) {
            mixerProductOffcanvas.removeClass("sticky");
            mixerProductOffcanvas.addClass("sticky-bottom");
            mixerProductOffcanvasInner.width('auto');
        } else if (scroll > sidebarStickyTop) {
            mixerProductOffcanvas.addClass("sticky");
            mixerProductOffcanvasInner.width(parentwidth-60);
            mixerProductOffcanvas.removeClass("sticky-bottom");
        } else {
            mixerProductOffcanvas.removeClass("sticky");
            mixerProductOffcanvasInner.width('auto');
            mixerProductOffcanvas.removeClass("sticky-bottom");
        }
    }
});
