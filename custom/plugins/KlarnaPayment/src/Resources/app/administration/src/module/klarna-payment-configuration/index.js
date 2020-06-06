import './extension/sw-settings-index';

import './page/klarna-payment-settings';
import './page/klarna-payment-wizard';

import deDE from './snippet/de_DE.json';
import enGB from './snippet/en_GB.json';

const { Module } = Shopware;

Module.register('klarna-payment-configuration', {
    type: 'plugin',
    name: 'KlarnaPayment',
    title: 'klarna-payment-configuration.module.title',
    description: 'klarna-payment-configuration.module.description',
    version: '1.0.0',
    targetVersion: '1.0.0',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        settings: {
            component: 'klarna-payment-settings',
            path: 'settings',
            meta: {
                parentPath: 'sw.settings.index'
            }
        },
        wizard: {
            component: 'klarna-payment-wizard',
            path: 'wizard',
            meta: {
                parentPath: 'sw.settings.index'
            }
        }
    }
});
