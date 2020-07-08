import template from './sw-order-line-items-grid.html.twig';

const { Component } = Shopware;

Component.override('sw-order-line-items-grid', {
    template,

    data() {
        return {
            isCustomProductModalOpen: false,
            currentProduct: null
        };
    },

    computed: {
        orderLineItems() {
            // Just for the output: Filter custom product and custom products options out of the order line items
            const filteredLineItems = this.$super('orderLineItems').filter((item) => {
                return ['customized-products', 'customized-products-option', 'option-values']
                    .includes(item.type) === false;
            });

            filteredLineItems.forEach((item) => {
                if (!this.productHasCustomizedProduct(item) || item.type !== 'product') {
                    return;
                }

                this.orderLineItemRepository.get(item.parentId, this.context).then((result) => {
                    item.parent = result;
                });
            });

            return filteredLineItems;
        }
    },

    methods: {
        productHasCustomizedProduct(item) {
            if (!item.parentId) {
                return false;
            }
            return this.order.lineItems.has(item.parentId);
        },

        onOpenCustomProductConfiguration(item) {
            this.isCustomProductModalOpen = true;
            this.currentProduct = item;
        },

        onCloseCustomProductConfigurationModal() {
            this.isCustomProductModalOpen = false;
            this.currentProduct = null;
        }
    }
});
