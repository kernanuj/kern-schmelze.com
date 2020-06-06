import template from './sw-order-detail-base.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-order-detail-base', {
    template,

    inject: ['KlarnaPaymentOrderUpdateService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    methods: {
        onSaveEdits() {
            this.$emit('loading-change', true);
            this.$emit('editing-change', false);

            this.KlarnaPaymentOrderUpdateService.updateOrder(this.orderId, this.versionContext.versionId).then(() => {
                this.$super('onSaveEdits');
            }).catch((error) => {
                this.createNotificationError({
                    title: this.$tc('klarna-payment-order-management.messages.updateErrorTitle'),
                    message: this.$tc('klarna-payment-order-management.messages.updateErrorMessage')
                });

                this.versionContext.versionId = Shopware.Context.api.liveVersionId;
                this.reloadEntityData();
            });
        }
    }
});
