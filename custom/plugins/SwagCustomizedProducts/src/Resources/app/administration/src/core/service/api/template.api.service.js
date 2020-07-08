const ApiService = Shopware.Classes.ApiService;

/**
 * Gateway for the API endpoint "template"
 * @class
 * @extends ApiService
 */
class SwagCustomizedProductsTemplateApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'swag-customized-products-template') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'SwagCustomizedProductsTemplateApiService';
    }

    /**
     * @returns {Promise<T>}
     */
    dispatchTreeGenerationMessage(templateId) {
        const headers = this.getBasicHeaders();

        return this.httpClient.post(
            `/_action/${this.getApiBasePath()}/${templateId}/tree`,
            {},
            {
                headers: headers
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default SwagCustomizedProductsTemplateApiService;
