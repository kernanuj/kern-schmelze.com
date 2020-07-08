import template from './swag-customized-products-option-tree.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const { createId } = Shopware.Utils;
const { types } = Shopware.Utils;

Component.register('swag-customized-products-option-tree', {
    template,

    inject: ['repositoryFactory'],

    mixins: ['position'],

    props: {
        option: {
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
            currentVersionContext: null,
            rootElementId: {},
            optionValues: [],
            valuesToDelete: [],
            currentEditingId: null,
            lastNewOptionValuePosition: null,
            isLoading: true,
            isCreating: false,
            activeItemId: null
        };
    },

    computed: {
        templateOptionValueRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_option_value');
        },

        templateOptionId() {
            return this.option.id;
        },

        rootElement() {
            const { displayName, type, typeProperties, translated } = this.option;

            return {
                id: this.rootElementId,
                isRoot: true,
                _isNew: false,
                translated,
                displayName,
                type,
                typeProperties
            };
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.rootElementId = createId();
            this.setActiveItemId(this.rootElement.id);
            this.fetchOptionValues();
            this.$emit('save-method-add', this.saveAllOptionValues.bind(this));
        },

        fetchOptionValues() {
            const criteria = this.createOptionValueCriteria();

            this.isLoading = true;
            return this.templateOptionValueRepository.search(criteria, this.versionContext).then((collection) => {
                this.optionValues = collection;
                this.isLoading = false;

                return this.optionValues;
            });
        },

        createOptionValueCriteria() {
            return (new Criteria())
                .addFilter(Criteria.equals('templateOptionId', this.templateOptionId))
                .addAssociation('prices')
                .addSorting(Criteria.sort('position'))
                .addSorting(Criteria.sort('createdAt'))
                .setLimit(500);
        },

        onCreateNewElement() {
            // prevent double execution of click event before button is rendered as disabled
            this.$nextTick(() => {
                if (this.isCreating) {
                    return;
                }

                this.isCreating = true;

                this.getNewPosition(
                    this.templateOptionValueRepository,
                    this.createOptionValueCriteria(),
                    this.versionContext
                ).then((position) => {
                    this.constructNewElement(position);
                }).finally(() => {
                    this.isCreating = false;
                });
            });
        },

        constructNewElement(position) {
            // If multiple new options are added before saving, getNewPosition will always result in the same number
            this.lastNewOptionValuePosition = (this.lastNewOptionValuePosition >= position)
                ? this.lastNewOptionValuePosition + 1
                : position;

            const newElement = this.templateOptionValueRepository.create(this.versionContext);
            newElement.templateOptionId = this.templateOptionId;
            newElement.position = this.lastNewOptionValuePosition;

            this.$set(newElement, 'value', {});
            this.$set(newElement.value, '_value', '');

            this.currentEditingId = newElement.id;

            this.optionValues.add(newElement);
        },

        onEditDisplayName(name, item) {
            const entity = this.optionValues.get(item.id);
            entity.displayName = name;

            this.currentEditingId = null;
            this.setActiveItemId(entity.id);
        },

        onStartInlineEditing(id) {
            this.currentEditingId = id;
        },

        onCreateItemAbort(item) {
            this.currentEditingId = null;
            this.onDeleteItem(item);
        },

        onCreateItemBlur(item) {
            if (item.displayName) {
                return;
            }

            this.onCreateItemAbort(item);
        },

        onDeleteItem(item) {
            this.valuesToDelete.push(item.id);
            this.optionValues.remove(item.id);
            this.setActiveItemId(this.rootElement.id);
        },

        onSetActiveItem(item) {
            if (this.currentEditingId) {
                return;
            }

            this.setActiveItemId(item.id);
        },

        setActiveItemId(id) {
            let item;

            if (id === this.rootElement.id) {
                item = this.rootElement;
            } else {
                item = this.optionValues.get(id);
            }

            this.activeItemId = id;
            this.$emit('active-item-change', item);

            if (!this.checkRequired(item.percentageSurcharge)) {
                item.percentageSurcharge = 0;
            }
        },

        checkRequired(value) {
            return !types.isUndefined(value) && (
                types.isNumber(value) ||
                (types.isString(value) && value.length > 0)
            );
        },

        saveAllOptionValues() {
            const optionValuesToReload = [];
            const optionValuesErrors = [];

            if (this.optionValues.length === 0) {
                return Promise.reject(new Error('no-children'));
            }

            const saveOptionValues = this.optionValues.map((optionValue) => {
                return new Promise((resolve) => {
                    this.templateOptionValueRepository.save(optionValue, this.versionContext).then(() => {
                        optionValuesToReload.push(optionValue.id);
                    })
                        .catch((e) => {
                            optionValuesErrors.push(e);
                        })
                        .finally(() => resolve());
                });
            });

            return Promise.all(saveOptionValues)
                .then(() => Promise.all(optionValuesToReload.map((id) => this.reloadOptionValue(id))))
                .then(() => {
                    if (optionValuesErrors.length <= 0) {
                        return Promise.all(this.deleteOptionValues());
                    }

                    throw optionValuesErrors[0];
                });
        },

        deleteOptionValues() {
            return this.valuesToDelete.map((id) => {
                return new Promise((resolve) => {
                    this.templateOptionValueRepository.delete(id, this.versionContext)
                        .finally(() => resolve());
                });
            });
        },

        reloadOptionValue(id) {
            return this.templateOptionValueRepository.get(id, this.versionContext).then((newOptionValue) => {
                const index = this.optionValues.findIndex(i => i.id === id);
                this.optionValues.remove(id);
                this.optionValues.addAt(newOptionValue, index);
            });
        }
    }
});
