import template from './swag-customized-products-option-type-advanced-surcharges.html.twig';
import './swag-customized-products-option-type-advanced-surcharges.scss';

const { Component } = Shopware;

Component.register('swag-customized-products-option-type-advanced-surcharges', {
    template,

    props: {
        prices: {
            required: true
        },

        currencies: {
            type: Array,
            required: true
        },

        taxId: {
            required: true
        },

        relativeSurcharge: {
            required: false,
            default: false
        }
    },

    data() {
        return {
            isLoading: false
        };
    },

    computed: {
        defaultCurrency() {
            return Shopware.Context.app.systemCurrencyId;
        },

        pricesColumns() {
            const currencies = this.currencies.map((currency) => {
                return {
                    property: currency.id,
                    label: currency.name,
                    rawData: true
                };
            });

            const currenciesSorted = currencies.sort(a => (a.property === this.defaultCurrency ? -1 : 1));
            const ruleId = {
                property: 'ruleId',
                // eslint-disable-next-line max-len
                label: this.$tc('swag-customized-products.optionDetailModal.optionType.advancedSurcharges.columnTitles.rule'),
                inlineEdit: true,
                rawData: true,
                primary: true,
                width: '250px'
            };

            if (this.relativeSurcharge) {
                return [
                    ruleId,
                    {
                        property: 'percentageSurcharge',
                        // eslint-disable-next-line max-len
                        label: this.$tc('swag-customized-products.optionDetailModal.optionType.surcharges.relativeLabel'),
                        inlineEdit: true,
                        rawData: true,
                        primary: true,
                        width: '250px'
                    }
                ];
            }

            return [
                ruleId,
                ...currenciesSorted
            ];
        }
    },

    methods: {
        addOptionPrice(ruleId) {
            this.$emit('prices-add', ruleId);
        },

        removeOptionPrice(optionPrice) {
            this.$emit('prices-remove', optionPrice.id);
        },

        isPriceFieldInherited(rule, currency) {
            return !rule.price.some((price) => price.currencyId === currency.id);
        },

        onInheritanceRestore(rule, currency) {
            rule.price = rule.price.filter((price) => price.currencyId !== currency.id);
        },

        onInheritanceRemove(rule, currency) {
            // create new price based on the default price
            const defaultPrice = this.findDefaultPriceOfRule(rule);

            const newPrice = {
                currencyId: currency.id,
                gross: this.convertPrice(defaultPrice.gross, currency),
                linked: defaultPrice.linked,
                net: this.convertPrice(defaultPrice.net, currency)
            };

            rule.price.push(newPrice);
        },

        convertPrice(value, currency) {
            const calculatedPrice = value * currency.factor;
            const priceRounded = calculatedPrice.toFixed(currency.decimalPrecision);

            return Number(priceRounded);
        },

        findDefaultPriceOfRule(rule) {
            return rule.price.find((price) => price.currencyId === Shopware.Context.app.systemCurrencyId);
        },

        isRuleSelected(ruleId) {
            return this.prices.some((optionPrice) => optionPrice.ruleId === ruleId);
        }
    }
});
