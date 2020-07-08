import './page/swag-customized-products-list';
import './page/swag-customized-products-detail';

import './view/swag-customized-products-detail-base';

import './component/swag-customized-products-condition-tree';
import './component/swag-customized-products-condition-tree-node';
import './component/swag-customized-products-option-detail-modal';
import './component/swag-customized-products-multi-select-extended';
import './component/swag-customized-products-exclusion-list';
import './component/swag-customized-products-exclusion-modal';
import './component/swag-customized-products-exclusion-delete-modal';
import './component/swag-customized-products-entity-single-select';
import './component/swag-customized-products-condition-tree-node-error';
import './component/swag-customized-products-media-preview';
import './component/swag-customized-products-media-media-item';

import './component/option-type-forms/option-type-tree/swag-customized-products-option-tree';
import './component/option-type-forms/option-type-tree/swag-customized-products-option-tree-item';
import './component/option-type-forms/option-type-tree/swag-customized-products-option-tree-content';
import './component/option-type-forms';

const { Module } = Shopware;

Module.register('swag-customized-products', {
    type: 'plugin',
    name: 'customized-products',
    title: 'swag-customized-products.general.mainMenuItemGeneral',
    description: 'swag-customized-products.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'default-symbol-products',
    favicon: 'icon-module-products.png',
    entity: 'swag_customized_products_template',

    routes: {
        index: {
            component: 'swag-customized-products-list',
            path: 'index'
        },

        detail: {
            component: 'swag-customized-products-detail',
            path: 'detail/:id',
            props: { default(route) {
                return { templateId: route.params.id };
            } },
            redirect: {
                name: 'swag.customized.products.detail.base'
            },
            children: {
                base: {
                    component: 'swag-customized-products-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'swag.customized.products.index'
                    }
                }
            }
        },

        create: {
            component: 'swag-customized-products-detail',
            path: 'create',
            redirect: {
                name: 'swag.customized.products.create.base'
            },
            children: {
                base: {
                    component: 'swag-customized-products-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'swag.customized.products.index'
                    }
                }
            },
            meta: {
                parentPath: 'swag.customized.products.index'
            }
        }
    },

    navigation: [{
        id: 'swag-customized-products',
        path: 'swag.customized.products.index',
        label: 'swag-customized-products.general.mainMenuItemGeneral',
        parent: 'sw-catalogue',
        position: 35
    }]
});
