import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'text-image-background-two-column',
    label: 'sw-cms.blocks.textImage.textImageBackgroundTwoColumn.label',
    category: 'text-image',
    component: 'sw-cms-block-text-image-background-two-column',
    previewComponent: 'sw-cms-preview-text-image-background-two-column',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        leftText: 'text',
        leftImage: {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'cover' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_camera_large.jpg'
                    }
                }
            }
        },
        rightText: 'text',
        rightImage: {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'cover' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_camera_large.jpg'
                    }
                }
            }
        }
    }
});
