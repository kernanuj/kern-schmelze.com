/* eslint-disable import/no-unresolved */
import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import optionTypeManager from './option-types';

/**
 * Helper method which terminates the absolute position of an element on the page.
 *
 * @params {Element} element
 * @returns {{top: number, left: number}}
 */
const getOffset = (element) => {
    const rect = element.getBoundingClientRect();
    return {
        left: rect.left + window.scrollX,
        top: rect.top + window.scrollY
    };
};

/**
 * Exclusion list validator which reads an exclusion tree from the DOM element and displays
 * a list of all violations.
 *
 * @class
 */
export default class SwagCustomizedProductsExclusionListValidation extends Plugin {
    /**
     * Plugin options
     *
     * @type {{idPrefix: string, formControlSelector: string, scrollOffset: number}}
     */
    static options = {
        formControlSelector: '.swag-customized-products-form-control',
        idPrefix: 'swag-customized-products-option-id-',
        buyButtonSelector: '#productDetailPageBuyProductForm .btn-buy',
        scrollOffset: 80,
        collapsePanelSelector: '.collapse'
    };

    /**
     * Initializes the plugin.
     *
     * @constructor
     * @returns {void}
     */
    init() {
        this.parentEl = this.el.closest('form');
        this.isStepByStepActive = DomAccess.hasAttribute(this.el, 'data-swag-customized-product-step-by-step');
        this.stepByStepModePlugin = null;

        if (this.isStepByStepActive) {
            this.stepByStepModePlugin = window.PluginManager.getPluginInstanceFromElement(
                this.el,
                'SwagCustomizedProductsStepByStepWizard'
            );
        }

        this.violationHolderElement = DomAccess.querySelector(
            this.parentEl,
            '.swag-customized-products__violation-list-holder'
        );
        this.violationTemplate = this.violationHolderElement.querySelector('template');
        this.violationTemplateString = this.violationTemplate.content.cloneNode(true).children[0].outerHTML;
        this.buyButton = DomAccess.querySelector(document, this.options.buyButtonSelector);

        this.isViolation = false;

        // Remove template-tag from violation holder
        this.violationTemplate.parentNode.removeChild(this.violationTemplate);

        /** @type {{excludeItem: (string), headline: (string)}} */
        this.translations = {
            excludeItem: DomAccess.getDataAttribute(
                this.el,
                'swag-exclusion-translation-excludeitem'
            ),
            headline: DomAccess.getDataAttribute(
                this.el,
                'swag-exclusion-translation-headline'
            )
        };

        const exclusions = DomAccess.getDataAttribute(this.el, 'data-swag-exclusion-list-validation-options');

        const elements = this.collectInputElements(
            this.el,
            this.options
        );
        this.exclusionTree = SwagCustomizedProductsExclusionListValidation.mergeExclusionTreeWithElements(
            exclusions,
            elements
        );

        this.optionTypeManager = optionTypeManager(this.$emitter);

        this.onInputChange();
        this._registerEventListeners();
    }

    /**
     * Registers the necessary event listeners.
     *
     * @private
     * @returns {void}
     */
    _registerEventListeners() {
        this.parentEl.addEventListener('change', this.onInputChange.bind(this), false);

        this.violationHolderElement.addEventListener('click', this.onViolationHolderElementClick.bind(this));
    }

    /**
     * Event listener which will be triggered when the user clicks into the violation holder element. The method scrolls
     * to the input element which has a violation.
     *
     * @param {Event} event
     * @returns {void}
     */
    onViolationHolderElementClick(event) {
        const clickTarget = event.target;
        if (!clickTarget.matches('.entry__link')) {
            return;
        }

        event.preventDefault();

        const targetId = DomAccess.getDataAttribute(clickTarget, 'data-target');
        const target = DomAccess.querySelector(this.el, `#${targetId}`);

        if (this.isStepByStepActive) {
            this.switchPageInStepByStep(target);
        }

        const collapsePanel = target.closest(this.options.collapsePanelSelector);
        if (collapsePanel && !this.isStepByStepActive) {
            /* eslint-env jquery */
            $(collapsePanel).collapse('show');
        }

        const { top } = getOffset(target);
        window.scrollTo({
            top: top - this.options.scrollOffset,
            left: 0,
            behavior: 'smooth'
        });
    }

