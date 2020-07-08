import template from './swag-customized-products-option-tree-item.html.twig';
import './swag-customized-products-option-tree-item.scss';

const { Component } = Shopware;

Component.register('swag-customized-products-option-tree-item', {
    template,

    mixins: [
        'placeholder'
    ],

    props: {
        item: {
            type: Object,
            required: true
        },

        isRootElement: {
            type: Boolean,
            required: false,
            default: false
        },

        isEditing: {
            type: Boolean,
            required: true
        },

        isActive: {
            type: Boolean,
            required: true
        },

        isCreating: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.registerWatcher();
        },

        registerWatcher() {
            this.$watch('isEditing', (value) => {
                this.$emit('on-isEditing', this.onIsEditing(value));
            }, {
                deep: true,
                immediate: true
            });
        },

        onIsEditing(value) {
            this.$nextTick(() => {
                if (value && typeof this.$refs.confirmField !== 'undefined') {
                    const inputElements = this.$refs.confirmField.$el.getElementsByTagName('input');
                    if (inputElements.length > 0 && inputElements[0].type === 'text') {
                        inputElements[0].focus();
                    }
                }
            });
        },

        onClickCreateNewElement() {
            this.$emit('new-element-create');
        },

        onFinishNameEdit(name) {
            this.$emit('display-name-edit', name, this.item);
        },

        onStartInlineEditing(id) {
            this.$emit('display-name-start-editing', id);
        },

        onStopInlineEditing() {
            this.$emit('display-name-start-editing', null);
        },

        onCancelSubmit(item) {
            this.$emit('create-item-abort', item);
        },

        onBlurTreeItemInput(item) {
            this.$emit('create-item-blur', item);
        },

        onSetActiveItem(item) {
            this.$emit('active-item-set', item);
        },

        onDeleteItem(item) {
            this.$emit('item-delete', item);
        }
    }
});

