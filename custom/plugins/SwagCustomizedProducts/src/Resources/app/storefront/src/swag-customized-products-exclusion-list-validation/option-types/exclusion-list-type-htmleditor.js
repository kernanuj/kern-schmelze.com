/* global $ */

export default {
    type: ['htmleditor'],

    validate: ({ element, operator }) => {
        return (operator === 'X' ? !$(element).summernote('isEmpty') : $(element).summernote('isEmpty'));
    }
};
