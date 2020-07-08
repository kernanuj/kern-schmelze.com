import template from './swag-customized-products-exclusion-list.html.twig';
import './swag-customized-products-exclusion-list.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-customized-products-exclusion-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        templateId: {
            type: String,
            required: true
        },

        context: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            exclusions: [],
            exclusionId: null,
            isLoading: false,
            showModal: false,
            selectedElements: {},
            showDeleteModal: false,
            currentName: '',
            searchTerm: ''
        };
    },

    computed: {
        exclusionRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_exclusion');
        },

        exclusionColumns() {
            return [
                {
                    property: 'name',
                    label: this.$tc('swag-customized-products.detail.tabGeneral.cardExclusion.labelName'),
                    sortable: true
                }
            ];
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.fetchExclusions();
        },

        fetchExclusions() {
            this.isLoading = true;

            return this.exclusionRepository.search(this.getListCriteria(), this.context).then((result) => {
                this.exclusions = result;

                if (this.$refs.grid) {
                    this.$refs.grid.applyResult(result);
                }

                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        getListCriteria() {
            return new Criteria(1, 10)
                .addFilter(Criteria.equals('templateId', this.templateId))
                .addSorting(Criteria.sort('name'))
                .setTerm(this.searchTerm);
        },

        onExclusionAdd() {
            this.showModal = true;
        },

        onExclusionModalClose(forceFetch = false) {
            this.showModal = false;
            this.exclusionId = null;

            if (!forceFetch) {
                return;
            }

            this.fetchExclusions();
        },

        onExclusionEdit(exclusion) {
            this.exclusionId = exclusion.id;
            this.showModal = true;
        },

        onExclusionDelete(exclusion) {
            this.showDeleteModal = true;
            this.selectedElements = {
                [exclusion.id]: exclusion
            };
            this.currentName = exclusion.name;
        },

        onExclusionsBulkDelete() {
            this.showDeleteModal = true;
        },

        onExclusionConfirmDelete() {
            const promises = Object.keys(this.selectedElements).reduce((accumulator, id) => {
                accumulator.push(this.exclusionRepository.deleteVersion(id, this.context.versionId, this.context));
                return accumulator;
            }, []);

            Promise.all(promises).then(() => {
                return this.fetchExclusions();
            }).then(() => {
                this.$nextTick(() => {
                    this.onExclusionCloseDeleteModal();
                });

                if (!this.$refs.grid) {
                    return;
                }

                this.$refs.grid.resetSelection();
            });
        },

        onExclusionCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onExclusionSelectionChanged(selection) {
            this.selectedElements = selection;
            const selectedItems = Object.values(this.selectedElements);

            if (selectedItems.length) {
                this.currentName = selectedItems[0].name;
            }
        },

        onExclusionSearch(term) {
            this.searchTerm = term;
            this.fetchExclusions();
        }
    }
});
