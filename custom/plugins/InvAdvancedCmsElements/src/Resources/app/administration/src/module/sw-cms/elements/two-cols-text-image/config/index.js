import template from './sw-cms-el-config-two-cols-text-image.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-two-cols-text-image', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('two-cols-text-image');
        }
    }
});
