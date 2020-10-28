$(document).ready(function() {
    $("video.lazy source").each(function() {
        var sourceFile = $(this).attr("data-src");
        $(this).attr("src", sourceFile);
        var video = this.parentElement;
        video.load();
        video.play();
    });
});
