import template from './fgits-orderdetail-autoinvoice-attach.html.twig';

const { Component, Mixin } = Shopware;

Component.register('fgits-orderdetail-autoinvoice-attach', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

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
                if (newValue) {
                    this.showNotificationWarning();
                }

                this.$emit('input', newValue);
                this.$emit('change', newValue);
            }
        }
    },

    methods: {
        showNotificationWarning() {
            this.createNotificationWarning({
                title: this.$tc('fgits-autoinvoice.attach.notification.title'),
                message: this.$tc('fgits-autoinvoice.attach.notification.message'),
                duration: 10000
            });
        }
    }
});
