/**
 * Add loading indicator for product images.
 */
export default function() {

    document.addEventListener('lazyloaded', function (event) {
        if (event.target.matches('.product-image')) {
            let container = event.target.closest('.product-image-wrapper');

            if (container !== null && typeof container.classList === 'object') {
                container.classList.add(window.lazySizesConfig.loadedClass);
            }
        }
    });
}