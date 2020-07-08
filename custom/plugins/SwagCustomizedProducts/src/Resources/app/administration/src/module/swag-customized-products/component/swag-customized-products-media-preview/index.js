import template from './swag-customized-products-media-preview.html.twig';
import './swag-customized-products-media-preview.scss';

const { Component } = Shopware;

Component.register('swag-customized-products-media-preview', {
    template,
    inject: [
        'repositoryFactory'
    ],

    props: {
        optionId: {
            type: String,
            required: true
        },
        mediaItems: {
            type: Array,
            required: true
        },
        label: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            resolvedMediaItems: []
        };
    },

    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.fetchItems();
        },

        fetchItems() {
            this.mediaItems.map(async (media) => {
                this.mediaRepository.get(media.mediaId, Shopware.Context.api).then((mediaEntry) => {
                    this.resolvedMediaItems.push(mediaEntry);
                });
            });
        }
    }
});
