import template from './fgits-orderdetail-payment-exclude.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('fgits-orderdetail-payment-exclude', {
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
            return this.repositoryFactory.create('payment_method');
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
        },
        getTitle() {
            return this.$attrs.label;
        },
        getHelpText() {
            return this.$attrs.helpText;
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
