import template from './fgits-orderdetail-autoinvoice-processed.html.twig';

const { Component } = Shopware;

Component.register('fgits-orderdetail-autoinvoice-processed', {
    template,

    props: {
        order: {
            type: Object,
            required: true
        }
    },

    computed: {
        fgitsAutoinvoiceProcessed: {
            get() {
                if (this.order.customFields) {
                    return this.order.customFields.fgits_autoinvoice_processed;
                }

                return false;
            }
        }
    }
});
