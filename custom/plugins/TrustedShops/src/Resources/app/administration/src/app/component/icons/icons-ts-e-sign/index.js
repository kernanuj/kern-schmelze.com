import tsIcon from './icons-ts-e-sign.html';

Shopware.Component.register( 'icons-ts-e-sign', {
    name: 'icons-ts-e-sign',
    functional: true,
    render( createElement, elementContext ) {
        const data = elementContext.data;

        return createElement('span', {
            class: [data.staticClass, data.class],
            style: data.style,
            attrs: data.attrs,
            on: data.on,
            domProps: {
                innerHTML: tsIcon
            }
        });
    }
});
