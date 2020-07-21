import './swag-customized-products-dynamic-form-field-renderer.scss';

const { Component, Filter } = Shopware;

/**
 * Builds the complete value label with surcharge price information
 * @param {Object} option - Current Option(Value) which price and oneTimeSurcharge information
 * @param {Object} context
 * @param {bool} bold - Add strong-tag to the surcharge display
 * @returns {String}
 */
function createPriceTag(option, context, bold = false) {
    const surcharge = option.price;

    if (!surcharge) {
        return option.displayName;
    }

    const currency = context.currency;
    const oneTimeTag = option.oneTimeSurcharge === true
        ? context.$tc('swag-customized-products-configuration-modal.oneTimeSurchargeSuffix')
        : context.$tc('swag-customized-products-configuration-modal.perItemSuffix');
    let currencyPriceTag = `${Filter.getByName('currency')(surcharge, currency.shortName)} ${oneTimeTag}`;

    if (surcharge > 0) {
        currencyPriceTag = `+${currencyPriceTag}`;
    }

    currencyPriceTag = (bold === true) ? `<strong>(${currencyPriceTag})</strong>` : `(${currencyPriceTag})`;

    return `${option.displayName} ${currencyPriceTag}`;
}

/**
 * Renders a text field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderTextField(h, context) {
    return h('sw-text-field', {
        props: {
            value: context.option.value
        },
        attrs: {
            label: createPriceTag(context.option, context),
            disabled: true,
            readonly: true
        }
    });
}

/**
 * Renders a textarea field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderTextArea(h, context) {
    return h('sw-textarea-field', {
        props: {
            value: context.option.value
        },
        attrs: {
            label: createPriceTag(context.option, context),
            disabled: true,
            readonly: true
        }
    });
}

/**
 * Renders a number field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderNumberField(h, context) {
    return h('sw-number-field', {
        props: {
            value: window.parseFloat(context.option.value)
        },
        attrs: {
            label: createPriceTag(context.option, context),
            disabled: true,
            readonly: true
        }
    });
}

/**
 * Renders a checkbox field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderCheckbox(h, context) {
    return h('sw-switch-field', {
        attrs: {
            label: createPriceTag(context.option, context),
            value: true,
            disabled: true
        }
    });
}

/**
 * Renders a selection field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderSelect(h, context) {
    const labelComponents = context.option.value.reduce((accumulator, currentValue) => {
        accumulator.push(h('sw-label', {
            class: 'swag-customized-products-configuration-modal__label',
            domProps: {
                innerHTML: createPriceTag(currentValue, context, true)
            }
        }));
        return accumulator;
    }, []);

    const wrapperTemplate = h('template', {
        slot: 'sw-field-input'
    }, labelComponents);

    return h('sw-block-field', {
        class: 'swag-customized-products-configuration-modal__tagged-field',
        attrs: {
            label: createPriceTag(context.option, context)
        }
    }, [wrapperTemplate]);
}

/**
 * Renders a color select
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderColorSelect(h, context) {
    const labelComponents = context.option.value.reduce((accumulator, currentValue) => {
        const colorPreview = `<div style="background-color: ${currentValue.value};"
                                   class="swag-customized-products-configuration-modal__color-preview"></div>`;

        accumulator.push(h('sw-label', {
            class: {
                'swag-customized-products-configuration-modal__label': true,
                'swag-customized-products-configuration-modal__label-colorselect': true
            },
            domProps: {
                innerHTML: colorPreview + createPriceTag(currentValue, context, true)
            }
        }));
        return accumulator;
    }, []);

    const wrapperTemplate = h('template', {
        slot: 'sw-field-input'
    }, labelComponents);

    return h('sw-block-field', {
        class: 'swag-customized-products-configuration-modal__tagged-field',
        attrs: {
            label: createPriceTag(context.option, context)
        }
    }, [wrapperTemplate]);
}

/**
 * Renders the image display modal of an image select
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderInnerImageModal(h, context) {
    const imageComponent = h('img', {
        class: 'swag-customized-products-configuration-modal__image-content',
        attrs: {
            src: this.imageModal.url
        }
    });

    const imageWrapper = h('div', {
        class: 'swag-customized-products-configuration__image-wrapper'
    }, [imageComponent]);

    return h('sw-modal', {
        class: 'swag-customized-products-configuration__image-modal',
        props: {
            title: this.imageModal.label,
            variant: 'full'
        },
        scopedSlots: {
            default: () => imageWrapper
        },
        on: {
            'modal-close': () => {
                context.showImageModal = false;
            }
        }
    });
}

/**
 * Renders the labels of an image select
 * @param {Function} h
 * @param {Object} context
 * @returns {Array<VNode>}
 */
