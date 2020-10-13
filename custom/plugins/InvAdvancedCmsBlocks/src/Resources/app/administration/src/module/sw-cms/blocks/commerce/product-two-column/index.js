import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'product-two-column',
    label: 'sw-cms.blocks.commerce.productTwoColumn.label',
    category: 'commerce',
    component: 'sw-cms-block-product-two-column',
    previewComponent: 'sw-cms-preview-product-two-column',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        left: 'product-box',
        right: 'product-box',
    }
});
