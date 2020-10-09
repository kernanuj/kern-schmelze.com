import updateSliders from "./modules/slider-fix";

document.addEventListener('readystatechange', (event) => {
    if (event.target.readyState === 'complete' &&
        window.maxiaLazyActive === true
    ) {
        updateSliders();
    }
}, false);

if (module.hot) {
    module.hot.accept();
}