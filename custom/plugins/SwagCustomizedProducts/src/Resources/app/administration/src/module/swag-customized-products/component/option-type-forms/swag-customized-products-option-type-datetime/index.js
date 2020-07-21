import template from './swag-customized-products-option-type-datetime.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-datetime', 'swag-customized-products-option-type-base', {
    template,

    data() {
        return {
            minSelectableDate: '0000-01-01',
            minDateConfig: {
                defaultDate: new Date()
            },
            maxDateConfig: {
                disable: []
            }
        };
    },

    watch: {
        'option.typeProperties.minDate'(value) {
            this.onMinDateChange(value);
        }
    },

    mounted() {
        this.mountedComponent();
    },

    methods: {
        mountedComponent() {
            this.maxDateConfig.defaultDate = this.option.typeProperties.minDate;
            this.onMinDateChange(this.option.typeProperties.minDate);
        },

        onMinDateChange(value) {
            if (!value) {
                this.disableMaxDateField();
                return;
            }

            this.enableMaxDateField();
            const maxDateField = this.$refs.maxDateField;
            maxDateField.flatpickrInstance.clear();
        },

        enableMaxDateField() {
            const maxDateField = this.$refs.maxDateField;
            maxDateField.flatpickrInstance._input.removeAttribute('disabled');

            const newToDate = new Date(this.option.typeProperties.minDate);

            // We have to subtract 1 day to allow selecting the current day
            newToDate.setDate(newToDate.getDate() - 1);
            this.maxDateConfig.disable = [{
                from: new Date(this.minSelectableDate),
                to: newToDate
            }];
        },

        disableMaxDateField() {
            const maxDateField = this.$refs.maxDateField;
            maxDateField.flatpickrInstance._input.setAttribute('disabled', 'disabled');
            maxDateField.flatpickrInstance.clear();
        }
    }
});
