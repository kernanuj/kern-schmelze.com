/* eslint-disable import/no-unresolved */
/* global Klarna */

import Plugin from 'src/plugin-system/plugin.class';
import PageLoadingIndicatorUtil from 'src/utility/loading-indicator/page-loading-indicator.util';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import DomAccess from 'src/helper/dom-access.helper';

export default class KlarnaPayments extends Plugin {
    init() {
        this._showElement('klarnaConfirmFormSubmit');

        const configuration = document.getElementById('klarna-configuration');

        if (!configuration) {
            return;
        }

        this.clientToken = configuration.getAttribute('data-client-token');
        this.paymentCategory = configuration.getAttribute('data-klarna-code');
        this.customerData = JSON.parse(configuration.getAttribute('data-customer-data'));

        if (this.paymentCategory) {
            this._disableSubmitButton();
        }

        this._createScript();
    }

    _createScript() {
        const url = 'https://x.klarnacdn.net/kp/lib/v1/api.js';

        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = url;

        script.addEventListener('load', this._handleScriptLoaded.bind(this), false);

        document.head.appendChild(script);
    }

    _handleScriptLoaded() {
        try {
            Klarna.Payments.init({
                client_token: this.clientToken
            });
        } catch (e) {
            this._hideElement('klarnaPaymentsContainer');
            this._showElement('klarnaUnavailableError');

            this._disableSubmitButton();

            return;
        }

        const me = this;

        if (this.paymentCategory) {
            const klarnaPayment = DomAccess.querySelector(document, '.klarna-payment');
            ElementLoadingIndicatorUtil.create(klarnaPayment);

            me._hideElement('klarnaPaymentsContainer');
            me._emptyElement('klarnaPaymentsContainer');

            me._disableSubmitButton();

            try {
                Klarna.Payments.load({
                    container: '#klarnaPaymentsContainer',
                    payment_method_category: this.paymentCategory
                }, (result) => {
                    if (!result.show_form) {
                        me._hideElement('klarnaPaymentsContainer');
                        me._showElement('klarnaUnavailableError');
                    } else {
                        me._showElement('klarnaPaymentsContainer');
                        me._hideElement('klarnaUnavailableError');

                        me._enableSubmitButton();
                    }
                    ElementLoadingIndicatorUtil.remove(klarnaPayment);
                });
            } catch (e) {
                me._hideElement('klarnaPaymentsContainer');
                me._showElement('klarnaUnavailableError');
            }
        }

        this._handlePaymentMethodModal();
        this._registerEvents();
    }

    _registerEvents() {
        const me = this;

        document
            .getElementById('confirmOrderForm')
            .addEventListener('submit', me._handleOrderSubmit.bind(this));

        const inputFields = document.querySelectorAll('[name=\'paymentMethodId\']');

        Array.prototype.forEach.call(inputFields, (radio) => {
            radio.addEventListener('change', me._handlePaymentMethodChange.bind(me));
        });
    }

    _handlePaymentMethodChange(event) {
        this._hideElements('klarnaPaymentsContainerModal');

        const code = this.getKlarnaCodeFromPaymentMethod(event.target.value);

        if (!code) {
            return;
        }

        this._showElement(`klarnaPaymentsContainerModal${event.target.value}`);
    }

    _handlePaymentMethodModal() {
        const me = this;
        const paymentMethods = document.querySelectorAll('.klarna-payment-method');

        Array.prototype.forEach.call(paymentMethods, (paymentMethod) => {
            const id = paymentMethod.getAttribute('id');
            const code = me.getKlarnaCodeFromPaymentMethod(id);

            try {
                Klarna.Payments.load({
                    container: `#klarnaPaymentsContainerModal${id}`,
                    payment_method_category: code,
                    instance_id: id
                }, (res) => {
                    if (!res.show_form) {
                        me._hideElement(id);
                    }
                });
            } catch (e) {
                me._hideElement(id);
            }
        });
    }

    _hideElements(classname) {
        const elements = document.getElementsByClassName(classname);

        Array.prototype.forEach.call(elements, (element) => {
            element.hidden = true;
        });
    }

    _hideElement(element) {
        const container = document.getElementById(element);

        if (container) {
            container.hidden = true;
        }
    }

    _showElement(element) {
        const container = document.getElementById(element);

        if (container) {
            container.hidden = false;
        }
    }

    _emptyElement(element) {
        const container = document.getElementById(element);

        if (container) {
            container.innerHTML = '';
        }
    }

    getKlarnaCodeFromPaymentMethod(paymentMethod) {
        const code = document.getElementById(paymentMethod);

        if (code) {
            return code.getAttribute('data-klarna-code');
        }

        return '';
    }

    _disableSubmitButton() {
        const button = document.getElementById('confirmFormSubmit');

        if (button) {
            button.setAttribute('disabled', 'disabled');
        }
    }

    _enableSubmitButton() {
        const button = document.getElementById('confirmFormSubmit');

        if (button) {
            button.removeAttribute('disabled');
        }
    }

    _moveKlarnaModalContainer(target) {
        const container = document.getElementById('klarnaModalContainer');

        target.parentElement.appendChild(container);
    }

    _handleOrderSubmit(event) {
        if (!this.paymentCategory) {
            return;
        }

        if (this.authorization) {
            return;
        }

        event.preventDefault();

        PageLoadingIndicatorUtil.create();

        this._createAuthorization();
    }

    _createAuthorization() {
        const me = this;

        try {
            Klarna.Payments.authorize(
                {
                    auto_finalize: true,
                    payment_method_category: this.paymentCategory
                },
                me.customerData,
                (result) => {
                    if (!result.show_form) {
                        me._hideElement('klarnaPaymentsContainer');
                        me._showElement('klarnaUnavailableError');
                    }

                    if (result.approved) {
                        me._saveAuthorization(result);
                        me._submitConfirmForm();
                    } else {
                        PageLoadingIndicatorUtil.remove();
                    }
                }
            );
        } catch (e) {
            me._hideElement('klarnaPaymentsContainer');
            me._showElement('klarnaUnavailableError');
        }
    }

    _saveAuthorization(result) {
        this.authorization = result.authorization_token;

        this._addAuthorizationToForm(this.authorization);
    }

    _addAuthorizationToForm(authorization) {
        const element = document.getElementById('klarnaAuthorizationToken');

        if (element) {
            element.value = authorization;
        }
    }

    _submitConfirmForm() {
        const form = document.getElementById('confirmOrderForm');

        if (form) {
            form.submit();
        }
    }
}