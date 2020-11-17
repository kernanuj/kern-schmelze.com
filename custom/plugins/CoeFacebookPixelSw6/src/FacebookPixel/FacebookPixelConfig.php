<?php


namespace CoeFacebookPixelSw6\FacebookPixel;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class FacebookPixelConfig
 * Reflects the Plug-In Config and runtime configuration.
 * Gets passed to the template engine for rendering
 * @package CoeFacebookPixelSw6\FacebookPixel
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class FacebookPixelConfig
{
    /** @var string  */
    public const TWIG_VARIABLE = "coeFacebookPixel";

    /**
     * @source PluginConfig
     * @var string
     */
    private $trackingId;

    /**
     * @source PluginConfig
     * @var bool
     */
    private $debugMode;

    /**
     * @source assignment
     * @var string
     */
    private $currency;

    /**
     * The acutal tracking pixel to output in the frontend
     * @source assignment
     * @var string
     */
    private $pixel;

    /**
     * @source assignment
     * @var string
     */
    private $productNumber;

    /**
     * @source assignment
     * @var string
     */
    private $categoryId;

    /**
     * @source assignment
     * @var string
     */
    private $orderNumber;

    /**
     * @source assignment
     * @var string
     */
    private $lastAddedProductId;

    /**
     * @source assignment
     * @var string
     */
    private $newPaymentMethodId;

    /**
     * @source assignment
     * @var bool
     */
    private $isNewRegistration = false;

    /**
     * @source assignment
     * @var bool
     */
    private $isNewNewsletterSubscription = false;

    /**
     * @source assignment
     * @var bool
     */
    private $cookieAccepted = false;

    /**
     * FacebookPixelConfig constructor.
     * @param SystemConfigService $configService
     */
    public function __construct(SystemConfigService $configService)
    {
        $this->trackingId = $configService->get("CoeFacebookPixelSw6.config.coeFacebookPixelId");
        $this->debugMode = $configService->get("CoeFacebookPixelSw6.config.coeFacebookPixelDebug");
    }

    /**
     * @return null|string
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function getTrackingId(): ?string
    {
        return $this->trackingId;
    }

    /**
     * Value comes form plugin config. No setter available
     * @param string $trackingID
     */
    private function setTrackingId(string $trackingID){}

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * Value comes form plugin config. No setter available
     * @param bool $debugMode
     */
    private function setDebugMode(bool $debugMode): void {}

    /**
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getPixel(): ?string
    {
        return $this->pixel;
    }

    /**
     * @param string $pixel
     */
    public function setPixel(string $pixel): void
    {
        $this->pixel = $pixel;
    }

    /**
     * @return string
     */
    public function getProductNumber(): ?string
    {
        return $this->productNumber;
    }

    /**
     * @param string $productNumber
     */
    public function setProductNumber(string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }

    /**
     * @return string
     */
    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    /**
     * @param string $categoryId
     */
    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getLastAddedProductId(): ?string
    {
        return $this->lastAddedProductId;
    }

    /**
     * @param string $lastAddedProductId
     */
    public function setLastAddedProductId(string $lastAddedProductId): void
    {
        $this->lastAddedProductId = $lastAddedProductId;
    }

    /**
     * @return bool
     */
    public function isNewRegistration(): bool
    {
        return $this->isNewRegistration;
    }

    /**
     * @param bool $isNewRegistration
     */
    public function setIsNewRegistration(bool $isNewRegistration): void
    {
        $this->isNewRegistration = $isNewRegistration;
    }

    /**
     * @return string
     */
    public function getNewPaymentMethodId(): ?string
    {
        return $this->newPaymentMethodId;
    }

    /**
     * @param string $newPaymentMethodId
     */
    public function setNewPaymentMethodId(string $newPaymentMethodId): void
    {
        $this->newPaymentMethodId = $newPaymentMethodId;
    }

    /**
     * @return bool
     */
    public function isNewNewsletterSubscription(): bool
    {
        return $this->isNewNewsletterSubscription;
    }

    /**
     * @param bool $isNewNewsletterSubscription
     */
    public function setIsNewNewsletterSubscription(bool $isNewNewsletterSubscription): void
    {
        $this->isNewNewsletterSubscription = $isNewNewsletterSubscription;
    }

    /**
     * @return bool
     */
    public function isCookieAccepted(): bool
    {
        return $this->cookieAccepted;
    }

    /**
     * @param bool $cookieAccepted
     */
    public function setCookieAccepted(bool $cookieAccepted): void
    {
        $this->cookieAccepted = $cookieAccepted;
    }

}