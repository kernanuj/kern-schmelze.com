import InvMixerProductMixer from './mixer/inv-mixer-product.mixer';

const PluginManager = window.PluginManager;
PluginManager.register('InvMixerProductMixer', InvMixerProductMixer, '[data-inv-mixer-product-mix]');
