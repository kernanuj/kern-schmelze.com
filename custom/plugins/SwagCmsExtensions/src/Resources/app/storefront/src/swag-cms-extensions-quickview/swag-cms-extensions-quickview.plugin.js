/* eslint-disable import/no-unresolved */
/* eslint-disable import/extensions */
/* eslint-env jquery */
import NativeEventEmitter from 'src/helper/emitter.helper';
import Plugin from 'src/plugin-system/plugin.class';
import PseudoModalUtil from 'src/utility/modal-extension/pseudo-modal.util';
import PluginManager from 'src/plugin-system/plugin.manager';
import HttpClient from 'src/service/http-client.service';
import DomAccess from 'src/helper/dom-access.helper';
import queryString from 'query-string';

import { SWAG_CMS_EXTENSIONS_LISTING_EXTENSION }
    from '../plugin-extensions/listing/swag-cms-extensions-listing-extension.plugin';
import { SWAG_CMS_EXTENSIONS_VARIANT_SWITCH_EXTENSION }
    from '../plugin-extensions/variant-switch/swag-cms-extensions-variant-switch-extension.plugin';

import QuickviewLoadingIndicatorUtil from '../util-extensions/quickview-loading-indicator.util';
import ProductStruct from './swag-cms-extensions-quickview-product-struct.util';
import CarouselTemplateUtil, { SWAG_CMS_EXTENSIONS_QUICKVIEW_CAROUSEL }
    from './swag-cms-extensions-quickview-carousel.util';

export const SWAG_CMS_EXTENSIONS_QUICKVIEW = {
    EVENT: {
        INITIALISED: 'SwagCmsExtensions/quickviewInitialised',
        PRODUCT_REGISTERED: 'SwagCmsExtensions/productRegistered',
        EVENTS_REGISTERED: 'SwagCmsExtensions/eventsRegistered',
        MODAL_OPENED: 'SwagCmsExtensions/modalOpened',
        PRODUCT_UPDATED: 'SwagCmsExtensions/quickviewProductUpdated',
        BUY_FORM_BEFORE_SUBMIT: 'beforeFormSubmit',
        BS_CAROUSEL_SLIDE: 'slide.bs.carousel'
    }
};

export default class SwagCmsExtensionsQuickview extends Plugin {
    static options = {
        /**
         * This selector is used to find instances of the listing plugin, so this plugin can
         * react to sorting and pagination.
         *
         * @var {string}
         */
        listingSelector: '[data-listing]',

        /**
         * This selector is used to find instances of the variant switch plugin, so this plugin
         * can fetch and show a new quickview when the variant is switched.
         *
         * @var {string}
         */
        variantSwitchSelector: '[data-variant-switch]',

        /**
         * This selector is used to find instances of the add-to-cart plugin, so quickviews can
         * react to products being added to the shopping cart (needed for closing the quickview).
         *
         * @var {string}
         */
        addToCartSelector: '[data-add-to-cart]',

        taxLinkSelector: 'a.product-detail-tax-link',

        /**
         * productBoxSelector is the selector used to identify all product-boxes in the section
         * this instance is running on.
         *
         * @var {string}
         */
        productBoxSelector: '[data-swag-cms-extensions-quickview-box]',

        /**
         * productBoxLinkSelector is the selector used to identify all links or clickable elements
         * inside a product box. This is used to catch all click events and open a quickview instead
         * of executing the default action.
         *
         * @var {string}
         */
        productBoxLinkSelector: [
            'a.product-name',
            'a.product-image-link',
            '.swag-cms-extensions-quickview-listing-button-detail a.btn'
        ].join(', '),

        /**
         * This selector is used to find the inner quickview container which is useful for saving
         * the current scroll-position relative to the outer quickview container. Usually the
         * quickview is only scrollable on small screens.
         *
         * @var {string}
         */
        quickviewContainerSelector: '.swag-cms-extensions-quickview-container',

        /**
         * modalClass is used to find the active quickview modal container and apply additional classes
         * to it.
         *
         * @var {string}
         */
        modalClass: 'swag-cms-extensions-quickview-modal',

        /**
         * quickviewControllerRoute is the index inside the window.router array at which the route to the
         * quickview-controller is stored.
         *
         * @var {string}
         */
        quickviewControllerRoute: 'widgets.swag.cmsExtensions.quickview',

        /**
         * quickviewControllerVariantRoute is the index inside the window.router array at which the route to the
         * quickview-controller's variant action is stored.
         *
         * @var {string}
         */
        quickviewControllerVariantRoute: 'widgets.swag.cmsExtensions.quickview.variant',

        /**
         * The ID of the cms-section this quickview instance is responsible for.
         *
         * @var {string}
         */
        sectionId: '',

        /**
         * arrow-left icon-component used as a "previous" button for the quickview carousel.
         *
         * @var {string}
         */
        arrowHeadLeft: '',

        /**
         * arrow-right icon-component used as a "next" button for the quickview carousel.
         *
         * @var {string}
         */
        arrowHeadRight: ''
    };

