const { Mixin } = Shopware;

Mixin.register('swag-customized-products-option', {
    methods: {
        translateOption(type) {
            return this.$tc(`swag-customized-products.optionTypes.${type}`);
        }
    }
});
