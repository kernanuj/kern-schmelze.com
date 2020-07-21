import template from './fgits-orderdetail-autoinvoice-cron.html.twig';

const {Component} = Shopware;

Component.register('fgits-orderdetail-autoinvoice-cron', {
    template,

    inject: ['fgitsAutoinvoiceService'],

    props: {
        value: {
            type: Boolean,
            required: false,
            default() {
                return false;
            }
        },
    },

    computed: {
        currentValue: {
            get() {
                if (!this.value) {
                    return false;
                }

                return this.value;
            },
            set(newValue) {
                this.activateCron();

                this.$emit('input', newValue);
                this.$emit('change', newValue);
            }
        }
    },

    methods: {
        activateCron: async function () {
            await this.fgitsAutoinvoiceService.activateCron();
        }
    }
});
