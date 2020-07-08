import template from './swag-customized-products-exclusion-delete-modal.html.twig';

const { Component } = Shopware;

Component.register('swag-customized-products-exclusion-delete-modal', {
    template,

    props: {
        currentSelection: {
            type: Object,
            required: true
        },
        currentExclusionName: {
            type: String,
            required: false,
            default() {
                return '';
            }
        }
    },

    computed: {
        selectedExclusionsCount() {
            return Object.values(this.currentSelection).length;
        },

        getModalText() {
            return this.$tc(
                'swag-customized-products.detail.tabGeneral.cardExclusion.textModalDelete',
                Math.max(this.selectedExclusionsCount, 1),
                {
                    count: this.selectedExclusionsCount,
                    name: this.currentExclusionName
                }
            );
        }
    },

    methods: {
        onExclusionConfirmDelete() {
            this.$emit('confirm-delete');
        },

        onExclusionCloseDeleteModal() {
            this.$emit('close-modal');
        }
    }
});
