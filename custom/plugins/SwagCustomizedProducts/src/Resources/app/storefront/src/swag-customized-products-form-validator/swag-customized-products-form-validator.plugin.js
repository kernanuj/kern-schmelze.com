/* eslint-disable import/no-unresolved */
import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class SwagCustomizedProductsFormValidator extends Plugin {
    static options = {
        dataselector: 'swagCustomizedProductsIsRequired',
        inputFieldsSelector: 'input, textarea'
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
    }

    _onFormChange(event) {
        const dataselector = this.options.dataselector;
        const optionId = event.target.dataset[dataselector];

        if (optionId) {
            const fields = [...DomAccess.querySelectorAll(
                this.parentEl,
                `[data-swag-customized-products-is-required="${optionId}"]`,
                false
            )];

            const isOptionValid = fields.some((item) => item.checked);

            fields.forEach((item) => {
                item.required = !isOptionValid;
            });
        }
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
