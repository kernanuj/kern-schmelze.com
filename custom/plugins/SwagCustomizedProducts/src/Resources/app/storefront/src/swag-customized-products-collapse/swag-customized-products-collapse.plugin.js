/* eslint-disable import/no-unresolved */
import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

/**
 * Controlls the auto collapsing for valid options
 *
 * @class
 */
export default class SwagCustomizedProductsCollapsingValidOptions extends Plugin {
    /**
     * Plugin options
     *
     * @type {{collapsingElementsSelector: string, inputFieldsSelector: string}}
     */
    static options = {
        collapsingElementsSelector: '.collapsingCustomizedProductsOption',
        inputFieldsSelector: 'input, textarea'
    };

    /**
     * Initializes Components and applies EventListeners on inputFields
     *
     * @returns {void}
     */
    init() {
        this.configurator = this.el;
        this.optionElements = DomAccess.querySelectorAll(this.configurator, this.options.collapsingElementsSelector);
        this.currentClickPromise = null;
        const inputFields = DomAccess.querySelectorAll(this.configurator, this.options.inputFieldsSelector);

        inputFields.forEach((inputField) => {
            inputField.addEventListener('focus', this.onInputBlur.bind(this));
            inputField.addEventListener('blur', this.onInputClick.bind(this));
        });
    }

    /**
     * OnBlur event listener of option inputs, which ensures that the `click` event was emitted before this
     *
     * @event blur
     * @returns {void}
     */
    onInputBlur(event) {
        if (this.currentClickPromise === null) {
            return;
        }

        this.currentClickPromise.then(() => {
            this.collapseValid(event);
        });
    }

    /**
     * OnClick event listener of option inputs
     *
     * @event click
     * @returns {void}
     */
    onInputClick(event) {
        this.updateLastEventOption(event);
    }

    /**
     * Collapses the option, if input is valid and you're not staying in the current option
     *
     * @param {Event} event
     * @returns {void}
     */
    collapseValid(event) {
        const eventOption = event.target.closest(this.options.collapsingElementsSelector);

        if (this.lastEventOption === eventOption) {
            return;
        }

        this.optionElements.forEach((option) => {
            const innerInputFields = DomAccess.querySelectorAll(option, this.options.inputFieldsSelector);
            const hasValidInput = Array.from(innerInputFields).some(field => field.validity.valid);

            if (hasValidInput && option === this.lastEventOption) {
                /* eslint-env jquery */
                $(option).collapse('hide');
            }
        });
    }

    /**
     * Ensure to not close e.g. a multi-select after the second selection, the clicked option must be saved
     *
     * @param {Event} event
     * @returns {void}
     */
    updateLastEventOption(event) {
        this.currentClickPromise = new Promise((resolve) => {
            this.lastEventOption = event.target.closest(this.options.collapsingElementsSelector);
            resolve();
        });
    }
}
