import './component/sendcloud-container';
import './component/sendcloud-content-window';
import './component/sendcloud-banner';
import './component/sendcloud-fonts';
import './component/sendcloud-main-text';
import './component/sendcloud-title';
import './component/sendcloud-button';
import './component/sendcloud-logo';
import './page/sendcloud-index';
import './page/sendcloud-welcome';
import './page/sendcloud-dashboard';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Module.register('sendcloud-shipping', {
    type: 'plugin',
    name: 'send-cloud.basic.label',
    title: 'send-cloud.basic.label',
    description: 'send-cloud.basic.description',
    color: '#1D97FF',
    icon: 'default-action-cloud-upload',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'sendcloud-index',
            path: ':page?'
        }
    },

    navigation: [{
        label: 'send-cloud.basic.label',
        color: '#1D97FF',
        path: 'sendcloud.shipping.index',
        icon: 'default-action-cloud-upload'
    }]
});