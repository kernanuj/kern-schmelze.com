import template from './swag-customized-products-option-type-colorselect.html.twig';

const { Component } = Shopware;
const { mapApiErrors } = Shopware.Component.getComponentHelper();

Component.extend('swag-customized-products-option-type-colorselect', 'swag-customized-products-option-type-base-tree', {
    template,

    computed: {
        ...mapApiErrors('activeItem', ['value._value'])
    },

    methods: {
        setActiveItem(item) {
            this.activeItem = item;
        }
    }
});
