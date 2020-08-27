$(document).ready(function() {
    var equal = $('.equal');

    // Equal height generic
    function equalHeight(element) {
        $(element).matchHeight();
    };

    if(equal.length) {
        equalHeight('.equal');
        $(window).resize(function () {
            equal.css('height', 'auto');
            equalHeight('.equal');
        });
    }
});
