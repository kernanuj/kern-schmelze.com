import template from './swag-customized-products-list.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-customized-products-list', {
    template,

    inject: [
        'repositoryFactory',
        'SwagCustomizedProductsTemplateService'
    ],

    mixins: [
        'notification',
        'listing',
        'placeholder'
    ],

    data() {
        return {
            isLoading: false,
            items: null,
            total: 0
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        templateRepository() {
            return this.repositoryFactory.create('swag_customized_products_template');
        },

        templateCriteria() {
            return (new Criteria())
                .addAssociation('media')
                .addAssociation('options')
                .addAssociation('options.values')
                .addSorting(Criteria.sort('updatedAt', 'DESC'));
        },

        templateColumns() {
            return this.getTemplateColumns();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();
        },

        getList() {
            this.isLoading = true;

            this.templateRepository.search(this.templateCriteria, Shopware.Context.api).then((response) => {
                this.items = response;
                this.total = response.total;

                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        onInlineEditSave(promise, entity) {
            this.isLoading = true;
            const templateName = entity.internalName || this.placeholder(entity, 'internalName');

            return promise.then(() => {
                this.getList();
                this.isLoading = false;
            }).catch(() => {
                this.getList();
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('global.notification.notificationSaveErrorMessage', 0, { entityName: templateName })
                });
            });
        },

        getTemplateColumns() {
            return [{
                property: 'internalName',
                label: this.$tc('swag-customized-products.list.columnInternalName'),
                routerLink: 'swag.customized.products.detail',
                inlineEdit: 'string',
                naturalSorting: true,
                allowResize: true
            }, {
                property: 'displayName',
                label: this.$tc('swag-customized-products.list.columnDisplayName'),
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'options',
                label: this.$tc('swag-customized-products.list.columnOptionsCount'),
                sortable: false,
                align: 'right',
                allowResize: true
            }, {
                property: 'description',
                label: this.$tc('swag-customized-products.list.columnDescription'),
                allowResize: true
            }];
        },

        updateTotal({ total }) {
            this.total = total;
        },

        onChangeLanguage() {
            this.getList();
        },

        onDuplicate(productTemplate) {
            const copySuffix = this.$tc('swag-customized-products.general.duplicateNameSuffix');
            const promise = this.SwagCustomizedProductsTemplateService.duplicateTemplate(productTemplate, copySuffix);

            return promise.then((duplicate) => {
                this.$router.push({
                    name: 'swag.customized.products.detail',
                    params: { id: duplicate.id }
                });
            });
        }
    }
});
