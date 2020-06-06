import ApiService from 'src/core/service/api.service';

const { Application } = Shopware;

class KlarnaPaymentConfigurationService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'klarna_payment') {
        super(httpClient, loginService, apiEndpoint);
    }

    validateCredentials(credentials) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/validate-credentials`,
                credentials,
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

Application.addServiceProvider('KlarnaPaymentConfigurationService', (container) => {
    const initContainer = Application.getContainer('init');

    return new KlarnaPaymentConfigurationService(initContainer.httpClient, container.loginService);
});

