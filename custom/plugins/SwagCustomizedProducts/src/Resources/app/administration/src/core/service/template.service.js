/**
 * Helper service for Customized Product Templates
 * @class
 */
class SwagCustomizedProductsTemplateService {
    /**
     * Duplicates a given template and sets specific values
     *
     * @param {Entity} template
     * @param {String} copySuffix
     * @return {Promise}
     */
    duplicateTemplate(template, copySuffix = '') {
        const overrideOptions = template.options.reduce(this.reduceOverrideOptions.bind(this), []);

        const overrides = {
            internalName: `${template.internalName} ${copySuffix}`,
            displayName: `${template.displayName} ${copySuffix}`,
            active: false,
            options: overrideOptions
        };

        return Shopware.Service('repositoryFactory')
            .create('swag_customized_products_template')
            .clone(template.id, Shopware.Context.api, overrides);
    }

    /**
     * @param {Array} accumulator
     * @param {Entity} option
     * @return {Array}
     *
     * @private
     */
    reduceOverrideOptions(accumulator, option) {
        option.id = '';
        option.itemNumber = '';
        option.values = option.values.reduce(this.reduceOverrideOptionValues, []);
        accumulator.push(option);

        return accumulator;
    }

    /**
     * @param {Array} accumulator
     * @param {Entity} value
     * @return {Array}
     *
     * @private
     */
    reduceOverrideOptionValues(accumulator, value) {
        value.id = '';
        value.itemNumber = '';
        accumulator.push(value);

        return accumulator;
    }
}

export default SwagCustomizedProductsTemplateService;
