import './acl';

import './component/sw-social-shopping-channel-network-base';
import './component/sw-social-shopping-channel-network-facebook';
import './component/sw-social-shopping-channel-network-instagram';
import './component/sw-social-shopping-channel-network-google-shopping';
import './component/sw-social-shopping-channel-network-pinterest';
import './component/sw-social-shopping-channel-integration-step';
import './component/sw-social-shopping-channel-integration';
import './component/sw-social-shopping-channel-error';

import './extension/sw-sales-channel-detail';
import './extension/sw-sales-channel-detail-base';
import './extension/sw-sales-channel-create';
import './extension/sw-sales-channel-create-base';
import './extension/sw-sales-channel-menu';
import './extension/sw-sales-channel-modal-grid';

import './app/init/swag-social-shopping-svg-icons.init';

import SocialShoppingService from './service/social-shopping.api.service';

Shopware.Module.register('swag-social-shopping', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.sales.channel.detail') {
            currentRoute.children.push({
                component: 'sw-social-shopping-channel-error',
                name: 'sw.sales.channel.detail.socialShoppingErrors',
                isChildren: true,
                path: '/sw/sales/channel/detail/:id/socialShoppingErrors'
            });

            currentRoute.children.push({
                component: 'sw-social-shopping-channel-integration',
                name: 'sw.sales.channel.detail.socialShoppingIntegration',
                isChildren: true,
                path: '/sw/sales/channel/detail/:id/socialShoppingIntegration'
            });
        }

        next(currentRoute);
    }
});
Shopware.Application.addServiceProvider('socialShoppingService', () => {
    const initContainer = Shopware.Application.getContainer('init');
    return new SocialShoppingService(initContainer.httpClient, Shopware.Service('loginService'));
});
