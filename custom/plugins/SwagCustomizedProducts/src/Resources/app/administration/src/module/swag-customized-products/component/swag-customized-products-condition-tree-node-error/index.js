import template from './swag-customized-products-condition-tree-node-error.html.twig';
import './swag-customized-products-condition-tree-node-error.scss';

const { Component, Utils } = Shopware;


Component.register('swag-customized-products-condition-tree-node-error', {
    template,

    props: {
        optionError: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        },
        operatorError: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        },
        showOperatorError: {
            type: Boolean,
            required: false,
            default: true
        }
    },

    computed: {
        hasError() {
            return !(Utils.types.isEmpty(this.optionError) || Utils.types.isEmpty(this.operatorError));
        }
    }
});
