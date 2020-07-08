import template from './swag-customized-products-configuration-modal.html.twig';
import './swag-customized-products-configuration-modal.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const Context = Shopware.Context.api;

Component.register('swag-customized-products-configuration-modal', {
    template,

    inject: ['repositoryFactory'],

    props: {
        orderLineItems: {
            type: Array,
            required: true
        },
        currentProduct: {
            type: Object,
            required: true
        },
        currency: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isLoading: false,
            options: []
        };
    },

    computed: {
        customProductContainer() {
            const id = this.currentProduct.parentId;

            if (!this.orderLineItems.has(id)) {
                return null;
            }

            const container = this.orderLineItems.get(id);
            if (container.type !== 'customized-products') {
                return null;
            }

            return container;
        },

        customProductOptions() {
            const customProductTemplate = this.customProductContainer;

            if (!customProductTemplate) {
                return [];
            }

            return this.orderLineItems.reduce((accumulator, item) => {
                if (item.type === 'customized-products-option' && item.parentId === customProductTemplate.id) {
                    accumulator.push(item);
                }
                return accumulator;
            }, []);
        },

        templateRepository() {
            return this.repositoryFactory.create('swag_customized_products_template');
        },

        optionValueRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_option_value');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.fetchCustomProductTemplate();
        },

        onCloseModal() {
            this.$emit('modal-close');
        },

        fetchCustomProductTemplate() {
            if (!this.customProductContainer.referencedId) {
                return Promise.reject(new Error('Custom Product container was not found in order.'));
            }

            this.isLoading = true;

            const criteria = this.createCustomProductTemplateCriteria();
            return this.templateRepository.get(this.customProductContainer.referencedId, Context, criteria)
                .then((entity) => {
                    this.options = this.hydrateCustomProductOptions(entity);
                    return this.options;
                });
        },

        createCustomProductTemplateCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('options');
            criteria.addAssociation('options.values');

            criteria.getAssociation('options')
                .addSorting(Criteria.sort('position', 'ASC'));
            criteria.getAssociation('options.values')
                .addSorting(Criteria.sort('position', 'ASC'));

            return criteria;
        },

        customProductOptionValues(optionId) {
            return this.orderLineItems.reduce((accumulator, item) => {
                if (item.type === 'option-values' && optionId === item.parentId) {
                    accumulator.push(item);
                }
                return accumulator;
            }, []);
        },

        hydrateCustomProductOptions(entity) {
            const mappedOptions = [];
            const promises = [];

            entity.options.forEach((item) => {
                // Either an option or an optionValue of custom Products
                const lineItem = this.customProductOptions.find(option => option.referencedId === item.id);

                if (!lineItem) {
                    return;
                }

                let value = lineItem.payload.value;
                if (item.values.length > 0) {
                    value = [];
                    this.customProductOptionValues(lineItem.id).forEach((optionValue) => {
                        console.log(optionValue);
                        // Provide further information of the optionValue
                        const payload = {
                            oneTimeSurcharge: optionValue.payload.isOneTimeSurcharge,
                            price: optionValue.unitPrice,
                            displayName: optionValue.label,
                            value: optionValue.payload.value._value
                        };

                        if (item.type === 'imageselect') {
                            promises.push(this.mediaRepository.get(payload.value, Shopware.Context.api).then((media) => {
                                payload.value = media;
                                value.push(payload);
                            }));

                            return;
                        }

                        value.push(payload);
                    });
                }

                const registeredPayload = {
                    id: lineItem.referencedId,
                    type: lineItem.payload.type,
                    displayName: lineItem.label,
                    price: lineItem.price.unitPrice,
                    oneTimeSurcharge: lineItem.payload.isOneTimeSurcharge,
                    value,
                    media: []
                };

                // If we're having a media associated to the line item, map it to our option
                if (Object.prototype.hasOwnProperty.call(lineItem.payload, 'media')) {
                    registeredPayload.media = lineItem.payload.media;
                }

                mappedOptions.push(registeredPayload);
            });

            Promise.all(promises).then(() => {
                this.isLoading = false;
            });

            return mappedOptions;
        }
    }
});
