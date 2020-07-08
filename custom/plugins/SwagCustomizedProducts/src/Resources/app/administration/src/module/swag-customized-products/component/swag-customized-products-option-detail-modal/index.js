import template from './swag-customized-products-option-detail-modal.html.twig';
import './swag-customized-products-option-detail-modal.scss';

const { Component } = Shopware;

Component.register('swag-customized-products-option-detail-modal', {
    template,

    mixins: [
        'swag-customized-products-option',
        Shopware.Mixin.getByName('notification')
    ],

    inject: [
        'repositoryFactory'
    ],

    props: {
        option: {
            type: Object,
            required: true
        },

        optionRepository: {
            type: Object,
            required: true
        },

        versionContext: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            valid: false,
            optionClone: undefined,
            saveMethods: [],
            isLoading: false
        };
    },

    computed: {
        componentName() {
            return `swag-customized-products-option-type-${this.option.type}`;
        },

        componentIsLoaded() {
            return Component.getComponentRegistry().has(this.componentName);
        },

        modalSize() {
            const optionTreeTypes = ['colorselect', 'imageselect', 'select'];

            if (optionTreeTypes.includes(this.option.type)) {
                return 'full';
            }

            return 'large';
        }
    },

    methods: {
        onCancel() {
            // If the option is being edited we should persist the version
            if (this.option.isEdit) {
                return this.$emit('modal-close');
            }

            // If the option is being created and canceled, we should delete the version
            return this.optionRepository.deleteVersion(
                this.option.id,
                this.versionContext.versionId,
                this.versionContext
            ).then(() => {
                this.$emit('modal-close');
            });
        },

        async onSave() {
            this.isLoading = true;

            // On the second version commit we remove the optionAdd validation flag
            delete this.option.typeProperties.optionAdd;

            try {
                // wait until all additional save methods are resolved
                const savePromises = this.saveMethods.map((saveMethod) => saveMethod());
                await Promise.all(savePromises);

                await this.optionRepository.save(this.option, this.versionContext);

                this.$emit('modal-close');
            } catch (error) {
                const titleSaveError = this.$tc('global.default.error');
                let messageSaveError = this.$tc(
                    'global.notification.notificationSaveErrorMessage', 0, { entityName: this.option.displayName }
                );

                if (error.message === 'no-children') {
                    messageSaveError = this.$tc('swag-customized-products.optionDetailModal.noChildren');
                }

                this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError
                });

                this.isLoading = false;
            }

            this.isLoading = false;
        },

        onSaveMethodAdd(method) {
            this.saveMethods.push(method);
        },

        onOptionValid(valid) {
            this.valid = valid;
        },

        getTitle(option) {
            return `${this.translateOption(option.type)}: ${option.translated.displayName}`;
        }
    }
});
