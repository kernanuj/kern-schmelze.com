export default {
    type: ['colorselect', 'imageselect', 'select'],

    validate: ({ element, operator }) => {
        const elementType = element.tagName.toLowerCase();
        if (elementType === 'select') {
            return (operator === 'X' ? element.value.length > 0 : !element.value.length);
        }

        // We're dealing with the default selection of the select element, therefore the condition has to be flipped
        return (operator === 'X' ? !element.checked : element.checked);
    }
};
