export default {
    type: ['datetime', 'timestamp'],

    validate: ({ element, operator }) => {
        return (operator === 'X' ? element.value.length > 0 : !element.value.length);
    }
};
