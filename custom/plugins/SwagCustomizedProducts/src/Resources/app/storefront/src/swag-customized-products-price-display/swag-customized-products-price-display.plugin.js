/* eslint-disable */
import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import StoreApiClient from 'src/service/store-api-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class SwagCustomizedProductPriceDisplay extends Plugin {
    /**
     * Plugin options
     * @type {{idPrefix: string, formControlSelector: string, buyButtonSelector: string, url: string, csrfToken: string}}
     */
    static options = {
        formControlSelector: '.swag-customized-products-form-control',
        buyButtonSelector: '#productDetailPageBuyProductForm .btn-buy',
        idPrefix: 'swag-customized-products-option-id-',
        csrfToken: '',
        url: ''
    };

    /**
     * Initializes the plugin
     *
     * @returns {void}
     */
    init() {
        this.client = new StoreApiClient();
        this.priceDisplayHolder = DomAccess.querySelector(document, '.swag-customized-product__price-display-holder');
        this.buyForm = this.el.parentNode;
        this.buyButton = DomAccess.querySelector(this.buyForm, this.options.buyButtonSelector);

        // Initial pull of the price information
        this.onFormChange();

        this.buyForm.addEventListener('change', this.onFormChange.bind(this));
    }

    /**
     * Event listener which will be triggered when the user enters any data into the form.
     *
     * @event change
     * @returns {void}
     */
    onFormChange() {
        const data = new FormData(this.buyForm);
        data.set('_csrf_token', this.options.csrfToken);

        ElementLoadingIndicatorUtil.create(this.priceDisplayHolder);

        this.client.post(this.options.url, data, this.onTemplateReceived.bind(this));
    }

    /**
     * After getting the price information from the server, this callback renders the returned html template to
     * the holder element.
     *
     * @param {String} data
     * @returns {void}
     */
    onTemplateReceived(data) {
        ElementLoadingIndicatorUtil.remove(this.priceDisplayHolder);

        this.priceDisplayHolder.innerHTML = data;
    }
}
