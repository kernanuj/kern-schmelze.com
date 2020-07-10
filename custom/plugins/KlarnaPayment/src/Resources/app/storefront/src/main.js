import KlarnaPayments from './klarna-payments/klarna-payments';
import KlarnaInstantShopping from './klarna-instant-shopping/klarna-instant-shopping';

window.PluginManager.register('KlarnaPayments', KlarnaPayments, '[data-is-klarna-payments]');
window.PluginManager.register('KlarnaInstantShopping', KlarnaInstantShopping, '[data-is-klarna-instant-shopping]');

if (module.hot) {
    module.hot.accept();
}
