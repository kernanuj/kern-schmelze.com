import template from './klarna-payment-tab.html.twig';
import './klarna-payment-tab.scss';

const { Component, Mixin } = Shopware;

Component.register('klarna-payment-tab', {
    template,

    data() {
        return {
            initialized: false,
            isLoading: true,
            isSubComponentLoading: false,
            hasError: false,
            klarnaOrder: {},
            splitBreakpoint: 1024,
            isMobile: false,
            klarnaHistory: {},
            orderAmount: []
        };
    },

    created() {
        this.createdComponent();
    },

    destroyed() {
        this.destroyedComponent();
    },

    mixins: [
        Mixin.getByName('notification')
    ],

    inject: ['KlarnaPaymentOrderService'],

    methods: {
        createdComponent() {
            this.$root.$on('language-change', this.loadData);

            this.$device.onResize({
                listener: this.checkViewport.bind(this)
            });

            this.checkViewport();
            this.loadData();
        },

        destroyedComponent() {
            this.$root.$off('language-change', this.loadData);
        },

        checkViewport() {
            this.isMobile = this.$device.getViewportWidth() < this.splitBreakpoint;
        },

        loadData() {
            const klarnaOrderId = this.$route.params.transaction.klarna_order_id;
            const salesChannel = this.$route.params.transaction.salesChannel;
            const orderTransactionId = this.$route.params.transaction.orderTransactionId;

            this.isLoading = true;
            this.hasError = false;

            this.KlarnaPaymentOrderService.fetchOrderData(klarnaOrderId, salesChannel).then((response) => {
                this.hasError = false;
                this.initialized = true;

                this.klarnaOrder = response.order;
                this.klarnaOrder.salesChannel = salesChannel;
                this.klarnaOrder.orderTransactionId = orderTransactionId;

                this.klarnaHistory = response.transactionHistory;
            }).catch(() => {
                this.createNotificationError({
                    title: this.$tc('klarna-payment-order-management.messages.loadErrorTitle'),
                    message: this.$tc('klarna-payment-order-management.messages.loadErrorMessage')
                });

                this.hasError = true;
            }).finally(() => {
                this.isLoading = false;
            });
        },

        setSubComponentLoading(subComponentLoading) {
            this.isSubComponentLoading = subComponentLoading;
        }
    }
});
