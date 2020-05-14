import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'image-text-gallery-six-columns',
    label: 'sw-cms.blocks.textImage.imageTextGallerySixColumns.label',
    category: 'text-image',
    component: 'sw-cms-block-image-text-gallery-six-columns',
    previewComponent: 'sw-cms-preview-image-text-gallery-six-columns',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        'first-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_camera_large.jpg'
                    }
                }
            }
        },
        'first-text': {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p style="text-align: center;">Lorem</p>
                        `.trim()
                    }
                }
            }
        },
        'second-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_plant_large.jpg'
                    }
                }
            }
        },
        'second-text': {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p style="text-align: center;">Ipsum</p>
                        `.trim()
                    }
                }
            }
        },
        'third-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_glasses_large.jpg'
                    }
                }
            }
        },
        'third-text': {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p style="text-align: center;">Dolor</p>
                        `.trim()
                    }
                }
            }
        },
        'fourth-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_glasses_large.jpg'
                    }
                }
            }
        },
        'fourth-text': {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p style="text-align: center;">Lorem</p>
                        `.trim()
                    }
                }
            }
        },
        'fifth-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_glasses_large.jpg'
                    }
                }
            }
        },
        'fifth-text': {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p style="text-align: center;">Ipsum</p>
                        `.trim()
                    }
                }
            }
        },
        'sixth-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_glasses_large.jpg'
                    }
                }
            }
        },
        'sixth-text': {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p style="text-align: center;">Dolor</p>
                        `.trim()
                    }
                }
            }
        }
    }
});
