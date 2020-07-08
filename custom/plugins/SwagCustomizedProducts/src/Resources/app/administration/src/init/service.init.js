import SwagCustomizedProductsTemplateService from '../core/service/template.service';
import SwagCustomizedProductsTemplateApiService from '../core/service/api/template.api.service';
import SwagCustomizedProductsTemplateOptionApiService from '../core/service/api/template-option.api.service';

const initContainer = Shopware.Application.getContainer('init');

Shopware.Application.addServiceProvider('SwagCustomizedProductsTemplateService', (container) => {
    return new SwagCustomizedProductsTemplateService(initContainer.httpClient, container.loginService);
});

Shopware.Application.addServiceProvider('SwagCustomizedProductsTemplateApiService', (container) => {
    return new SwagCustomizedProductsTemplateApiService(initContainer.httpClient, container.loginService);
});

Shopware.Application.addServiceProvider('SwagCustomizedProductsTemplateOptionService', (container) => {
    return new SwagCustomizedProductsTemplateOptionApiService(initContainer.httpClient, container.loginService);
});

Shopware.Application.addServiceProvider('SwagCustomizedProductsUiLanguageContextHelper', (container) => {
    const factoryContainer = Shopware.Application.getContainer('factory');
    const localeFactory = factoryContainer.locale;
    const localeToLanguageService = container.localeToLanguageService;

    return () => {
        const currentLocale = localeFactory.getLastKnownLocale();
        return localeToLanguageService.localeToLanguage(currentLocale);
    };
});
