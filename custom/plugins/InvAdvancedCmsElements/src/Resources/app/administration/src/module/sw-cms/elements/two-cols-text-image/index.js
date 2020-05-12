// Todo: https://docs.shopware.com/en/shopware-platform-dev-en/how-to/custom-cms-element
import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'two-cols-text-image',
    label: 'sw-cms.elements.customTextImageElement.label',
    component: 'sw-cms-el-two-cols-text-image',
    configComponent: 'sw-cms-el-config-two-cols-text-image',
    previewComponent: 'sw-cms-el-preview-two-cols-text-image',
    defaultConfig: {
        imageContent: {
            source: 'static',
            value: `
                <h2>Lorem Ipsum dolor sit amet</h2>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
                Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
                At vero eos et accusam et justo duo dolores et ea rebum.
                Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
            `.trim()
        },
        imageSrc: {
            source: 'static',
            value: null,
            required: true,
            entity: {
                name: 'imageSrc'
            }
        },
        displayMode: {
            source: 'static',
            value: 'standard'
        },
        url: {
            source: 'static',
            value: null
        },
        newTab: {
            source: 'static',
            value: false
        },
        minHeight: {
            source: 'static',
            value: '340px'
        },
        verticalAlign: {
            source: 'static',
            value: null
        }
    }
});
