import template from './swag-customized-products-option-type-header.html.twig';
import './swag-customized-products-option-type-header.scss';

const { Component } = Shopware;
const { mapApiErrors } = Shopware.Component.getComponentHelper();

Component.register('swag-customized-products-option-type-header', {
    template,

    mixins: [
        'placeholder'
    ],

    props: {
        option: {
            type: Object,
            required: true
        },

        showRequired: {
            type: Boolean,
            required: false,
            default: true
        },

        data: {
            type: Object
        }
    },

    computed: {
        displayName: {
            get() {
                if (this.data) {
                    return this.data.name;
                }
                return this.option.displayName;
            },

            set(newName) {
                if (this.data) {
                    this.data.name = newName;
                }
                this.option.displayName = newName;
            }
        },
        requiredTooltip() {
            if (this.option.templateExclusionConditions.length > 0) {
                return this.$tc('swag-customized-products.optionDetailModal.optionType.requiredTooltip.disabled');
            }

            return this.$tc('swag-customized-products.optionDetailModal.optionType.requiredTooltip.enabled');
        },

        ...mapApiErrors('option', ['displayName'])
    }
});
