$(document).ready(function() {
    console.log('Video Lazyload script loaded.');
    $("video.lazy source").each(function() {
        var sourceFile = $(this).attr("data-src");
        $(this).attr("src", sourceFile);
        var video = this.parentElement;
        video.load();
        video.play();
    });
});
