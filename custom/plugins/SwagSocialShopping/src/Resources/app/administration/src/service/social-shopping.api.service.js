import ApiService
    from 'src/core/service/api.service';

class SocialShoppingApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'social-shopping') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'socialShoppingService';
    }

    getNetworks() {
        const apiRoute = `/_action/${this.getApiBasePath()}/networks`;

        return this.httpClient.get(
            apiRoute,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    validate(socialShoppingSalesChannelId) {
        const apiRoute = `/_action/${this.getApiBasePath()}/validate`;

        return this.httpClient.post(
            apiRoute,
            {
                social_shopping_sales_channel_id: socialShoppingSalesChannelId
            },
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default SocialShoppingApiService;
