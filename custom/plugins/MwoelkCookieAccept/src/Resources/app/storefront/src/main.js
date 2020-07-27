import CookiePlugin from './cookie-plugin/cookie-plugin.plugin';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('MwoelkCookiePlugin', CookiePlugin, '[data-mwoelk-cookie-plugin]');
