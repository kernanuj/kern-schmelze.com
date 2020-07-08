import template from './swag-customized-products-option-type-textfield.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-textfield', 'swag-customized-products-option-type-base', {
    template,

    methods: {
        // Gets called by base class
        createdComponent() {
            if (!this.checkRequired(this.option.typeProperties.minLength)) {
                this.option.typeProperties.minLength = 0;
            }

            if (!this.checkRequired(this.option.typeProperties.maxLength)) {
                this.option.typeProperties.maxLength = 1000;
            }

            this.$super('createdComponent');
        },

        validateInput(value) {
            return this.checkRequired(value.typeProperties.minLength)
                && this.checkRequired(value.typeProperties.maxLength);
        }
    }
});
