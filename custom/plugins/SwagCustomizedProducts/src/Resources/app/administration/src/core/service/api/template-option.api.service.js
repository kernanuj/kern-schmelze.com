const ApiService = Shopware.Classes.ApiService;

/**
 * Gateway for the API endpoint "template option"
 * @class
 * @extends ApiService
 */
class SwagCustomizedProductsTemplateOptionApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'swag-customized-products-template-option') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'SwagCustomizedProductsTemplateOptionApiService';
    }

    /**
     * @returns {Promise<T>}
     */
    getSupportedTypes() {
        const headers = this.getBasicHeaders();

        return this.httpClient.get(
            `/_action/${this.getApiBasePath()}/types`,
            { headers }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default SwagCustomizedProductsTemplateOptionApiService;
