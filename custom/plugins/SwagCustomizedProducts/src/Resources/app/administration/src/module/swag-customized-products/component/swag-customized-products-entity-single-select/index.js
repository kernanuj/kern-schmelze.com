import template from './swag-customized-products-entity-single-select.html.twig';
import './swag-customized-products-entity-single-select.scss';

const { Component } = Shopware;

Component.extend('swag-customized-products-entity-single-select', 'sw-entity-single-select', {
    template,

    props: {
        showSelectionError: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    computed: {
        results() {
            if (this.singleSelection && this.resultCollection) {
                const collection = this.createCollection(this.resultCollection);
                collection.push(this.singleSelection);
                this.resultCollection.forEach((item) => {
                    if (Object.prototype.hasOwnProperty.call(item, 'typeProperties') &&
                            Object.prototype.hasOwnProperty.call(item.typeProperties, 'isMultiSelect') &&
                            item.typeProperties.isMultiSelect === true) {
                        return;
                    }

                    if (item.id !== this.singleSelection.id) {
                        collection.add(item);
                    }
                });
                return collection;
            }

            return this.resultCollection;
        }
    },

    methods: {
        loadSelected() {
            if (this.value === '' || this.value === null) {
                return Promise.resolve();
            }

            this.isLoading = true;
            return this.repository.get(this.value, this.context).then((item) => {
                this.singleSelection = item;
                this.isLoading = false;
                this.$emit('load', item);
                return item;
            });
        },

        displaySearch(result) {
            result = this.filterResult(result);

            if (!this.resultCollection) {
                this.resultCollection = result;
            } else {
                result.forEach(item => {
                    // Prevent duplicate entries
                    if (!this.resultCollection.has(item.id)) {
                        this.resultCollection.push(item);
                    }
                });
            }
        },

        filterResult(result) {
            return result.filter((item) => {
                if (Object.prototype.hasOwnProperty.call(item, 'typeProperties') &&
                        Object.prototype.hasOwnProperty.call(item.typeProperties, 'isMultiSelect') &&
                        item.typeProperties.isMultiSelect === true) {
                    return false;
                }

                return true;
            });
        }
    }
});
