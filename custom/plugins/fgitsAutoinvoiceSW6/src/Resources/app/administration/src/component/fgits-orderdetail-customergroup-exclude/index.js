import template from './fgits-orderdetail-customergroup-exclude.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('fgits-orderdetail-customergroup-exclude', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        value: {
            type: Array,
            required: false,
            default() {
                return [];
            }
        },
    },

    data() {
        return {
            isLoading: false,
            options: [],
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('customer_group');
        },
        currentValue: {
            get() {
                if (!this.value) {
                    return [];
                }

                return this.value;
            },
            set(newValue) {
                this.$emit('input', newValue);
                this.$emit('change', newValue);
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            const criteria = new Criteria();
            criteria.addSorting(Criteria.sort('name', 'ASC'));
            this.isLoading = true;
            this.repository
                .search(criteria, Shopware.Context.api)
                .then((entity) => {
                    entity.forEach((item) => {
                        this.options.push(item);
                    });
                    this.isLoading = false;
                })
            ;
        }
    }
});
