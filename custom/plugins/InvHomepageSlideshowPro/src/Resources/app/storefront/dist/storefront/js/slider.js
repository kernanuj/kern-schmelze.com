$(document).ready(function(){
    var autoplay = true;
    if ($('.center').attr("data-autoplay")) {
        autoplay = $('.center').attr("data-autoplay");
    }

    var autoplayspeed = 5000;
    if ($('.center').attr("data-autoplay-speed")) {
        autoplayspeed = $('.center').attr("data-autoplay-speed");
    }

    console.log($('.center').attr("data-autoplay"));
    console.log($('.center').attr("data-autoplay-speed"));

    $(".center").slick({
        dots: false,
        infinite: true,
        centerMode: true,
        centerPadding: '20%',
        slidesToShow: 1,
        slidesToScroll: 1,
        lazyLoad: 'ondemand', // ondemand progressive anticipated
        infinite: true,
        autoplay: autoplay,
        autoplaySpeed: autoplayspeed,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    arrows: false,
                    centerMode: false,
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 768,
                settings: {
                    arrows: false,
                    centerMode: false,
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: false,
                    centerMode: false,
                    slidesToShow: 1
                }
            }
        ]
    });
});
