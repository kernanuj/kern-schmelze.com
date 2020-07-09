import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import KlarnaInstantShoppingLoadingIndicator from '../loading-indicator/klarna-instant-shopping-loading-indicator';

export default class KlarnaInstantShopping extends Plugin {
    /**
     * default plugin options
     *
     * @type {*}
     */
    static options = {
        buttonKey: '',
        environment: 'playground',
        countryIso: '',
        currencyIso: '',
        billingCountries: [],
        instanceId: '',
        klarnaLocale: '',
        libraryUrl: 'https://x.klarnacdn.net/instantshopping/lib/v1/lib.js',
        merchantUrls: {},
        orderLines: {},
        theme: {
            variation: 'dark',
            type: 'express'
        },
        actionUrls: {
            initiateSessionUrl: '',
            updateDataUrl: '',
            updateIdentificationUrl: '',
            updateShippingUrl: ''
        },
        selectors: {
            klarnaModalSelector: 'klarna-instant-shopping-fullscreen'
        },
        detailPageProductId: ''
    };

    static cartToken;

    init() {
        if (this.el) {
            try {
                this.httpClient = new (require('src/service/store-api-client.service').default)();
            } catch {
                this.httpClient = new HttpClient(window.accessKey, window.contextToken);
            }

            this._createScript();
        }
    }

    _createScript() {
        if (document.querySelectorAll(`script[src="${this.options.libraryUrl}"]`).length > 0) {
            this._onKlarnaLoad();

            return;
        }

        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = this.options.libraryUrl;

        script.addEventListener('load', this._onKlarnaLoad.bind(this), false);

        document.head.appendChild(script);
    }

    _registerKlarnaEvents() {
        const me = this;

        Klarna.InstantShopping.on('buy_button_clicked', (callbackData) => {
            me._initializeWidget(callbackData);
        }, { setup: { instance_id: me.options.instanceId } });

        Klarna.InstantShopping.on('shipping_updated', (callbackData) => {
            me._updateShipping(callbackData);
        }, { setup: { instance_id: me.options.instanceId } });

        Klarna.InstantShopping.on('identification_updated', (callbackData) => {
            me._updateIdentification(callbackData);
        }, { setup: { instance_id: me.options.instanceId } });

        Klarna.InstantShopping.on('session_initiated', (callbackData) => {
            me._initiateSession(callbackData);
        }, { setup: { instance_id: me.options.instanceId } });
    }

    _initializeWidget() {
        const me = this;
        const target = this.options.actionUrls.updateDataUrl;

        if (!target) {
            return;
        }

        this.showLoadingIndicator(window.document.body);

        this.httpClient.post(
            target,
            JSON.stringify({
                productId: this.options.detailPageProductId,
                productQuantity: this.getProductQuantity(this.options.detailPageProductId)
            }),
            (response) => {
                const responseData = JSON.parse(response);
                const merchantData = JSON.parse(responseData.merchant_data || '{}');

                responseData.setup = {
                    instance_id: me.options.instanceId
                };

                Klarna.InstantShopping.update(responseData, (updateResponse) => {
                    KlarnaInstantShoppingLoadingIndicator.remove(window.document.body);

                    // deactivates button and closes modal
                    if (!updateResponse.show_button) {
                        me.el.parentNode.removeChild(me.el);
                    }

                    me.cartToken = merchantData.klarna_cart_token;
                });
            }
        );
    }

    _updateIdentification(data) {
        const me = this;
        const target = this.options.actionUrls.updateIdentificationUrl;

        if (!target) {
            return;
        }

        this.showLoadingIndicator(window.document.body);

        this.httpClient.post(
            target,
            JSON.stringify({
                country: data.customer_country,
                postal_code: data.customer_postal_code,
                cartToken: me.cartToken
            }),
            (response) => {
                const responseData = JSON.parse(response);

                if (responseData.status && responseData.status === 'error') {
                    me.el.parentNode.removeChild(me.el);
                    KlarnaInstantShoppingLoadingIndicator.remove(window.document.body);
                    return;
                }

                responseData.setup = {
                    instance_id: me.options.instanceId
                };

                Klarna.InstantShopping.update(responseData, (updateResponse) => {
                    KlarnaInstantShoppingLoadingIndicator.remove(window.document.body);

                    // deactivates button and closes modal
                    if (!updateResponse.show_button) {
                        me.el.parentNode.removeChild(me.el);
                    }
                });
            }
        );
    }

