import ApiService from 'src/core/service/api.service';

class AutoinvoiceService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'fgits_autoinvoice') {
        super(httpClient, loginService, apiEndpoint);
    }

    activateCron() {
        const route = '/fgits/cron/activate';

        return this.httpClient.get(
            route,
            {
                headers: this.getBasicHeaders()
            }
        );
    }

    sendInvoice(orderId) {
        const route = '/fgits/order/${orderId}/invoice/send';

        return this.httpClient.get(
            route,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default AutoinvoiceService;
