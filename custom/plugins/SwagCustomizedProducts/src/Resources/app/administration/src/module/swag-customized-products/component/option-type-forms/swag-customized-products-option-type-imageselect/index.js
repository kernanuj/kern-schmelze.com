import template from './swag-customized-products-option-type-imageselect.html.twig';
import './swag-customized-products-option-type-imageselect.scss';

const { Component } = Shopware;
const { mapApiErrors } = Shopware.Component.getComponentHelper();

Component.extend('swag-customized-products-option-type-imageselect', 'swag-customized-products-option-type-base-tree', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            activeItem: null,
            displayMediaItem: null,
            uploadTag: 'swag-customized-products-option-imageselect-upload-tag'
        };
    },

    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        templateRepository() {
            return this.repositoryFactory.create('swag_customized_products_template');
        },

        ...mapApiErrors('activeItem', ['value._value'])
    },

    watch: {
        activeItem() {
            if (this.activeItem.value) {
                this.setMediaItem({ targetId: this.activeItem.value._value });
            }
        }
    },

    methods: {
        setMediaFromSelection(selection) {
            this.setMediaItem({ targetId: selection[0].id });
        },

        setMediaItem({ targetId }) {
            if (!targetId) {
                this.displayMediaItem = null;
                this.activeItem.value._value = '';
                return;
            }

            this.mediaRepository.get(targetId, Shopware.Context.api).then((response) => {
                this.displayMediaItem = response;
                this.activeItem.value._value = targetId;
            });
        },

        onUnlinkImage() {
            this.setMediaItem({ targetId: null });
        },

        onDropMedia(mediaEntity) {
            this.setMediaItem({ targetId: mediaEntity.id });
        },

        setActiveItem(item) {
            this.activeItem = item;
        }
    }
});
