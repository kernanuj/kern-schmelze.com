import template from './klarna-order-items.html.twig';

const { Component } = Shopware;

Component.register('klarna-order-items', {
    template,

    props: {
        klarnaOrder: {
            type: Object,
            required: true
        },

        mode: {
            type: String,
            required: false
        }
    },

    computed: {
        orderItems() {
            const data = [];

            this.klarnaOrder.order_lines.forEach((order_item) => {
                const price = this.$options.filters.klarna_currency(
                    order_item.total_amount,
                    this.klarnaOrder.currency,
                    this.klarnaOrder.decimal_precision
                );

                let disabled = false;
                let quantity = order_item.quantity;

                if (this.mode === 'refund' && order_item.captured_quantity > 0) {
                    quantity = order_item.captured_quantity;
                }

                if (this.mode === 'capture') {
                    quantity -= order_item.captured_quantity;
                } else if (this.mode === 'refund') {
                    quantity -= order_item.refunded_quantity;
                }

                if (quantity <= 0) {
                    disabled = true;
                }

                data.push({
                    id: order_item.reference,
                    reference: order_item.reference,
                    product: order_item.name,
                    amount: quantity,
                    disabled: disabled,
                    price: price,
                    orderItem: order_item
                });
            });

            return data;
        },

        orderItemColumns() {
            return [
                {
                    property: 'reference',
                    label: this.$tc('klarna-payment-order-management.modal.columns.reference'),
                    rawData: true
                },
                {
                    property: 'product',
                    label: this.$tc('klarna-payment-order-management.modal.columns.product'),
                    rawData: true
                },
                {
                    property: 'amount',
                    label: this.$tc('klarna-payment-order-management.modal.columns.amount'),
                    rawData: true
                },
                {
                    property: 'price',
                    label: this.$tc('klarna-payment-order-management.modal.columns.price'),
                    rawData: true
                }
            ];
        }
    },

    methods: {
        onSelectItem(selection, item, selected) {
            this.$emit('select-item', item.id, selected);
        },

        onChangeQuantity(value, reference) {
            this.$emit('change-quantity', reference, value);
        }
    }
});
