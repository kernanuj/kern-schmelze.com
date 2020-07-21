import './swag-customized-products-condition-tree.scss';
import template from './swag-customized-products-condition-tree.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-customized-products-condition-tree', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        templateId: {
            type: String,
            required: true
        },
        exclusionId: {
            type: String,
            required: true
        },
        context: {
            type: Object,
            required: true
        },
        conditions: {
            type: Array,
            required: true
        }
    },

    computed: {
        conditionRepository() {
            return this.repositoryFactory.create('swag_customized_products_template_exclusion_condition');
        },

        optionCriteria() {
            return this.getOptionCriteria();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.conditions.length <= 0) {
                this.addConditions(2);
            }
        },

        /**
         * Adds {count} conditions to the collection
         *
         * @param {Number} count
         */
        addConditions(count) {
            // eslint-disable-next-line no-plusplus
            for (let i = 0; i < count; i++) {
                const condition = this.conditionRepository.create();
                condition.templateExclusionId = this.exclusionId;
                condition.templateOptionVersionId = this.context.versionId;

                this.conditions.add(condition);
            }
        },

        deleteCondition(conditionIndex) {
            this.$emit('delete-condition', conditionIndex);
        },

        getOptionCriteria() {
            const optionCriteria = new Criteria();

            optionCriteria.addFilter(
                Criteria.multi(
                    'AND',
                    [
                        Criteria.equals('templateId', this.templateId),
                        Criteria.not(
                            'and',
                            [
                                // ToDo PT-11904 - remove supported types from blacklist
                                Criteria.equalsAny(
                                    'type',
                                    [
                                        'colorpicker',
                                        'fileupload',
                                        'imageupload'
                                    ]
                                )
                            ]
                        ),
                        Criteria.equals('required', false)
                    ]
                )
            ).addSorting(Criteria.sort('position'));

            const selectedOptionIds = this.conditions.reduce((acc, node) => {
                if (node.optionId) {
                    acc.push(node.optionId);
                }

                return acc;
            }, []);

            if (selectedOptionIds.length > 0) {
                optionCriteria.addFilter(
                    Criteria.not(
                        'and',
                        [Criteria.equalsAny('id', selectedOptionIds)]
                    )
                );
            }

            return optionCriteria;
        }
    }
});
