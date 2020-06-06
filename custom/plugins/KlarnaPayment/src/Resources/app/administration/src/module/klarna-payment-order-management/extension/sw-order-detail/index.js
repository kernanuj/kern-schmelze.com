import template from './sw-order-detail.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-order-detail', {
    template,

    data() {
        return {
            klarnaTransactions: []
        };
    },

    computed: {
        isEditable() {
            return this.klarnaTransactions.length === 0 || this.$route.name !== 'klarna-payment-order-management.payment.detail';
        },

        showTabs() {
            return true;
        }
    },

    created() {
        this.$router.push({ name: 'sw.order.detail', params: { id: this.orderId } });
    },

    watch: {
        orderId: {
            deep: true,
            handler() {
                if (!this.orderId) {
                    this.klarnaTransactions = [];

                    return;
                }

                this.loadOrderData();
            },
            immediate: true
        }
    },

    methods: {
        loadOrderData() {
            const orderRepository = this.repositoryFactory.create('order');

            const orderCriteria = new Criteria(1, 1);
            orderCriteria.addAssociation('transactions');

            return orderRepository.get(this.$route.params.id, Shopware.Context.api, orderCriteria).then((order) => {
                this.loadKlarnaTransactions(order);
            });
        },

        loadKlarnaTransactions(order) {
            const me = order;

            order.transactions.forEach((orderTransaction) => {
                if (!orderTransaction.customFields) {
                    return;
                }

                if (!orderTransaction.customFields.klarna_order_id) {
                    return;
                }

                this.klarnaTransactions.push({
                    orderTransactionId: orderTransaction.id,
                    klarna_order_id: orderTransaction.customFields.klarna_order_id,
                    salesChannel: me.salesChannelId
                });
            });
        }
    }
});
