import template from './swag-customized-products-detail.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-customized-products-detail', {
    template,

    inject: [
        'repositoryFactory',
        'SwagCustomizedProductsTemplateService',
        'SwagCustomizedProductsTemplateApiService'
    ],

    mixins: [
        'placeholder',
        'notification'
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel'
    },

    props: {
        templateId: {
            type: String,
            required: false,
            default: null
        }
    },

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            template: null,
            displayMediaItem: null,
            versionContext: {}
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    computed: {
        identifier() {
            return this.template && this.template.internalName
                ? this.template.internalName
                : this.$tc('swag-customized-products.create.title');
        },

        templateRepository() {
            return this.repositoryFactory.create('swag_customized_products_template');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        isCreateMode() {
            return this.$route.name === 'swag.customized.products.create.base';
        },

        templateCriteria() {
            return new Criteria();
        },

        entityDescription() {
            return this.placeholder(
                this.template,
                'internalName',
                this.$tc('swag-customized-products.create.title')
            );
        },

        tooltipSave() {
            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light'
            };
        },

        // Only for overriding purposes to re-enable the tabview
        showTabs() {
            return false;
        },

        isNewlyCreated() {
            if (!this.template) {
                return true;
            }

            return this.template._isNew;
        }
    },

    beforeRouteLeave(to, from, next) {
        if (to.name === 'swag.customized.products.detail.base') {
            next();
            return;
        }

        this.handleRouteLeave(next);
    },

    watch: {
        templateId() {
            if (this.templateId === null) {
                this.createTemplate();
            }

            this.createdComponent();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.versionContext = Shopware.Context.api;

            if (this.templateId === null) {
                this.createTemplate();
                return;
            }

            this.createVersion();
        },

        loadEntityData() {
            this.isLoading = true;

            return this.templateRepository.get(this.templateId, this.versionContext, this.templateCriteria)
                .then((response) => {
                    this.template = response;
                    this.isLoading = false;
                    return response;
                }).catch((err) => {
                    this.isLoading = false;
                    return err;
                });
        },

        createTemplate() {
            if (this.languageStore.getCurrentId() !== this.languageStore.systemLanguageId) {
                this.languageStore.setCurrentId(this.languageStore.systemLanguageId);
            }
            this.template = this.templateRepository.create(this.versionContext);
        },

        createVersion() {
            this.templateRepository.createVersion(this.templateId, this.versionContext).then((newContext) => {
                this.versionContext = newContext;
                return this.loadEntityData();
            });
        },

        async onSave() {
            this.isLoading = true;

            try {
                if (!this.versionContext.versionId) {
                    // If we dont have a version yet, we save a normal entity
                    await this.templateRepository.save(this.template, Shopware.Context.api);
                    this.isSaveSuccessful = true;

                    return;
                }

                // Merging the version into the entity
                await this.saveEdits();
                await this.templateRepository.mergeVersion(this.versionContext.versionId, this.versionContext);
                this.SwagCustomizedProductsTemplateApiService.dispatchTreeGenerationMessage(this.template.id);

                this.isSaveSuccessful = true;
            } catch (e) {
                const templateName = this.template.internalName;
                const titleSaveError = this.$tc('global.default.error');
                const messageSaveError = this.$tc(
                    'global.notification.notificationSaveErrorMessage', 0, { entityName: templateName }
                );

                this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError
                });

                this.isSaveSuccessful = false;
                this.isLoading = false;

                return;
            }

            this.versionContext.versionId = Shopware.Context.api.liveVersionId;
        },

        /**
         * @returns {Promise}
         */
        saveEdits() {
            return this.templateRepository.save(this.template, this.versionContext);
        },

        onSaveFinish(resetSaveAnimation = false) {
            if (resetSaveAnimation) {
                this.isSaveSuccessful = false;
            }

            let templateId = this.templateId;
            if (templateId === null) {
                templateId = this.template.id;

                this.$router.push({ name: 'swag.customized.products.detail', params: { id: templateId } });
                return;
            }

            this.createdComponent();
        },

        onCancel() {
            this.$router.push({ name: 'swag.customized.products.index' });
        },

        onAddItemToTemplate(mediaEntity) {
            this.template.mediaId = mediaEntity.id;
        },

        onChangeLanguage() {
            this.createdComponent();
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        handleRouteLeave(next) {
            this.templateRepository.deleteVersion(
                this.templateId,
                this.versionContext.versionId,
                this.versionContext
            ).catch((error) => {
                // This error has no consequences, because we revert to the live version anyways
                this.$emit('error', error);
                return error;
            });

            next();
        },

        async onDuplicateSave() {
            await this.onSave();
            const copySuffix = this.$tc('swag-customized-products.general.duplicateNameSuffix');
            const criteria = this.templateCriteria
                .addAssociation('options')
                .addAssociation('options.values');

            return this.templateRepository.get(this.templateId, Shopware.Context.api, criteria).then((result) => {
                return this.SwagCustomizedProductsTemplateService.duplicateTemplate(result, copySuffix);
            }).then((duplicate) => {
                this.$router.push({
                    name: 'swag.customized.products.detail',
                    params: { id: duplicate.id }
                });
            });
        }
    }
});