    /**
     * Initialises the SwagCmsExtensionsQuickview plugin
     *
     * @returns {void}
     */
    init() {
        this._firstLoad = false;
        this._currentProductId = '';
        this._pseudoModal = null;
        this._scrollPos = 0;
        this._products = new Map();
        this._isModalLoading = false;

        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._carouselTemplateUtil = new CarouselTemplateUtil(
            this.options.arrowHeadLeft,
            this.options.arrowHeadRight
        );

        this._registerEventListeners();
        this._registerProducts();

        this.$emitter.publish(SWAG_CMS_EXTENSIONS_QUICKVIEW.EVENT.INITIALISED, this);
    }

    /**
     * _reInitialise is responsible for initialising the plugin again,
     * when for example the listing was reloaded or the sorting changed.
     *
     * The listing and product update listeners don't need to be added again, so
     * they're left out here.
     *
     * @returns {void}
     */
    _reInitialise() {
        this._products.clear();

        this._registerProductBoxListeners();
        this._registerProducts();
    }

    /**
     * _registerProducts searches all product boxes in the section this instance
     * is responsible for and adds them to the internal cache (this._products).
     *
     * @returns {Boolean}
     */
    _registerProducts() {
        const boxes = this._getProductBoxes(this.el.parentNode);

        if (!boxes) {
            return boxes;
        }

        boxes.forEach(box => this._registerProduct(this._getProductBoxDataset(box).productId));
        return true;
    }

    /**
     * Adds a product to the internal cache, assigns a loading indicator as the template by default.
     *
     * @param {string} productId
     *
     * @returns {void}
     */
    _registerProduct(productId) {
        this._products.set(
            productId,
            new ProductStruct(
                productId,
                false,
                QuickviewLoadingIndicatorUtil.getTemplate()
            )
        );

        this.$emitter.publish(SWAG_CMS_EXTENSIONS_QUICKVIEW.EVENT.PRODUCT_REGISTERED, productId);
    }

    /**
     * @returns {void}
     */
    _registerEventListeners() {
        this._registerProductBoxListeners();
        this._registerListingListener();
    }

    /**
     * @returns {Boolean}
     */
    _registerProductBoxListeners() {
        const boxes = this._getProductBoxes(this.el.parentNode);

        if (!boxes) {
            return boxes;
        }

        boxes.forEach(this._registerLinkClickListeners.bind(this));
        return true;
    }

    /**
     * _registerListingListener adds a listener for the listing plugin so the plugin can
     * react when the listing changes due to sorting, or pagination.
     *
     * The error raised if no listing is found is ignored, since some pages don't have a
     * listing we'd need to monitor.
     *
     * @returns {void}
     */
    _registerListingListener() {
        this._addEventListener(
            SWAG_CMS_EXTENSIONS_LISTING_EXTENSION.EVENT.RENDER_RESPONSE,
            this._reInitialise,
            DomAccess.querySelector(document, this.options.listingSelector, false)
        );
    }

    /**
     * @param {HTMLElement|EventTarget} parent
     *
     * @returns {array|Boolean}
     */
    _getProductBoxes(parent) {
        const boxes = DomAccess.querySelectorAll(parent, this.options.productBoxSelector, false);

        if (!boxes) {
            return boxes;
        }

        return [...boxes].filter(
            box => this._getProductBoxDataset(box).sectionId === this.options.sectionId
        );
    }

