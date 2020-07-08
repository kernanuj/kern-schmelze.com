export default {
    type: ['numberfield'],

    validate: ({ element, operator }) => {
        const defaultValue = element.defaultValue;
        return (operator === 'X' ? element.value !== defaultValue : element.value === defaultValue);
    }
};
