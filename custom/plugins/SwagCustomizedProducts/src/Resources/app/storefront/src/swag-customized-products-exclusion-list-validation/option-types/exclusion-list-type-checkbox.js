export default {
    type: ['checkbox'],

    validate: ({ element, operator }) => {
        return (operator === 'X' ? element.checked : !element.checked);
    }
};