    /**
     * @param {HTMLElement} box
     *
     * @returns {object}
     */
    _getProductBoxDataset(box) {
        return JSON.parse(box.dataset.swagCmsExtensionsQuickviewBoxOptions);
    }

    /**
     * _registerLinkClickListeners is responsible for adding click listeners on
     * all links in a product box, so the event can be intercepted and handled
     * by this plugin.
     *
     * @param {HTMLElement|EventTarget} box
     *
     * @returns {void}
     */
    _registerLinkClickListeners(box) {
        const callback = this._onProductBoxLinkClick.bind(this, {
            productId: this._getProductBoxDataset(box).productId
        });

        DomAccess.querySelectorAll(box, this.options.productBoxLinkSelector).forEach((link) => {
            this._addEventListener('click', callback, link);
        });
    }

    /**
     * @param {object} options
     * @param {Event} event
     *
     * @returns {void}
     */
    _onProductBoxLinkClick(options, event) {
        event.preventDefault();

        this._openModal(options);
    }

    /**
     * @param {object}
     */
    _openModal({ productId }) {
        if (this._isModalLoading) {
            return;
        }

        this._isModalLoading = true;
        this._firstLoad = true;
        this._currentProductId = productId;
        this._pseudoModal = new PseudoModalUtil(
            this._carouselTemplateUtil.create([
                this._carouselTemplateUtil.createItem(QuickviewLoadingIndicatorUtil.getTemplate(), '', true)
            ])
        );

        this._pseudoModal.open(this._fetchWindow.bind(this, productId));
        this._addPluginClasses(this._pseudoModal);

        this.$emitter.publish(SWAG_CMS_EXTENSIONS_QUICKVIEW.EVENT.MODAL_OPENED, this);
    }

    /**
     * @param {PseudoModalUtil} pseudoModal
     *
     * @returns {void}
     */
    _addPluginClasses(pseudoModal) {
        pseudoModal.getModal().classList.add(this.options.modalClass);

        DomAccess.querySelector(pseudoModal.getModal(), '.modal-dialog').classList.add('modal-dialog-centered', 'modal-xl');
        DomAccess.querySelector(pseudoModal.getModal(), '.modal-header').remove();
    }

    /**
     * @param {string} productId
     *
     * @returns {void}
     */
    _fetchWindow(productId) {
        const previousProductId = this._getSibling(productId, -1, this._products);
        const nextProductId = this._getSibling(productId, 1, this._products);

        this._updateProductQuickview(previousProductId);
        this._updateProductQuickview(productId);
        this._updateProductQuickview(nextProductId);
    }

    /**
     * @param {string} productId
     *
     * @returns {void}
     */
    _updateProductQuickview(productId) {
        this._fetchQuickview(productId, this._setProductQuickview.bind(this, productId));
    }

    /**
     * @param {string} productId
     * @param {function} callback
     *
     * @returns {void}
     */
    _fetchQuickview(productId, callback) {
        if (this._products.get(productId).loaded) {
            callback(this._products.get(productId).quickview);
            return;
        }

        this._client.get(
            `${window.router[this.options.quickviewControllerRoute]}/${productId}`,
            callback.bind(this)
        );
    }

    /**
     * @param {string} listingProductId
     * @param {object} data
     * @param {function} callback
     *
     * @returns {void}
     */
    _fetchVariantQuickview(listingProductId, data, callback) {
        const variantRoute = window.router[this.options.quickviewControllerVariantRoute];

        this._client.get(
            `${variantRoute}/${listingProductId}?${queryString.stringify(data)}`,
            callback.bind(this)
        );
    }

    /**
     * @param {string} productId
     * @param {string} quickview
     *
     * @returns {void}
     */
    _setProductQuickview(productId, quickview) {
        this._products.get(productId).loaded = true;
        this._products.get(productId).quickview = quickview;

        this._syncProductsAndDom(productId);
    }

    /**
     * @param {string} productId
     * @param {string} variantId
     * @param {string} quickview
     *
     * @returns {void}
     */
    _setVariantQuickview(productId, variantId, quickview) {
        const product = this._products.get(productId);
        const variant = new ProductStruct(
            variantId,
            false,
            quickview
        );

        product.variants.set(variant.id, variant);
        product.variantId = variant.id;

        this._syncProductsAndDom(productId);
    }

