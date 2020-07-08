import template from './swag-customized-products-option-type-surcharges.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-customized-products-option-type-surcharges', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        value: {
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
            displayMaintainCurrenciesModal: false,
            systemDefaultCurrency: {},
            advancedTooltip: '',
            currencies: [],
            color: ''
        };
    },

    computed: {
        defaultPrice() {
            return this.value.price.find(price => {
                return price.currencyId === Shopware.Context.app.systemCurrencyId;
            });
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },

        relativeTooltip() {
            return this.$tc('swag-customized-products.optionDetailModal.optionType.surcharges.relativeSurchargesTooltip');
        }
    },

    watch: {
        'value.relativeSurcharge': {
            handler(value) {
                // Reset advanced surcharges option to false if it was enabled without a taxId and
                // relative surcharges get disabled
                if (!value && !this.value.taxId) {
                    this.value.advancedSurcharge = false;
                }

                if (!value && !this.value.taxId) {
                    this.advancedTooltip =
                        this.$tc('swag-customized-products.optionDetailModal.optionType.surcharges.selectTaxRateTooltip');
                    return;
                }

                this.advancedTooltip =
                    this.$tc('swag-customized-products.optionDetailModal.optionType.surcharges.advancedSurchargesTooltip');
            },
            immediate: true
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!this.value.price) {
                this.$emit('price-add', {
                    net: 0,
                    gross: 0,
                    linked: true,
                    currencyId: Shopware.Context.app.systemCurrencyId
                });
            }

            this.currencyRepository.search(new Criteria(), Shopware.Context.api).then(items => {
                this.currencies = items;
                this.systemDefaultCurrency = items.get(Shopware.Context.app.systemCurrencyId);
            });
        },

        onMaintainCurrenciesClose(price) {
            this.value.price = price;
            this.displayMaintainCurrenciesModal = !this.displayMaintainCurrenciesModal;
        }
    }
});