    /**
     * When the step by step mode is enabled, the method switches the page which contains the provided element
     * @param {Element} element
     * @returns {Boolean}
     */
    switchPageInStepByStep(element) {
        const plugin = this.stepByStepModePlugin;
        const parent = element.closest('.swag-customized-products__item');

        const pageConfig = plugin.navigationEntries.find((item) => {
            return item.pageEl.isSameNode(parent);
        });

        if (!pageConfig) {
            return false;
        }

        plugin.transitionToPage.call(plugin, pageConfig.pageNum + 1);

        return true;
    }

    /**
     * Event handler which will fire when the user changes any input element in the buybox form.
     *
     * @event change
     * @returns {void}
     */
    onInputChange() {
        const violations = this.buildViolations();
        this.isViolation = violations;

        if (violations) {
            this.$emitter.publish('buyButtonDisable', true);
            this.buyButton.setAttribute('disabled', 'disabled');
            return;
        }

        this.buyButton.removeAttribute('disabled');
        this.$emitter.publish('buyButtonDisable', false);
    }

    /**
     * Finds child elements matching the selector in the provided option object from the provided parent element.
     *
     * The method extracts the UUID from the element and replaces the prefix with nothing to get the option ID.
     *
     * @static
     * @param {Node} parentEl
     * @param {{idPrefix: string, formControlSelector: string}} options
     * @returns {{id: String, element: Node}[]}
     */
    collectInputElements(parentEl, options) {
        const elements = Array.from(DomAccess.querySelectorAll(parentEl, options.formControlSelector));

        return elements.map((element) => {
            const id = this.extractIdFromElement(element);
            const defaultValue = element.defaultValue;

            return {
                element,
                id,
                defaultValue
            };
        });
    }

    /**
     * Merges input elements to the exclusion tree and resolves the excludes to the elements which
     *
     * @static
     * @param {Object} exclusions
     * @param {{id: String, element: Element}[]} elements
     * @returns {Map<String, Array<{id: String, type: String, operator: Object, element: Element}>>}
     */
    static mergeExclusionTreeWithElements(exclusions, elements) {
        if (exclusions.length <= 0) {
            return new Map();
        }

        /**
         * Helper method which finds an element by ID.
         *
         * @param {String} id
         * @returns {Element|undefined}
         */
        const findElementById = (id) => {
            const { element } = elements.find((item) => {
                return item.id === id;
            });

            return element;
        };

        return exclusions.reduce((accumulator, exclusion) => {
            let id = null;

            exclusion = exclusion.reduce((acc, condition) => {
                if (id === null) {
                    id = condition.id;
                }

                condition.element = findElementById(condition.id);

                acc.push(condition);

                return acc;
            }, []);

            accumulator.set(id, exclusion);

            return accumulator;
        }, new Map());
    }