    /**
     * This method is responsible for updating the DOM, when the internal cache changes.
     * It also handles the creation of the carousel markup, when the modal is opened
     * for the first time.
     *
     * @param {string} productId
     *
     * @returns {void}
     */
    _syncProductsAndDom(productId) {
        if (this._firstLoad) {
            const elements = [];

            this._products.forEach(product => {
                elements.push(this._carouselTemplateUtil.createItem(
                    product.quickview,
                    product.id,
                    product.id === this._currentProductId
                ));
            });

            this._pseudoModal.updateContent(
                this._carouselTemplateUtil.create(elements)
            );

            this._registerCarouselListener();
            this._firstLoad = false;
        }

        const product = this._products.get(productId);
        const variantId = product.variantId;

        this._updateCarouselItemContent(
            productId,
            variantId ? product.variants.get(variantId).quickview : product.quickview
        );

        this._isModalLoading = false;
    }

    /**
     * @param {HTMLElement|EventTarget} carouselItem
     *
     * @returns {void}
     */
    _registerBuyFormListener(carouselItem) {
        this._addEventListener(
            SWAG_CMS_EXTENSIONS_QUICKVIEW.EVENT.BUY_FORM_BEFORE_SUBMIT,
            this._pseudoModal.close.bind(this._pseudoModal),
            DomAccess.querySelector(carouselItem, this.options.addToCartSelector)
        );
    }

    /**
     * @param {HTMLElement|EventTarget} carouselItem
     *
     * @returns {void}
     */
    _registerTaxLinkListener(carouselItem) {
        this._addEventListener(
            'click',
            this._onTaxLinkClick,
            DomAccess.querySelector(carouselItem, this.options.taxLinkSelector)
        );
    }

    /**
     * @param {HTMLElement|EventTarget} carouselItem
     *
     * @returns {void}
     */
    _registerVariantSwitchListener(carouselItem) {
        this._addEventListener(
            SWAG_CMS_EXTENSIONS_VARIANT_SWITCH_EXTENSION.EVENT.VARIANT_SWITCHED,
            this._onVariantSwitch,
            DomAccess.querySelector(carouselItem, this.options.variantSwitchSelector, false)
        );
    }

    /**
     * @returns {void}
     */
    _registerCarouselListener() {
        $(`#${SWAG_CMS_EXTENSIONS_QUICKVIEW_CAROUSEL.CAROUSEL_ID}`).on(
            SWAG_CMS_EXTENSIONS_QUICKVIEW.EVENT.BS_CAROUSEL_SLIDE,
            this._onCarouselSlide.bind(this)
        );
    }

    /**
     * @param {Event} event
     *
     * @returns {void}
     */
    _onCarouselSlide(event) {
        const direction = event.direction === 'left' ? -1 : 1;
        const currentProductId = event.relatedTarget.dataset.swagCmsExtensionsQuickviewCarouselProductId;
        const nextProductId = this._getSibling(currentProductId, direction, this._products);

        this._fetchWindow(currentProductId);
        this._currentProductId = nextProductId;
    }

    /**
     * @param {string} productId
     * @param {string} content
     *
     * @returns {void}
     */
    _updateCarouselItemContent(productId, content) {
        const carouselItem = this._getCarouselItem(productId, this._pseudoModal.getModal());

        if (!carouselItem) {
            return;
        }

        carouselItem.innerHTML = content;

        PluginManager.initializePlugins();
        this._registerBuyFormListener(carouselItem);
        this._registerVariantSwitchListener(carouselItem);
        this._registerTaxLinkListener(carouselItem);

        if (this._scrollPos > 0) {
            this._setContainerScrollPosition(productId, this._scrollPos);
            this._scrollPos = 0;
        }
    }

    /**
     * @param {string} productId
     * @param {HTMLElement|EventTarget} parentNode
     *
     * @returns {HTMLElement}
     */
    _getCarouselItem(productId, parentNode) {
        return DomAccess.querySelector(
            parentNode,
            `[${SWAG_CMS_EXTENSIONS_QUICKVIEW_CAROUSEL.CAROUSEL_ITEM_PRODUCT_ID_ATTR}="${productId}"]`,
            false
        );
    }

