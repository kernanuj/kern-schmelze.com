import template from './swag-customized-products-option-type-numberfield.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-numberfield', 'swag-customized-products-option-type-base', {
    template,

    methods: {
        // Gets called by base class
        createdComponent() {
            if (!this.checkRequired(this.option.typeProperties.minValue)) {
                this.option.typeProperties.minValue = 0;
            }

            if (!this.checkRequired(this.option.typeProperties.maxValue)) {
                this.option.typeProperties.maxValue = 100;
            }

            if (!this.checkRequired(this.option.typeProperties.interval)) {
                this.option.typeProperties.interval = 1;
            }

            if (!this.checkRequired(this.option.typeProperties.defaultValue)) {
                this.option.typeProperties.defaultValue = 0;
            }

            this.$super('createdComponent');
        },

        validateInput(value) {
            return this.checkRequired(value.typeProperties.minValue)
                && this.checkRequired(value.typeProperties.maxValue)
                && this.checkRequired(value.typeProperties.interval)
                && this.checkRequired(value.typeProperties.defaultValue);
        }
    }
});
