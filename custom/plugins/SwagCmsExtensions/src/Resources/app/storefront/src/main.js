import SwagCmsExtensionsQuickview
    from './swag-cms-extensions-quickview/swag-cms-extensions-quickview.plugin';
import SwagCmsExtensionsListingPluginExtension
    from './plugin-extensions/listing/swag-cms-extensions-listing-extension.plugin';
import SwagCmsExtensionsVariantSwitchExtension
    from './plugin-extensions/variant-switch/swag-cms-extensions-variant-switch-extension.plugin';

import SwagCmsExtensionsScrollNavigation
    from './swag-cms-extensions-scroll-navigation/swag-cms-extensions-scroll-navigation.plugin';
import SwagCmsExtensionsScrollNavigationToggleMenu
    from './swag-cms-extensions-scroll-navigation/swag-cms-extensions-scroll-navigation-toggle-menu.plugin';

function registerPlugins(manager) {
    manager.register(
        'SwagCmsExtensionsQuickview',
        SwagCmsExtensionsQuickview,
        '[data-swag-cms-extensions-quickview="true"]'
    );

    manager.register(
        'SwagCmsExtensionsScrollNavigation',
        SwagCmsExtensionsScrollNavigation,
        '[data-swag-cms-extensions-scroll-navigation="true"]'
    );

    manager.register(
        'SwagCmsExtensionsScrollNavigationToggleMenu',
        SwagCmsExtensionsScrollNavigationToggleMenu,
        '[data-swag-cms-extensions-scroll-navigation-toggle-menu="true"]'
    );
}

function overridePlugins(manager) {
    manager.override(
        'Listing',
        SwagCmsExtensionsListingPluginExtension,
        '[data-listing]'
    );

    manager.override(
        'VariantSwitch',
        SwagCmsExtensionsVariantSwitchExtension,
        '[data-variant-switch]'
    );
}

registerPlugins(window.PluginManager);
overridePlugins(window.PluginManager);

if (module.hot) {
    module.hot.accept();
}