    /**
     * Builds the violations according to the current configuration.
     *
     * @returns {Boolean}
     */
    buildViolations() {
        const exclusions = this.exclusionTree;

        let violations = Array.from(exclusions).map(([, exclusion]) => {
            const allConditionsApplied = exclusion.reduce((accumulator, condition) => {
                const { element, operator, type } = condition;

                // Once the accumulator is false, we don't have to check the other element states
                if (!accumulator) {
                    return accumulator;
                }

                accumulator = this.validateElementState(element, operator.type, type);

                return accumulator;
            }, true);

            return {
                violationFound: allConditionsApplied,
                elements: exclusion
            };
        });

        // Filter out exclusions that don't violate
        violations = violations.filter((item) => {
            return item.violationFound === true;
        });

        // No violations found, reset violation list
        if (!violations || violations.length <= 0) {
            SwagCustomizedProductsExclusionListValidation.updateViolationListDisplay(
                [],
                this.violationHolderElement,
                this.translations,
                this.violationTemplateString
            );

            return false;
        }

        const violatingElements = violations.map((violation) => {
            return violation.elements;
        });

        const violationListElements = violatingElements.map((violatingExclusion) => {
            // Clone array to loose reference
            const conditions = [...violatingExclusion];
            const firstCondition = conditions.shift();
            const labelText = SwagCustomizedProductsExclusionListValidation.findLabelForFormElement(firstCondition.element);

            const excludedElements = conditions.map((condition) => {
                return {
                    labelText: SwagCustomizedProductsExclusionListValidation.findLabelForFormElement(condition.element),
                    element: condition.element
                };
            });

            return { element: firstCondition.element, labelText, excludedElements };
        });

        SwagCustomizedProductsExclusionListValidation.updateViolationListDisplay(
            violationListElements,
            this.violationHolderElement,
            this.translations,
            this.violationTemplateString
        );

        return true;
    }

    /**
     * Extracts the actual element id from the DOM id. It removes the ID prefix.
     *
     * @param {Node} element
     * @returns {String}
     */
    extractIdFromElement(element) {
        const id = element.id;
        if (!id || !id.length) {
            return '';
        }

        return id.replace(this.options.idPrefix, '');
    }

    /**
     * Helper method which validates an element state.
     *
     * @param {Node} element
     * @param {String} operator
     * @param {String} type
     * @returns {Boolean}
     */
    validateElementState(element, operator, type) {
        return this.optionTypeManager.call(
            this,
            this.optionTypeManager.has(type) ? type : 'default',
            'validate',
            { element, operator, type }
        );
    }

    /**
     * Extracts the label text from the label element associated with the provided form element.
     *
     * @param {Element} element
     * @returns {string}
     */
    static findLabelForFormElement(element) {
        const parentElement = element.closest('.swag-customized-products-option', false);
        const labelEl = parentElement.querySelector('.swag-customized-products-option__title');

        return labelEl.innerText.trim();
    }

    /**
     * Renders the violation list.
     *
     * @static
     * @param {Array} violationList
     * @param {Element} renderToElement
     * @param {{excludeItem: (string), headline: (string)}} translations
     * @returns {boolean}
     */
    static updateViolationListDisplay(violationList, renderToElement, translations, template) {
        /**
         * Helper Method which formats the exclude string.
         *
         * @param {String} item
         * @param {String} excludedElements
         * @returns {string}
         */
        const formatExcludeString = (item, excludedElements) => {
            let translation = translations.excludeItem.replace('%1', item);
            translation = translation.replace('%2', excludedElements);

            return translation;
        };

        const renderListItems = (items) => {
            return items.map((item) => {
                let excludedElements = item.excludedElements.map((excludedItem) => {
                    const elementId = excludedItem.element.id;
                    return `<li>
                        <strong class="entry__link" data-target="${elementId}">
                            ${excludedItem.labelText}
                        </strong>
                    </li>`;
                });
                excludedElements = `<ul class="excluded-element-list">${excludedElements.join('')}</ul>`;

                // eslint-disable-next-line max-len
                return `<li class="violation-list__entry">${formatExcludeString(`<strong class="entry__link" data-target="${item.element.id}">${item.labelText}</strong>`, excludedElements)}</li>`;
            }).join('');
        };

        // Reset error message
        if (!violationList || violationList.length <= 0) {
            renderToElement.innerHTML = '';
            return false;
        }

        const content = `
            <ul class="violation-list">
                ${renderListItems(violationList)}
            </ul>
        `;

        template = template.replace('%1', translations.headline);
        template = template.replace('%2', content);

        renderToElement.innerHTML = template;

        return true;
    }
}
