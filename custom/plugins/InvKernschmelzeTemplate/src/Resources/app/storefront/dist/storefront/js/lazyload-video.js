$(document).ready(function() {
    function loadLazyVideo() {
        var el = $("video.lazy"),
            offset = el.offset(),
            scrollTop = $(window).scrollTop();

        if ((scrollTop > offset.top - 700)) {
            $("video.lazy source").each(function() {
                var sourceFile = $(this).attr("data-src");
                $(this).attr("src", sourceFile);
                var video = this.parentElement;
                $(this).parent('video').removeClass('lazy');
                video.load();
                video.play();
            });
        }
    }

    if($("video#video-container.lazy").length) {
        if(!$("video.lazy source").attr("src")) {
            loadLazyVideo();

            $(window).scroll(function() {
                if($("video.lazy").length) {
                    loadLazyVideo();
                }
            });
        }
    }
});
