import KlarnaPayments from './klarna-payments/klarna-payments';

window.PluginManager.register('KlarnaPayments', KlarnaPayments, '[data-is-klarna-payments]');

if (module.hot) {
    module.hot.accept();
}
