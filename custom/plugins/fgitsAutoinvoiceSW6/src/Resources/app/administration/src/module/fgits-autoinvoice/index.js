const { Module } = Shopware;
import './page/fgits-autoinvoice-export';

Module.register('fgits-autoinvoice', {
    type: 'plugin',
    title: 'FgitsAutoInvoice',
    description: '',
    color: '#2087a7',
    icon: 'default-documentation-file',

    routes: {
        export: {
            component: 'fgits-autoinvoice-export',
            path: 'export'
        }
    },

    navigation: [{
        label: 'FgitsAutoInvoice',
        color: '#2087a7',
        path: 'fgits.autoinvoice.export',
        icon: 'default-documentation-file',
        parent: 'sw-order',
        position: 1000
    }],

    settingsItem: {
        group: 'system',
        to: 'fgits.autoinvoice.export',
        icon: 'default-documentation-file'
    }
});
