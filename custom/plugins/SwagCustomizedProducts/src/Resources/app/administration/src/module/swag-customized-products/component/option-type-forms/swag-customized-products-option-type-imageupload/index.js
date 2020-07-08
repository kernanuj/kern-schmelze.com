import template from './swag-customized-products-option-type-imageupload.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-imageupload', 'swag-customized-products-option-type-base', {
    template,

    created() {
        if (this.option.typeProperties.maxFileSize === undefined) {
            this.$set(
                this.option.typeProperties,
                'maxFileSize',
                10
            );
        }

        if (this.option.typeProperties.maxCount === undefined) {
            this.$set(
                this.option.typeProperties,
                'maxCount',
                1
            );
        }
    },

    methods: {
        validateInput(value) {
            return this.checkRequired(value.typeProperties.maxFileSize)
                && this.checkRequired(value.typeProperties.maxCount);
        }
    }
});
