import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

const ELEMENT_LOADER_CLASS = 'element-loader-backdrop';

/**
 * we do have to place loading indicator on top of offcanvas cart and klarna modal.
 * therefore we have to set a sepcific z-index to the loading indicator element,
 * but don't want to overwrite it globally
 */
export default class KlarnaInstantShoppingLoadingIndicator extends ElementLoadingIndicatorUtil {
    /**
     * adds the loader from the element
     *
     * @param {HTMLElement} el
     */
    static create(el) {
        el.classList.add('has-element-loader');
        if (ElementLoadingIndicatorUtil.exists(el)) return;
        KlarnaInstantShoppingLoadingIndicator.appendLoader(el);
        setTimeout(() => {
            const loader = el.querySelector(`.${ELEMENT_LOADER_CLASS}`);
            if (!loader) {
                return;
            }

            loader.classList.add('element-loader-backdrop-open');
        }, 1);
    }

    static getTemplate() {
        return `
        <div class="${ELEMENT_LOADER_CLASS} klarna-loading-indicator">
            <div class="loader" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        `;
    }

    /**
     * inserts the loader into the passed element
     *
     * @param {HTMLElement} el
     */
    static appendLoader(el) {
        el.insertAdjacentHTML('beforeend', KlarnaInstantShoppingLoadingIndicator.getTemplate());
    }
}
