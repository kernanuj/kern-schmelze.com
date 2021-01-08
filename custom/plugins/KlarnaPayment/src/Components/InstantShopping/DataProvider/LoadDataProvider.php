<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use KlarnaPayment\Components\ButtonKeyHandler\ButtonKeyHandlerInterface;
use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Components\Exception\ButtonKeyCreationFailed;
use KlarnaPayment\Components\Extension\Hydrator\InstantShopping\DataExtensionHydratorInterface;
use KlarnaPayment\Components\Extension\TemplateData\InstantShoppingDataExtension;
use KlarnaPayment\Components\Helper\LocaleHelper\LocaleHelperInterface;
use KlarnaPayment\Components\Helper\SalesChannelHelper\SalesChannelHelperInterface;
use KlarnaPayment\Components\Struct\Configuration;
use KlarnaPayment\Installer\Modules\RuleInstaller;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPage;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPage;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\Product\ProductPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LoadDataProvider implements LoadDataProviderInterface
{
    /** @var ConfigReaderInterface */
    private $configReader;

    /** @var ButtonKeyHandlerInterface */
    private $buttonKeyHandler;

    /** @var RequestStack */
    private $requestStack;

    /** @var DataExtensionHydratorInterface */
    private $dataExtensionHydrator;

    /** @var LoggerInterface */
    private $logger;

    /** @var LocaleHelperInterface */
    private $localeHelper;

    /** @var SystemConfigService */
    private $systemConfigService;

    /** @var SalesChannelHelperInterface */
    private $salesChannelHelper;

    public function __construct(
        ConfigReaderInterface $configReader,
        ButtonKeyHandlerInterface $buttonKeyHandler,
        RequestStack $requestStack,
        DataExtensionHydratorInterface $dataExtensionHydrator,
        LoggerInterface $logger,
        LocaleHelperInterface $localeHelper,
        SystemConfigService $systemConfigService,
        SalesChannelHelperInterface $salesChannelHelper
    ) {
        $this->configReader          = $configReader;
        $this->buttonKeyHandler      = $buttonKeyHandler;
        $this->requestStack          = $requestStack;
        $this->dataExtensionHydrator = $dataExtensionHydrator;
        $this->logger                = $logger;
        $this->localeHelper          = $localeHelper;
        $this->systemConfigService   = $systemConfigService;
        $this->salesChannelHelper    = $salesChannelHelper;
    }

    public function registerInstantShopping(Page $page, SalesChannelContext $salesChannelContext): void
    {
        $pluginConfig = $this->configReader->read($salesChannelContext->getSalesChannel()->getId());

        /** @var Request $currentRequest */
        $currentRequest = $this->requestStack->getCurrentRequest();

        if (!$this->isInstantShoppingAllowed(
            $pluginConfig,
            $page,
            $salesChannelContext->getCustomer(),
            $currentRequest
        )) {
            return;
        }

        $salesChannelDomain = $currentRequest->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID);

        $klarnaLocale = $this->localeHelper->getKlarnaLocale(
            (string) $currentRequest->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_LOCALE, 'en-GB'),
            $salesChannelContext->getShippingLocation()->getCountry()->getIso() ?: ''
        );

        try {
            $buttonKeyEntity = $this->buttonKeyHandler->getOrCreateButtonKey(
                $salesChannelDomain,
                $salesChannelContext->getContext()
            );

            if (!$buttonKeyEntity) {
                return;
            }
        } catch (ButtonKeyCreationFailed $e) {
            // no-op, error is logged
            return;
        }

        $detailPageProductId = '';

        if ($page instanceof ProductPage) {
            $detailPageProductId = $page->getProduct()->getId();
        }

        $templateData = new InstantShoppingDataExtension(
            [
                'instanceId'          => Uuid::randomHex(),
                'environment'         => $pluginConfig->get('testMode', false) ? InstantShoppingDataExtension::ENVIRONMENT_PLAYGROUND : InstantShoppingDataExtension::ENVIRONMENT_PRODUCTION,
                'buttonKey'           => $buttonKeyEntity->getButtonKey(),
                'currencyIso'         => $salesChannelContext->getCurrency()->getIsoCode(),
                'countryIso'          => $salesChannelContext->getShippingLocation()->getCountry()->getIso(),
                'klarnaLocale'        => $klarnaLocale,
                'merchantUrls'        => $this->dataExtensionHydrator->hydrateMerchantUrls($salesChannelDomain, $salesChannelContext),
                'orderLines'          => $this->dataExtensionHydrator->hydrateOrderLines($page, $salesChannelDomain, $salesChannelContext),
                'variation'           => (string) $pluginConfig->get('instantShoppingVariation'),
                'type'                => (string) $pluginConfig->get('instantShoppingType'),
                'detailPageProductId' => $detailPageProductId,
                'actionUrls'          => $this->dataExtensionHydrator->hydrateActionUrls($salesChannelDomain, $salesChannelContext),
                'billingCountries'    => $this->getCountries($salesChannelContext->getSalesChannel()->getId(), $salesChannelContext->getContext()),
            ]
        );

        $page->addExtension(InstantShoppingDataExtension::EXTENSION_NAME, $templateData);
    }

    private function isInstantShoppingAllowed(
        Configuration $pluginConfig,
        Page $page,
        ?CustomerEntity $customer,
        ?Request $request
    ): bool {
        if (!$request || $this->isB2bCustomer($customer) || !(bool) $pluginConfig->get('instantShoppingEnabled')) {
            return false;
        }

        if ($page instanceof OffcanvasCartPage || $page instanceof CheckoutCartPage) {
            if ($page->getCart()->getLineItems()->count() < 1) {
                return false;
            }
        }

        return true;
    }

    private function isB2bCustomer(?CustomerEntity $customer): bool
    {
        if ($customer && !empty($customer->getCompany())) {
            return true;
        }

        if ($customer) {
            $shippingAddress = $customer->getActiveShippingAddress() ?: $customer->getDefaultShippingAddress();
            $billingAddress  = $customer->getActiveBillingAddress() ?: $customer->getDefaultBillingAddress();

            if (!$shippingAddress || !$billingAddress) {
                $this->logger->error(
                    'No shipping or billing addresses was found for customer',
                    $customer->jsonSerialize()
                );

                return true;
            }

            if (!empty($shippingAddress->getCompany()) || !empty($billingAddress->getCompany())) {
                return true;
            }
        }

        return false;
    }

    private function getCountries(string $salesChannelId, Context $context): array
    {
        $salesChannel = $this->salesChannelHelper->getSalesChannel($salesChannelId, $context);
        $countries    = [];

        $salesChannelCountries = $salesChannel->getCountries() !== null ? $salesChannel->getCountries()->getElements() : [];

        foreach ($salesChannelCountries as $country) {
            if (!in_array($country->getIso(), array_keys(RuleInstaller::AVAIBILITY_CONDITIONS), true)) {
                continue;
            }

            $countries[] = $country->getIso();
        }

        return array_filter($countries);
    }
}
