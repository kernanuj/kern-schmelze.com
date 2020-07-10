$(document).ready(function(){
    $(".center").slick({
        dots: false,
        infinite: true,
        centerMode: true,
        centerPadding: '20%',
        slidesToShow: 1,
        slidesToScroll: 1,
        lazyLoad: 'ondemand', // ondemand progressive anticipated
        infinite: true,
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
