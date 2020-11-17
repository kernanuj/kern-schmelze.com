import './page/inv-reports-pro-index';
import './component/inventivo-banner';
//import './page/inv-reports-pro-product-stream-list';

const { Module } = Shopware;

Module.register('inv-reports-pro', {
    type: 'plugin',
    name: 'inv-reports-pro',
    title: 'inv-reports-pro.general.mainMenuItemGeneral',
    description: 'inv-reports-pro.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#6AD6F0',
    icon: 'default-object-lab-flask',
    favicon: 'icon-module-dashboard.png',

    routes: {
        index: {
            components: {
                default: 'inv-reports-pro-index'
            },
            path: 'index'
        }
    },

    navigation: [{
        id: 'inv-reports-pro',
        label: 'inv-reports-pro.general.mainMenuItemGeneral',
        color: '#6AD6F0',
        icon: 'default-object-lab-flask',
        path: 'inv.reports.pro.index',
        position: 10
    }]
});
