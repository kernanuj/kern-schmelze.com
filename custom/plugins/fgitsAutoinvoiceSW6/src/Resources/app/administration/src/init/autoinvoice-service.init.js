const { Application } = Shopware;

import AutoinvoiceService from '../service/api/autoinvoice.service';

Application.addServiceProvider('fgitsAutoinvoiceService', (container) => {
    const initContainer = Application.getContainer('init');

    return new AutoinvoiceService(initContainer.httpClient, container.loginService);
});
