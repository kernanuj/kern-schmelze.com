import './swag-customized-products-condition-tree-node.scss';
import template from './swag-customized-products-condition-tree-node.html.twig';

const { Component, Utils } = Shopware;
const { Criteria } = Shopware.Data;
const { mapApiErrors } = Shopware.Component.getComponentHelper();

Component.register('swag-customized-products-condition-tree-node', {
    template,

    inject: [
        'repositoryFactory',
        'SwagCustomizedProductsUiLanguageContextHelper'
    ],

    props: {
        templateId: {
            type: String,
            required: true
        },
        context: {
            type: Object,
            required: true
        },
        condition: {
            type: Object,
            required: true
        },
        index: {
            type: Number,
            required: true
        },
        optionCriteria: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            loadedOption: null,
            uiLanguageContext: null
        };
    },

    computed: {
        optionRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_option');
        },

        operatorRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_exclusion_operator');
        },

        operatorCriteria() {
            const operatorCriteria = new Criteria();

            if (!this.loadedOption) {
                return operatorCriteria;
            }

            operatorCriteria.addFilter(
                Criteria.equals('templateOptionType', this.loadedOption.type)
            );
            return operatorCriteria;
        },

        ...mapApiErrors('condition', ['templateOptionId', 'templateExclusionOperatorId'])
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            return this.SwagCustomizedProductsUiLanguageContextHelper().then((language) => {
                const context = Shopware.Context.api;
                context.languageId = language;
                this.uiLanguageContext = context;
            });
        },

        onOptionChange(id, option) {
            // Reset operators to prevent misconfiguration
            this.condition.templateExclusionOperatorId = null;

            if (!Utils.types.isEmpty(this.conditionTemplateOptionIdError)) {
                Shopware.State.dispatch(
                    'error/removeApiError',
                    { expression: this.conditionTemplateOptionIdError.selfLink }
                );
            }

            if (!option) {
                return;
            }

            this.loadedOption = option;
        },

        onOperatorChange() {
            if (!Utils.types.isEmpty(this.conditionTemplateExclusionOperatorIdError)) {
                Shopware.State.dispatch(
                    'error/removeApiError',
                    { expression: this.conditionTemplateExclusionOperatorIdError.selfLink }
                );
            }
        },

        onOptionLoaded(item) {
            this.loadedOption = item;
        }
    }
});