    /**
     * _onVariantSwitch is called, when the extended variant-switch plugin fires its
     * event. In case the quickview was opened on a small screen, the user might have
     * scrolled the container a bit, so we're saving the current scroll position of the
     * container here. After this, the variant's quickview markup is fetched and displayed.
     *
     * @param {event} event
     *
     * @returns {void}
     */
    _onVariantSwitch(event) {
        const listingProductId = event.detail.listingProductId;
        const data = {
            switched: event.detail.switched,
            options: event.detail.options,
            parentId: event.detail.parentId
        };
        const setVariantQuickviewCallback = this._setVariantQuickview.bind(
            this,
            listingProductId,
            this._joinValues(JSON.parse(data.options))
        );

        this._scrollPos = this._getContainerScrollPosition(listingProductId);

        this._fetchVariantQuickview(
            listingProductId,
            data,
            setVariantQuickviewCallback
        );
    }

    /**
     * Closes the quickview and opens the tax information modal
     *
     * @param {event} event
     *
     * @returns {void}
     */
    _onTaxLinkClick(event) {
        event.preventDefault();

        this._client.get(event.target.dataset.url, (response) => {
            this._pseudoModal.close();
            (new PseudoModalUtil(response)).open();
        });
    }

    /**
     * _addEventListener is a helper method which adds a new event listener to an element,
     * ensuring that the listener wont be registered multiple times, and that a event emitter
     * is present on the element, which helps with event handling.
     *
     * @param {string} eventName
     * @param {function} callback
     * @param {HTMLElement|EventTarget} element
     *
     * @returns {void}
     */
    _addEventListener(eventName, callback, element) {
        const cb = callback.bind(this);

        if (!element) {
            return;
        }

        if (!element.$emitter) {
            /* eslint-disable-next-line no-new */
            new NativeEventEmitter(element);
        }

        if (this._listenerPresent(element, eventName, cb)) {
            return;
        }

        element.$emitter.subscribe(eventName, cb);
    }

    /**
     * Checks if a listener for the given event is already present on the given elements $emitter.
     *
     * @param {HTMLElement|EventTarget} element
     * @param {string} eventName
     * @param {function|null} callback
     *
     * @returns {boolean}
     */
    _listenerPresent(element, eventName, callback = null) {
        function eventNamePresent(_listener) {
            return _listener.splitEventName[0] === eventName;
        }

        function callbackPresent(_listener) {
            if (callback === null) {
                return true;
            }
            return _listener.cb === callback;
        }

        if (!element.$emitter) {
            return false;
        }

        return element.$emitter.listeners.some(listener => (eventNamePresent(listener) && callbackPresent(listener)));
    }

    /**
     * _setContainerScrollPosition gets the quickview container of the specified product
     * and sets its scroll position to the specified value, or the internal one, if none
     * is given.
     *
     * @param {string} productId
     * @param {number} scrollPos
     *
     * @returns {void}
     */
    _setContainerScrollPosition(productId, scrollPos) {
        DomAccess.querySelector(
            this._getCarouselItem(productId, this._pseudoModal.getModal()),
            this.options.quickviewContainerSelector
        ).scrollTop = scrollPos;
    }

    /**
     * _getContainerScrollPosition gets the quickview container of the specified product
     * and returns its scroll position.
     *
     * @param {string} productId
     *
     * @returns {number}
     */
    _getContainerScrollPosition(productId) {
        return DomAccess.querySelector(
            this._getCarouselItem(productId, this._pseudoModal.getModal()),
            this.options.quickviewContainerSelector
        ).scrollTop;
    }

    /**
     * @param {object} obj
     *
     * @returns {string}
     */
    _joinValues(obj) {
        return Object.keys(obj).map((key) => obj[key]).join('');
    }

    /**
     * @param {string} productId
     * @param {int} direction
     * @param {Map<string,ProductStruct>} productMap
     *
     * @returns {string|null}
     */
    _getSibling(productId, direction, productMap) {
        const productList = [...productMap.values()];
        const startIndex = productList.findIndex(el => el.id === productId);

        if (startIndex < 0) {
            return null;
        }

        let siblingIndex = (direction > 0 ? startIndex + 1 : startIndex - 1) % productList.length;

        if (siblingIndex < 0) {
            siblingIndex = productList.length - 1;
        }

        return productList[siblingIndex].id;
    }
}
