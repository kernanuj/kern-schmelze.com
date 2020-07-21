import template from './swag-customized-products-option-type-timestamp.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-timestamp', 'swag-customized-products-option-type-base', {
    template,

    data() {
        return {
            maxTimeConfig: {
                minTime: null
            }
        };
    },

    watch: {
        'option.typeProperties.startTime'(value) {
            this.onStartTimeChanged(value);
        }
    },

    mounted() {
        this.mountedComponent();
    },

    methods: {
        mountedComponent() {
            this.onStartTimeChanged(this.option.typeProperties.startTime);
        },

        onStartTimeChanged(value) {
            if (!value) {
                this.disableMaxTimeField();
                return;
            }

            this.enableMaxTimeField();
            const maxTimeField = this.$refs.maxTimeField;
            maxTimeField.flatpickrInstance.setDate(value);
        },

        enableMaxTimeField() {
            const maxTimeField = this.$refs.maxTimeField;
            maxTimeField.flatpickrInstance._input.removeAttribute('disabled');

            this.maxTimeConfig.minTime = this.option.typeProperties.startTime;
        },

        disableMaxTimeField() {
            const maxTimeField = this.$refs.maxTimeField;
            maxTimeField.flatpickrInstance._input.setAttribute('disabled', 'disabled');
            maxTimeField.flatpickrInstance.clear();
        }
    }
});
