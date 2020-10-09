/**
 * Add loading indicator for product images.
 */
export default function() {

    document.addEventListener('lazyloaded', function (event) {
        if (event.target.matches('.product-image')) {
            let container = event.target.closest('.product-image-wrapper');
            container.classList.add(window.lazySizesConfig.loadedClass);
        }
    });
}