<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Extension\TemplateData;

use Shopware\Core\Framework\Struct\Struct;

class InstantShoppingDataExtension extends Struct
{
    public const LIBRARY_URL = 'https://x.klarnacdn.net/instantshopping/lib/v1/lib.js';

    public const EXTENSION_NAME = 'klarna_instant_shopping_data';

    public const ENVIRONMENT_PLAYGROUND = 'playground';
    public const ENVIRONMENT_PRODUCTION = 'production';

    /** @var string */
    protected $instanceId;

    /** @var string */
    protected $buttonKey;

    /** @var string */
    protected $environment = self::ENVIRONMENT_PLAYGROUND;

    /** @var string */
    protected $currencyIso;

    /** @var string */
    protected $countryIso;

    /** @var string */
    protected $klarnaLocale;

    /** @var array */
    protected $merchantUrls = [];

    /** @var array */
    protected $orderLines = [];

    /** @var string */
    protected $variation;

    /** @var string */
    protected $type;

    /** @var string */
    protected $detailPageProductId = '';

    /** @var array */
    protected $actionUrls = [];

    /** @var array */
    protected $billingCountries = [];

    public function __construct(?array $data)
    {
        if (empty($data)) {
            return;
        }

        $this->assign($data);
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function getButtonKey(): string
    {
        return $this->buttonKey;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getCurrencyIso(): string
    {
        return $this->currencyIso;
    }

    public function getCountryIso(): string
    {
        return $this->countryIso;
    }

    public function getKlarnaLocale(): string
    {
        return $this->klarnaLocale;
    }

    public function getMerchantUrls(): array
    {
        return $this->merchantUrls;
    }

    public function getOrderLines(): array
    {
        return $this->orderLines;
    }

    public function getTheme(): array
    {
        return [
            'variation' => $this->getVariation(),
            'type'      => $this->getType(),
        ];
    }

    public function getVariation(): string
    {
        return $this->variation;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDetailPageProductId(): string
    {
        return $this->detailPageProductId;
    }

    public function getActionUrls(): array
    {
        return $this->actionUrls;
    }

    public function getBillingCountries(): array
    {
        return $this->billingCountries;
    }

    public function jsonSerialize(): array
    {
        return [
            'instanceId'          => $this->getInstanceId(),
            'buttonKey'           => $this->getButtonKey(),
            'environment'         => $this->getEnvironment(),
            'countryIso'          => $this->getCountryIso(),
            'currencyIso'         => $this->getCurrencyIso(),
            'klarnaLocale'        => $this->getKlarnaLocale(),
            'libraryUrl'          => self::LIBRARY_URL,
            'merchantUrls'        => $this->getMerchantUrls(),
            'orderLines'          => $this->getOrderLines(),
            'theme'               => $this->getTheme(),
            'detailPageProductId' => $this->getDetailPageProductId(),
            'actionUrls'          => $this->getActionUrls(),
            'billingCountries'    => $this->getBillingCountries(),
        ];
    }
}
