import template from './fgits-autoinvoice-export.html.twig';

const { Component, Mixin } = Shopware;

Component.register('fgits-autoinvoice-export', {
    template,

    inject: ['fgitsAutoinvoiceService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            cardTitle: this.$tc('fgits-autoinvoice.export.cardTitle')
        };
    },

    methods: {
        fgitsAutoinvoiceExport: async function () {
            this.isLoading = true;

            await this.fgitsAutoinvoiceService.exportInvoices().then(response => {
                this.status = response.status;
            });

            if (this.status === 'OK') {
                this.showNotificationSuccess();

                this.isLoading = false;
            } else {
                this.showNotificationError();

                this.isLoading = false;

                console.log('[#fgits-autoinvoice-export] ' + this.status);
            }
        },

        showNotificationSuccess() {
            this.createNotificationSuccess({
                title: this.$tc('fgits-autoinvoice.export.notificationSuccessTitle'),
                message: this.$tc('fgits-autoinvoice.export.notificationSuccessMessage'),
                duration: 5000
            });
        },

        showNotificationError() {
            this.createNotificationError({
                title: this.$tc('fgits-autoinvoice.export.notificationErrorTitle'),
                message: this.$tc('fgits-autoinvoice.export.notificationErrorMessage'),
                duration: 5000
            });
        }
    }
});
