import template from './swag-customized-products-exclusion-modal.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const { mapApiErrors } = Shopware.Component.getComponentHelper();

Component.register('swag-customized-products-exclusion-modal', {
    template,

    inject: [
        'repositoryFactory',
        'ruleConditionDataProviderService'
    ],

    props: {
        templateId: {
            type: String,
            required: true
        },

        exclusionId: {
            type: String,
            required: false,
            default: ''
        },

        context: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            exclusion: null,
            isLoading: false
        };
    },

    computed: {
        exclusionRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_exclusion');
        },

        conditionRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_exclusion_condition');
        },

        ruleConditionRepository() {
            return this.repositoryFactory.create('rule_condition');
        },

        associationValue() {
            if (!this.exclusion.ruleId) {
                return '';
            }

            return this.exclusion.ruleId;
        },

        ...mapApiErrors('exclusion', ['name'])
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.isLoading = true;

            if (this.exclusionId) {
                const criteria = new Criteria(1, 1);
                criteria.setIds([this.exclusionId]);
                criteria.addAssociation('conditions.templateExclusionOperator');

                this.exclusionRepository.search(criteria, this.context).then((result) => {
                    this.exclusion = result.first();
                    this.isLoading = false;
                }).catch(() => {
                    this.isLoading = false;
                });

                return;
            }

            this.exclusion = this.exclusionRepository.create(this.context);
            this.exclusion.templateId = this.templateId;
            this.isLoading = false;
        },

        onSave() {
            this.isLoading = true;

            this.exclusionRepository.save(this.exclusion, this.context).then(() => {
                this.isLoading = false;
                this.onClose(true);
            }).catch(() => {
                this.isLoading = false;
            });
        },

        /**
         * Deletes the condition at the given index.
         * For the indexes 0 and 1 the condition is unset, to keep at least two conditions.
         *
         * @param {Number} conditionIndex
         */
        deleteCondition(conditionIndex) {
            if (this.exclusion.conditions.length <= 2) {
                const id = this.exclusion.conditions.getAt(conditionIndex).id;
                this.exclusion.conditions.remove(id);

                const condition = this.conditionRepository.create();
                condition.templateExclusionId = this.exclusionId;
                condition.templateOptionVersionId = this.context.versionId;

                this.exclusion.conditions.add(condition);

                return;
            }

            this.exclusion.conditions = this.exclusion.conditions.filter((node, index) => {
                return index !== conditionIndex;
            });
        },

        onClose(fetchResults = false) {
            this.$emit('modal-close', fetchResults);
        }
    }
});
