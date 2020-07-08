const { Component } = Shopware;

Component.extend('swag-customized-products-multi-select-extended', 'sw-multi-select', {
    props: {
        options: {
            type: Array,
            required: false,
            default() {
                return [];
            }
        },
        searchFunction: {
            type: Function,
            required: false,
            default({ options, labelProperty, valueProperty }) {
                const foundOptions = options.filter(option => {
                    const label = option[labelProperty];
                    if (!label) {
                        return false;
                    }
                    return label.toLowerCase() === this.searchTerm.toLowerCase();
                });

                if (foundOptions.length > 0) {
                    return foundOptions;
                }

                const newOption = {};
                newOption[labelProperty] = this.searchTerm;
                newOption[valueProperty] = this.searchTerm;
                return [newOption];
            }
        }
    },

    created() {
        this.$on('item-add', this.onItemAdded);
        this.$on('item-remove', this.onItemRemoved);
    },

    watch: {
        value: {
            handler(newValue) {
                this.options.splice(0);
                newValue.forEach(
                    (option) => {
                        const newOption = {};
                        newOption[this.labelProperty] = option;
                        newOption[this.valueProperty] = option;
                        this.options.push(newOption);
                    }
                );
            },
            deep: true,
            immediate: true
        }
    },

    methods: {
        addItem(item) {
            if (item === undefined) {
                return;
            }

            const identifier = item[this.valueProperty];

            if (this.isSelected(item)) {
                this.remove(item);
                return;
            }

            this.$emit('item-add', item);

            this.currentValue = [...this.currentValue, identifier];

            this.$refs.selectionList.focus();
            this.$refs.selectionList.select();
        },

        onItemAdded(item) {
            const duplicates = this.options.find((o) => {
                return (
                    o[this.valueProperty] === item[this.valueProperty] &&
                    o[this.labelProperty] === item[this.labelProperty]
                );
            });

            if (duplicates === undefined) {
                this.options.push(item);
            }

            this.searchTerm = '';
            this.$refs.selectionList.focus();
            this.$refs.selectionList.select();
        },

        onItemRemoved(item) {
            this.options.splice(this.options.findIndex((option) => {
                return (
                    option[this.valueProperty] === item[this.valueProperty] &&
                    option[this.labelProperty] === item[this.labelProperty]
                );
            }), 1);
            this.searchTerm = '';
        },

        onSearchTermChange(term) {
            this.searchTerm = term;
            this.$emit('search-term-change', this.searchTerm);
            this.resetActiveItem();
        }
    }
});
