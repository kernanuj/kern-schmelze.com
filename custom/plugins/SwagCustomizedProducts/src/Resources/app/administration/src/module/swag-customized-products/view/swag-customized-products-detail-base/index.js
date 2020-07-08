import template from './swag-customized-products-detail-base.html.twig';
import './swag-customized-products-detail-base.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const { mapApiErrors } = Shopware.Component.getComponentHelper();

Component.register('swag-customized-products-detail-base', {
    template,

    inject: [
        'repositoryFactory',
        'SwagCustomizedProductsTemplateOptionService'
    ],

    mixins: [
        'placeholder',
        'notification',
        'position',
        'swag-customized-products-option'
    ],

    props: {
        template: {
            type: Object,
            required: true
        },
        isCreateMode: {
            type: Boolean,
            required: true
        },
        versionContext: {
            type: Object,
            required: true
        },
        parentRefs: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isLoading: false,
            uploadTag: 'swag-customized-products-upload-tag',
            optionTypes: null,
            selectedOptions: {},
            newOption: null,
            lastNewPosition: undefined,
            displayMediaItem: null,
            showOptionDetailModal: false,
            showOptionCreateModal: false,
            showOptionDeleteModal: false,
            disableRouteParams: true,
            optionSearchTerm: '',
            optionSortProperty: 'position',
            optionSortDirection: 'ASC',
            options: []
        };
    },

    computed: {
        optionRepository() {
            return this.repositoryFactory.create(
                this.template.options.entity,
                this.template.options.source
            );
        },

        templateRepository() {
            return this.repositoryFactory.create('swag_customized_products_template');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        optionColumns() {
            return this.getOptionColumns();
        },

        selectedOptionsCount() {
            return Object.values(this.selectedOptions).length;
        },

        getOptionsDeleteModalText() {
            return this.$tc(
                'swag-customized-products.detail.tabGeneral.cardOption.textModalDelete',
                Math.max(this.selectedOptionsCount, 1),
                {
                    count: this.selectedOptionsCount,
                    name: this.currentOptionDisplayName
                }
            );
        },

        optionConfirmCreateDisabled() {
            if (this.newOption === null) {
                return false;
            }

            return (
                !this.newOption.hasOwnProperty('displayName') ||
                !this.newOption.hasOwnProperty('type') ||
                this.newOption.displayName === null ||
                this.newOption.type === null ||
                this.newOption.displayName.length < 1
            );
        },

        optionAddDisabled() {
            const apiContext = Shopware.Context.api;

            return apiContext.languageId !== apiContext.systemLanguageId;
        },

        showExclusionList() {
            let excludeableOptions = this.options.filter((option) => {
                return option.required === false;
            });

            excludeableOptions = excludeableOptions.filter((item) => {
                if (Object.prototype.hasOwnProperty.call(item, 'typeProperties') &&
                        Object.prototype.hasOwnProperty.call(item.typeProperties, 'isMultiSelect') &&
                        item.typeProperties.isMultiSelect === true) {
                    return false;
                }

                return true;
            });

            return excludeableOptions.length >= 2;
        },

        ...mapApiErrors('template', ['displayName', 'internalName'])
    },

    watch: {
        'template.mediaId'() {
            this.setMediaItem({ targetId: this.template.mediaId });
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadOptionTypes();
            this.setMediaItem({ targetId: this.template.mediaId });

            this.getOptionList();
        },

        getListCriteria() {
            return (new Criteria(1, 10))
                .setTerm(this.optionSearchTerm)
                .addFilter(Criteria.equals('templateId', this.template.id))
                .addSorting(Criteria.sort(this.optionSortProperty, this.optionSortDirection))
                .addAssociation('prices')
                .addAssociation('values')
                .addAssociation('templateExclusionConditions');
        },

        loadOptionTypes() {
            return this.SwagCustomizedProductsTemplateOptionService.getSupportedTypes().then((types) => {
                // TODO: Remove blacklist when storefront implementation is finished
                const optionTypeBlacklist = [
                    'colorpicker'
                ];

                this.optionTypes = types.sort((a, b) => {
                    return this.translateOption(a).toLowerCase() <= this.translateOption(b).toLowerCase() ? -1 : 1;
                }).filter((optionType) => {
                    return !optionTypeBlacklist.includes(optionType);
                });

                return this.optionTypes;
            });
        },

        onOptionAdd() {
            const newOption = this.optionRepository.create(this.versionContext);
            const positionCriteria = (new Criteria())
                .addFilter(Criteria.equals('templateId', this.template.id));

            this.getNewPosition(this.optionRepository, positionCriteria, this.versionContext).then((position) => {
                // If multiple new options are added before saving, getNewPosition will always result in the same number
                this.lastNewPosition = (this.lastNewPosition >= position) ? this.lastNewPosition + 1 : position;

                newOption.templateId = this.template.id;
                newOption.position = this.lastNewPosition;
                newOption.advancedSurcharge = false;
                newOption.oneTimeSurcharge = false;
                newOption.relativeSurcharge = false;
                newOption.typeProperties = {
                    // On the first version commit we add the optionAdd validation flag
                    optionAdd: true
                };
                newOption.type = 'checkbox';

                newOption.values = [];
                this.newOption = newOption;
                this.showOptionCreateModal = true;
            });
        },

        onOptionEdit(item) {
            item.isEdit = true;

            this.showOptionDetailModal = item;
        },

        onOptionCloseDetailModal() {
            this.getOptionList();
            this.showOptionDetailModal = false;

            if (this.newOption) {
                this.newOption = null;
            }
        },

        onOptionDelete(item) {
            this.showOptionDeleteModal = item;
            this.currentOptionDisplayName = item.displayName;
        },

        onOptionBulkDelete() {
            this.showOptionDeleteModal = 'bulk';
            this.currentOptionDisplayName = Object.values(this.selectedOptions)[0].displayName;
        },

        onOptionCloseCreateModal() {
            this.showOptionCreateModal = false;
        },

        onOptionCloseDeleteModal() {
            this.showOptionDeleteModal = false;
            this.currentOptionDisplayName = null;
        },

        onOptionConfirmDelete() {
            if (this.showOptionDeleteModal === 'bulk') {
                this.onOptionConfirmBulkDelete();
                return;
            }

            const itemId = this.showOptionDeleteModal.id;
            this.showOptionDeleteModal = false;

            this.optionRepository.deleteVersion(itemId, this.versionContext.versionId, this.versionContext)
                .then(() => {
                    this.getOptionList();
                });
        },

        onOptionConfirmCreate() {
            if (this.optionConfirmCreateDisabled) {
                return;
            }

            this.onOptionCloseCreateModal();

            this.optionRepository.save(this.newOption, this.versionContext).then(() => {
                return this.optionRepository.get(this.newOption.id, this.versionContext);
            }).then((option) => {
                this.showOptionDetailModal = option;
            });
        },

        onOptionConfirmBulkDelete() {
            const promises = [];

            Object.keys(this.selectedOptions).forEach((id) => {
                promises.push(this.optionRepository.deleteVersion(id, this.versionContext.versionId, this.versionContext));
            });

            this.onOptionCloseDeleteModal();

            Promise.all(promises).then(() => {
                this.getOptionList();
            });
        },

        onOptionSelectionChanged(selection) {
            this.selectedOptions = selection;
        },

        onOptionSearch(searchTerm) {
            this.optionSearchTerm = searchTerm;

            if (this.$refs.grid && this.$refs.grid.result) {
                this.$refs.grid.result.criteria.setTerm(this.optionSearchTerm);
            }

            this.getOptionList();
        },

        async saveOptions() {
            this.isLoading = true;

            await this.optionRepository.saveAll(this.options, this.versionContext);
            await this.getOptionList();

            this.isLoading = false;
        },

        saveError(option) {
            this.isLoading = false;

            if (this.newOption && option.id === this.newOption.id) {
                // TODO: new option got a save error (PT-10720)
            }

            const titleSaveError = this.$tc('global.default.error');
            const messageSaveError = this.$tc('swag-customized-products.detail.tabGeneral.cardOption.errorSave');

            this.createNotificationError({
                title: titleSaveError,
                message: messageSaveError
            });
        },

        setMediaItem({ targetId }) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((response) => {
                this.displayMediaItem = response;
            });
            this.template.mediaId = targetId;
        },

        onDropMedia(mediaEntity) {
            this.setMediaItem({ targetId: mediaEntity.id });
        },

        setMediaFromSidebar({ mediaEntity }) {
            this.setMediaItem({ targetId: mediaEntity.id });
        },

        onUnlinkImage() {
            this.setMediaItem({ targetId: null });
        },

        openMediaSidebar() {
            if (!Shopware.Utils.object.hasOwnProperty(this.parentRefs, 'mediaSidebarItem')) {
                return;
            }

            this.parentRefs.mediaSidebarItem.openContent();
        },

        getOptionColumns() {
            return [{
                property: 'displayName',
                label: this.$tc('swag-customized-products.detail.tabGeneral.cardOption.labelName'),
                inlineEdit: 'string',
                sortable: false
            }, {
                property: 'type',
                label: this.$tc('swag-customized-products.detail.tabGeneral.cardOption.labelType'),
                inlineEdit: true,
                sortable: false
            }, {
                property: 'valuesCount',
                label: this.$tc('swag-customized-products.detail.tabGeneral.cardOption.labelOptionValuesCount'),
                align: 'center',
                sortable: false
            }, {
                property: 'position',
                label: this.$tc('swag-customized-products.detail.tabGeneral.cardOption.labelPosition'),
                align: 'center',
                sortable: false
            }];
        },

        async getOptionList() {
            this.isLoading = true;

            this.options = await this.optionRepository.search(this.getListCriteria(), this.versionContext);
            await this.refreshGrid();

            this.isLoading = false;

            return this.options;
        },

        refreshGrid() {
            if (!this.$refs.grid) {
                return Promise.resolve();
            }

            return this.$refs.grid.load();
        }
    }
});
