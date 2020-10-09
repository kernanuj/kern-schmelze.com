export default function () {
    /*
     * add support for single background images
     */
    document.addEventListener('lazybeforeunveil', function(e) {
        var bg = e.target.getAttribute('data-bg');

        if (bg) {
            e.target.style.backgroundImage = 'url(' + bg + ')';
        }
    });

    /*
     * remove src attribute (placeholder) when loading
     */
    document.addEventListener('lazybeforeunveil', function(e) {
        e.target.removeAttribute('src');
    });

    /*
     * add loaded class after load
     */
    document.addEventListener('lazyloaded', function(e) {
        e.target.classList.add(window.lazySizesConfig.loadedClass);
    });
}