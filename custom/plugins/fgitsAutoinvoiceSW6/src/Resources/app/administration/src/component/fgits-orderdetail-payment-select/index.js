import template from './fgits-orderdetail-payment-select.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('fgits-orderdetail-payment-select', {
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
            return this.repositoryFactory.create('state_machine_state');
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
            criteria.addAssociation('stateMachine');
            criteria.addFilter(
                Criteria.equals(
                    'state_machine_state.stateMachine.technicalName',
                    'order_transaction.state'
                )
            );
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
