import template from './klarna-refund-button.html.twig';
import './klarna-refund-button.scss';

const { Component, Mixin } = Shopware;

Component.register('klarna-refund-button', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

    inject: ['KlarnaPaymentOrderService'],

    props: {
        klarnaOrder: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isLoading: false,
            hasError: false,
            showRefundModal: false,
            isRefundSuccessful: false,
            selection: [],
            refundAmount: 0.0,
            description: ''
        };
    },

    computed: {
        remainingAmount() {
            return this.klarnaOrder.captured_amount - this.klarnaOrder.refunded_amount;
        },

        buttonEnabled() {
            if (this.remainingAmount <= 0) {
                return false;
            } if (this.klarnaOrder.order_status === 'CANCELLED') {
                return false;
            } if (this.klarnaOrder.captured_amount <= 0) {
                return false;
            }

            return true;
        },

        maxRefundAmount() {
            return this.remainingAmount / (10 ** this.klarnaOrder.decimal_precision);
        },

        minRefundValue() {
            return 1 / (10 ** this.klarnaOrder.decimal_precision);
        }
    },

    methods: {
        openRefundModal() {
            this.showRefundModal = true;
            this.isRefundSuccessful = false;

            this.refundAmount = this.remainingAmount / (10 ** this.klarnaOrder.decimal_precision);
            this.description = '';
            this.selection = [];
        },

        calculateRefundAmount() {
            let amount = 0;

            this.selection.forEach((selection) => {
                if (selection.selected) {
                    amount += selection.unit_price * selection.quantity;
                }
            });

            if (amount === 0 || amount > this.remainingAmount) {
                amount = this.remainingAmount;
            }

            amount /= (10 ** this.klarnaOrder.decimal_precision);

            this.refundAmount = amount;
        },

        closeRefundModal() {
            this.showRefundModal = false;
        },

        onRefundFinished() {
            this.isRefundSuccessful = false;
        },

        refundOrder() {
            this.isLoading = true;

            const orderLines = [];

            this.selection.forEach((selection) => {
                this.klarnaOrder.order_lines.forEach((order_item) => {
                    if (order_item.reference === selection.reference && selection.selected && selection.quantity > 0) {
                        const copy = { ...order_item };

                        copy.quantity = selection.quantity;
                        copy.total_amount = copy.unit_price * copy.quantity;

                        const taxRate = copy.tax_rate / (10 ** this.klarnaOrder.decimal_precision);

                        copy.total_tax_amount = Math.round(copy.total_amount / (100 + taxRate) * taxRate);

                        orderLines.push(copy);
                    }
                });
            });

            const request = {
                orderTransactionId: this.klarnaOrder.orderTransactionId,
                klarna_order_id: this.klarnaOrder.order_id,
                salesChannel: this.klarnaOrder.salesChannel,
                refundAmount: this.refundAmount,
                description: this.description,
                orderLines: JSON.stringify(orderLines),
                decimalPrecision: this.klarnaOrder.decimal_precision,
                complete: this.refundAmount === this.maxRefundAmount
            };

            this.KlarnaPaymentOrderService.refundOrder(request).then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('klarna-payment-order-management.messages.refundSuccessTitle'),
                    message: this.$tc('klarna-payment-order-management.messages.refundSuccessMessage')
                });

                this.isRefundSuccessful = true;
            }).catch(() => {
                this.createNotificationError({
                    title: this.$tc('klarna-payment-order-management.messages.refundErrorTitle'),
                    message: this.$tc('klarna-payment-order-management.messages.refundErrorMessage')
                });

                this.isRefundSuccessful = false;
            }).finally(() => {
                this.$emit('reload');

                this.isLoading = false;
                this.showRefundModal = false;
            });
        },

        onSelectItem(reference, selected) {
            if (this.selection.length === 0) {
                this._populateSelectionProperty();
            }

            this.selection.forEach((selection) => {
                if (selection.reference === reference) {
                    selection.selected = selected;
                }
            });

            this.calculateRefundAmount();
        },

        onChangeQuantity(reference, quantity) {
            if (this.selection.length === 0) {
                this._populateSelectionProperty();
            }

            this.selection.forEach((selection) => {
                if (selection.reference === reference) {
                    selection.quantity = quantity;
                }
            });

            this.calculateRefundAmount();
        },

        onChangeDescription(description) {
            const max_chars = 255;

            if (description.length >= max_chars) {
                description = description.substr(0, max_chars);
            }

            this.description = description;
        },

        _populateSelectionProperty() {
            this.klarnaOrder.order_lines.forEach((order_item) => {
                let quantity = order_item.quantity;

                if (order_item.captured_quantity > 0) {
                    quantity = order_item.captured_quantity;
                }

                this.selection.push({
                    quantity: quantity - order_item.refunded_quantity,
                    reference: order_item.reference,
                    unit_price: order_item.unit_price,
                    selected: false
                });
            });
        }
    }
});
