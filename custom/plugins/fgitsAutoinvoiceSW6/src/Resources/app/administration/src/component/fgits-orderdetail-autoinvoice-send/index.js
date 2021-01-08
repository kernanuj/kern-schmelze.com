import template from './fgits-orderdetail-autoinvoice-send.html.twig';

const { Component } = Shopware;

Component.register('fgits-orderdetail-autoinvoice-send', {
    template,

    inject: ['fgitsAutoinvoiceService'],

    props: {
        order: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isLoading: false,
            sent: false,
            text: this.$tc('fgits-autoinvoice.send.button.text'),
            variant: ''
        };
    },

    methods: {
        fgitsAutoinvoiceSend: async function () {
            this.isLoading = true;

            await this.fgitsAutoinvoiceService.sendInvoice(this.order.id).then(response => {
                this.status = response.status;
            });

            if (this.status === 'OK') {
                this.sent = true;
                this.variant = '';

                this.isLoading = false;

                window.setTimeout(function () {
                    return window.location.reload();
                }, 500);
            } else {
                this.sent = false;
                this.text = this.$tc('fgits-autoinvoice.send.button.error');
                this.variant = 'danger';

                this.isLoading = false;

                console.log('[#fgits-orderdetail-autoinvoice-send] ' + this.status);
            }
        }
    }
});
