import './component/ts-settings-start-banner';
import './page/ts-settings-index';
import './view/ts-settings-index-start';
import './view/ts-settings-index-shopreviews';
import './view/ts-settings-index-productreviews';
import './view/ts-settings-index-reviewrequests';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Module.register('ts-settings', {
    type: 'plugin',
    name: 'ts-settings',
    title: 'ts-settings.general.title',
    description: 'ts-settings.general.description',
    color: '#FFDC0F',
    icon: 'ts-e-sign',
    favicon: '../../../../../trustedshops/administration/img/favicon/icon-module-trustedshops.png',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'ts-settings-index',
            path: 'index',
            redirect: {
                name: 'ts.settings.index.start'
            },
            children: {
                start: {
                    component: 'ts-settings-index-start',
                    path: 'start'
                },
                shopreviews: {
                    component: 'ts-settings-index-shopreviews',
                    path: 'shopreviews'
                },
                productreviews: {
                    component: 'ts-settings-index-productreviews',
                    path: 'productreviews'
                },
                reviewrequests: {
                    component: 'ts-settings-index-reviewrequests',
                    path: 'reviewrequests'
                }
            }
        },
    },

    navigation: [{
        id: 'ts-settings',
        label: 'ts-settings.general.title',
        color: '#000000',
        path: 'ts.settings.index',
        icon: 'ts-e-sign'
    }]
});
