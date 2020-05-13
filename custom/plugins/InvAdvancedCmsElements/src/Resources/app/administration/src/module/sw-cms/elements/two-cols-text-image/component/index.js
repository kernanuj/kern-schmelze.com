import template from './sw-cms-el-two-cols-text-image.html.twig';
import './sw-cms-el-two-cols-text-image.scss';

Shopware.Component.register('sw-cms-el-two-cols-text-image', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        imageSrc() {
            return this.element.config.imageSrc.value;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('two-cols-text-image');
        },
    }
});
