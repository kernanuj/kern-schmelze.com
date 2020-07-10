/* eslint-disable import/no-unresolved */
import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class SwagCustomizedProductsFormValidator extends Plugin {
    static options = {
        inputFieldsSelector: 'input, textarea',
        buyButtonSelector: '#productDetailPageBuyProductForm .btn-buy'
    };

    init() {
        this.parentEl = this.el.parentNode;
        this._registerEventListeners();
    }

    /**
     * @returns {void}
     */
    _registerEventListeners() {
        const inputFields = DomAccess.querySelectorAll(this.parentEl, this.options.inputFieldsSelector, false);
        this.$emitter.subscribe(
            'change',
            this._onFormChange.bind(this)
        );

        inputFields.forEach((field) => {
            field.addEventListener(
                'invalid',
                this._onInputInvalid.bind(this)
            );
        });

        // Initially check for required fields
        this._onFormChange();
    }

    /**
     * Validates if all required input fields are filled
     * @private
     */
    _onFormChange() {
        const buyButton = DomAccess.querySelector(this.parentEl, this.options.buyButtonSelector);
        const inputFields = DomAccess.querySelectorAll(this.parentEl, this.options.inputFieldsSelector, false);
        const selectionValidation = {};
        const requiredSimpleFieldsFilled = Array.from(inputFields).reduce((acc, field) => {
            // Collect validity information of every selection input group
            const selectionOptionId = field.dataset.swagCustomizedProductsSelectionRequired;
            if (selectionOptionId !== undefined) {
                selectionValidation[selectionOptionId] = selectionValidation[selectionOptionId] || field.checked;
                if (field.hasAttribute('required')) {
                    field.removeAttribute('required');
                }
            }

            if (!acc || !field.required) {
                return acc;
            }

            if (field.required && field.value.length) {
                return acc;
            }

            acc = false;
            return acc;
        }, true);

        const requiredSelectionFieldsFilled = Object.values(selectionValidation).every(groupValid => groupValid);
        const requiredFieldsFilled = requiredSimpleFieldsFilled && requiredSelectionFieldsFilled;

        if (requiredFieldsFilled) {
            if (buyButton.hasAttribute('disabled')) {
                buyButton.removeAttribute('disabled');
            }

            return;
        }

        buyButton.setAttribute('disabled', 'disabled');
    }

    _onInputInvalid(event) {
        const element = event.target.closest('.collapse');

        /* eslint-env jquery */
        $(element).collapse('show');

        // Fire event
        this.$emitter.publish('invalid', {
            element: event.target
        });
    }
}
