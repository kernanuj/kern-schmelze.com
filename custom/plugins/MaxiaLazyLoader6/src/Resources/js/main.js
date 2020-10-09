import lazySizes from "lazysizes";
lazySizes.cfg.init = false;

import "lazysizes/plugins/bgset/ls.bgset";
import "lazysizes/plugins/print/ls.print";
import initLazysizesCustom from "./modules/lazysizes-custom";
import initProductBoxAnimation from "./modules/product-box-animation";

document.addEventListener('readystatechange', (event) => {
    if (event.target.readyState === 'complete' &&
        window.maxiaLazyActive === true
    ) {
        initProductBoxAnimation();
        initLazysizesCustom();
        document.body.classList.add('maxia-lazy');
        lazySizes.cfg = window.lazySizesConfig;
        lazySizes.init();
    }
}, false);

if (module.hot) {
    module.hot.accept();
}