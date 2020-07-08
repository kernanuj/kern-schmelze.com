import template from './swag-customized-products-type-base.html.twig';

const { Component } = Shopware;
const { types } = Shopware.Utils;

Component.register('swag-customized-products-option-type-base', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        option: {
            type: Object,
            required: true
        },
        versionContext: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            activeItem: null
        };
    },

    computed: {
        optionPriceRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_option_price');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.registerWatcher();

            if (!this.checkRequired(this.option.percentageSurcharge)) {
                this.option.percentageSurcharge = 0;
            }
        },

        registerWatcher() {
            this.$watch('option', (value) => {
                this.$emit('option-valid', this.validateInput(value));
            }, {
                deep: true,
                immediate: true
            });

            return true;
        },

        validateInput(value) {
            return value !== null;
        },

        checkRequired(value) {
            return !types.isUndefined(value) && (
                types.isNumber(value) ||
                (types.isString(value) && value.length > 0)
            );
        },

        addPriceRule(ruleId) {
            if (!ruleId) {
                return;
            }

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

        removePriceRule(priceRuleId) {
            this.option.prices.remove(priceRuleId);
        },

        addPrice(price) {
            if (!this.option.price) {
                this.$set(this.option, 'price', []);
            }

            this.option.price.push(price);
        },

        replacePrice(price) {
            this.option.price = price;
        },

        changePercentageSurcharge(newValue) {
            this.option.percentageSurcharge = newValue;
        },

        changeTaxId(newValue) {
            this.option.taxId = newValue;
        }
    }
});
