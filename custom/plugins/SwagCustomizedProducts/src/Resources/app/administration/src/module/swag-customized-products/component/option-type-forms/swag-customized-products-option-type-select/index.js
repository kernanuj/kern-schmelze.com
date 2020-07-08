import template from './swag-customized-products-option-type-select.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-select', 'swag-customized-products-option-type-base-tree', {
    template,

    methods: {
        changeIsDropDown(value) {
            this.$set(this.option.typeProperties, 'isDropDown', value);
        },

        setActiveItem(item) {
            this.activeItem = item;
        }
    }
});