function renderImageLabelComponents(h, context) {
    return context.option.value.reduce((accumulator, currentValue) => {
        const optionValueIcon = h('sw-icon', {
            class: 'swag-customized-products-configuration-modal__image-label-icon',
            props: {
                name: 'small-eye',
                size: '12px'
            }
        });

        const optionValueContent = h('span', {
            domProps: {
                innerHTML: createPriceTag(currentValue, context, true)
            }
        });

        accumulator.push(h('sw-label', {
            class: {
                'swag-customized-products-configuration-modal__label': true,
                'swag-customized-products-configuration-modal__label--clickable': true
            },
            scopedSlots: {
                default: () => [optionValueContent, optionValueIcon]
            },
            on: {
                selected: () => {
                    this.imageModal = {
                        label: currentValue.displayName,
                        url: currentValue.value.url
                    };
                    context.showImageModal = true;
                }
            }
        }));
        return accumulator;
    }, []);
}

/**
 * Renders an image select
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderImageSelect(h, context) {
    let innerImageModal = null;

    if (context.showImageModal === true) {
        innerImageModal = renderInnerImageModal(h, context);
    }

    const labelComponents = renderImageLabelComponents(h, context);
    const wrapperTemplate = h('template', {
        slot: 'sw-field-input'
    }, labelComponents);

    const blockFieldComponent = h('sw-block-field', {
        class: 'swag-customized-products-configuration-modal__tagged-field',
        attrs: {
            label: createPriceTag(context.option, context)
        }
    }, [wrapperTemplate]);

    return h('div', {}, [blockFieldComponent, innerImageModal]);
}

/**
 * Renders a timestamp field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderTimestamp(h, context) {
    const date = new Date(context.option.value);

    // ToDo PT-11138 - Switch `sw-text-field` to `sw-date-picker` if it can be disabled
    return h('sw-text-field', {
        props: {
            value: date.toISOString().split('T')[1].split('.')[0]
        },
        attrs: {
            label: createPriceTag(context.option, context),
            disabled: true,
            readonly: true
        }
    });
}

/**
 * Renders a date time field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderDateTime(h, context) {
    const date = new Date(context.option.value);

    // ToDo PT-11138 - Switch `sw-text-field` to `sw-date-picker` if it can be disabled
    return h('sw-text-field', {
        props: {
            value: date.toISOString().split('T')[0]
        },
        attrs: {
            label: createPriceTag(context.option, context),
            disabled: true,
            readonly: true
        }
    });
}

/**
 * Renders a htmleditor field
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderHtmlEditor(h, context) {
    const optionLabel = h('div', {
        class: 'sw-field__label'
    }, [
        h('label', {
            class: 'swag-customized-products-html-renderer__label',

            attrs: {
                for: 'swag-customized-products-html-renderer__display'
            },

            domProps: {
                innerHTML: createPriceTag(context.option, context)
            }
        })
    ]);

    const optionHtmlRendered = h('div', {
        class: [
            'sw-block-field__block',
            'swag-customized-products-html-renderer__display'
        ],

        domProps: {
            innerHTML: context.option.value
        }
    });

    return h('div', {
        class: [
            'sw-field',
            'sw-block-field',
            'sw-field--textarea',
            'is--disabled',
            'sw-field--default',
            'swag-customized-products-html-renderer'
        ]
    }, [
        optionLabel,
        optionHtmlRendered
    ]);
}

function renderImageUpload(h, context) {
    return h('swag-customized-products-media-preview', {
        props: {
            mediaItems: context.option.media,
            optionId: context.option.id,
            label: createPriceTag(context.option, context)
        }
    });
}

/**
 * Converts an option to a rendered component
 * @param {Function} h
 * @param {Object} context
 * @returns {VNode}
 */
function renderComponent(h, context) {
    switch (context.option.type) {
        case 'textarea':
            return renderTextArea(h, context);

        case 'numberfield':
            return renderNumberField(h, context);

        case 'checkbox':
            return renderCheckbox(h, context);

        case 'timestamp':
            return renderTimestamp(h, context);

        case 'datetime':
            return renderDateTime(h, context);

        case 'select':
            return renderSelect(h, context);

        case 'colorselect':
            return renderColorSelect(h, context);

        case 'imageselect':
            return renderImageSelect(h, context);

        case 'htmleditor':
            return renderHtmlEditor(h, context);

        case 'fileupload':
        case 'imageupload':
            return renderImageUpload(h, context);

        default:
            return renderTextField(h, context);
    }
}

Component.register('swag-customized-products-dynamic-form-field-renderer', {
    props: {
        option: {
            type: Object,
            required: true
        },
        currency: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            showImageModal: false
        };
    },

    render(h) {
        return renderComponent(h, this);
    }
});