    _updateShipping(data) {
        const me = this;
        const target = this.options.actionUrls.updateShippingUrl;

        if (!target) {
            return;
        }

        this.showLoadingIndicator(window.document.body);

        this.httpClient.post(
            target,
            JSON.stringify({
                shippingMethodId: data.customer_selected_shipping_option.reference,
                country: data.customer_country,
                postal_code: data.customer_postal_code,
                cartToken: me.cartToken
            }),
            (response) => {
                const responseData = JSON.parse(response);
                if (responseData.status && responseData.status === 'error') {
                    me.el.parentNode.removeChild(me.el);
                    KlarnaInstantShoppingLoadingIndicator.remove(window.document.body);
                    return;
                }

                const merchantData = JSON.parse(responseData.merchant_data || '{}');
                me.cartToken = merchantData.klarna_cart_token;

                responseData.setup = {
                    instance_id: me.options.instanceId
                };

                Klarna.InstantShopping.update(responseData, (updateResponse) => {
                    KlarnaInstantShoppingLoadingIndicator.remove(window.document.body);

                    // deactivates button and closes modal
                    if (!updateResponse.show_button) {
                        me.el.parentNode.removeChild(me.el);
                    }
                });
            }
        );
    }

    _initiateSession(data) {
        const me = this;
        const target = this.options.actionUrls.initiateSessionUrl;

        if (!target) {
            return;
        }

        if (undefined === me.cartToken) {
            setTimeout(() => { this._initiateSession(data); }, 250);

            return;
        }

        this.showLoadingIndicator(window.document.body);

        this.httpClient.post(
            target,
            JSON.stringify({
                country: data.customer_country,
                postal_code: data.customer_postal_code,
                cartToken: me.cartToken
            }),
            (response) => {
                const responseData = JSON.parse(response);
                if (responseData.status && responseData.status === 'error') {
                    me.el.parentNode.removeChild(me.el);
                    KlarnaInstantShoppingLoadingIndicator.remove(window.document.body);
                    return;
                }

                responseData.setup = {
                    instance_id: me.options.instanceId
                };

                Klarna.InstantShopping.update(responseData, (updateResponse) => {
                    KlarnaInstantShoppingLoadingIndicator.remove(window.document.body);

                    // deactivates button and closes modal
                    if (!updateResponse.show_button) {
                        me.el.parentNode.removeChild(me.el);
                    }
                });
            }
        );
    }

    _onKlarnaLoad() {
        const me = this;

        Klarna.InstantShopping.load(this.getKlarnaRequestData(), (response) => {
            if (response.show_button) {
                me.el.style.display = 'block';

                me._registerKlarnaEvents();
            } else {
                me.el.parentNode.removeChild(me.el);
            }
        });
    }

    showLoadingIndicator(element) {
        // klarna modal z-index is max int safe value by default
        const klarnaModal = document.getElementById(this.options.selectors.klarnaModalSelector);
        klarnaModal.style.zIndex = '1500';

        KlarnaInstantShoppingLoadingIndicator.create(element);
    }

    getKlarnaRequestData() {
        return {
            setup: {
                instance_id: this.options.instanceId,
                key: this.options.buttonKey,
                environment: this.options.environment,
                region: 'eu'
            },
            purchase_country: this.options.countryIso,
            purchase_currency: this.options.currencyIso,
            billing_countries: this.options.billingCountries,
            locale: this.options.klarnaLocale,
            merchant_urls: this.options.merchantUrls,
            order_lines: this.options.orderLines,
            styling: {
                theme: {
                    variation: this.options.theme.variation,
                    type: this.options.theme.type
                }
            }
        };
    }

    getProductQuantity(productId) {
        if (!productId) {
            return 1;
        }

        const quantityElement = document.querySelector(`select[name="lineItems[${productId}][quantity]"]`);

        if (quantityElement && parseInt(quantityElement.options[quantityElement.selectedIndex].value, 10) > 0) {
            return parseInt(quantityElement.options[quantityElement.selectedIndex].value, 10);
        }

        return 1;
    }
}
