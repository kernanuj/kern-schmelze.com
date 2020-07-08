import template from './swag-customized-products-option-tree-content.html.twig';
import './swag-customized-products-option-tree-content.scss';

const { Component } = Shopware;
const { mapApiErrors } = Shopware.Component.getComponentHelper();

Component.register('swag-customized-products-option-tree-content', {
    template,

    mixins: [
        'placeholder'
    ],

    inject: [
        'repositoryFactory'
    ],

    props: {
        option: {
            type: Object,
            required: true
        },

        data: {
            type: Object,
            required: false,
            default() {
                return null;
            }
        },

        versionContext: {
            type: Object,
            required: true
        }
    },

    computed: {
        optionPriceRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_option_price');
        },

        optionValuesPriceRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_option_value_price');
        },

        visibleValue() {
            if (this.data.isRoot) {
                return this.option;
            }

            return this.data;
        },

        multiSelectToolTip() {
            if (this.option.templateExclusionConditions.length > 0) {
                return this.$tc(
                    'swag-customized-products.optionDetailModal.optionType.select.isMultiSelectTooltip.disabled'
                );
            }

            return this.$tc('swag-customized-products.optionDetailModal.optionType.select.isMultiSelectTooltip.enabled');
        },

        ...mapApiErrors('data', ['displayName'])
    },

    methods: {
        changeMultiSelect(value) {
            if (value === true) {
                this.$set(this.option.typeProperties, 'isDropDown', false);
            }

            this.$set(this.option.typeProperties, 'isMultiSelect', value);
        },

        addPriceRule(ruleId) {
            if (!ruleId) {
                return;
            }

            if (this.data.isRoot) {
                this.addOptionPriceRule(ruleId);
                return;
            }

            this.addOptionValuePriceRule(ruleId);
        },

        addOptionPriceRule(ruleId) {
            const newOptionPrice = this.optionPriceRepository.create(this.versionContext);

            newOptionPrice.ruleId = ruleId;
            newOptionPrice.templateOptionId = this.option.id;
            newOptionPrice.templateOptionVersionId = this.versionContext.versionId;
            newOptionPrice.price = [
                {
                    currencyId: Shopware.Context.app.systemCurrencyId,
                    linked: true,
                    gross: 0,
                    net: 0
                }
            ];

            this.option.prices.push(newOptionPrice);
        },

        addOptionValuePriceRule(ruleId) {
            const newOptionValuePrice = this.optionValuesPriceRepository.create(this.versionContext);

            newOptionValuePrice.ruleId = ruleId;
            newOptionValuePrice.templateOptionValueId = this.data.id;
            newOptionValuePrice.templateOptionValueVersionId = this.versionContext.versionId;
            newOptionValuePrice.price = [
                {
                    currencyId: Shopware.Context.app.systemCurrencyId,
                    linked: true,
                    gross: 0,
                    net: 0
                }
            ];

            this.data.prices.push(newOptionValuePrice);
        },

        addPrice(price) {
            if (!this.visibleValue.price) {
                this.$set(this.visibleValue, 'price', []);
            }

            this.visibleValue.price.push(price);
        },

        replacePrice(price) {
            this.visibleValue.price = price;
        },

        changePercentageSurcharge(newValue) {
            this.visibleValue.percentageSurcharge = newValue;
        },

        changeTaxId(newValue) {
            this.visibleValue.taxId = newValue;
        },

        warn(msg) {
            Shopware.Utils.debug.warn('swag-customized-products-option-tree', msg);
        },

        removePriceRule(ruleId) {
            if (!ruleId) {
                return;
            }

            if (this.data.isRoot) {
                this.option.prices.remove(ruleId);
                return;
            }

            this.data.prices.remove(ruleId);
        }
    }
});
