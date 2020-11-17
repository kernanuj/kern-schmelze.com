<?php

namespace CoeFacebookPixelSw6\FacebookPixel;


/**
 * Class PixelBuilderService
 * @package CoeFacebookPixelSw6\FacebookPixel
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
interface PixelBuilderServiceInterface
{
    /**
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setLeadTracking();

    /**
     * @param array $productIDs
     * @param string $contentType
     * @param string $contentName
     * @param string|null $value
     * @param string|null $currency
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setViewContentTracking(
        array $productIDs,
        string $contentType,
        string $contentName,
        string $value = null,
        string $currency = null
    );

    /**
     * @param array $productIDs
     * @param string $contentType
     * @param int $numItems
     * @param string $value
     * @param string $currency
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setPurchaseTracking(
        array $productIDs,
        string $contentType,
        int $numItems,
        string $value,
        string $currency
    );

    /**
     * @param string $productID
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setAddToCartTracking(string $productID);

    /**
     * @param string $paymentID
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setAddPaymentInfoTracking(string $paymentID);

    /**
     * @param string $customerNumber
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setCompleteRegistrationTracking(string $customerNumber);

    /**
     * @param array $productIDs
     * @param string $contentType
     * @param int $numItems
     * @param string $value
     * @param string $currency
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setInitiateCheckoutTracking(
        array $productIDs,
        string $contentType,
        int $numItems,
        string $value,
        string $currency
    );

    /**
     * Used to add additional tracking information by an FacebookPixeltBuiltEvent.
     * This method should add any property with the given params to the final pixel.
     * @param string $property
     * @param string $param
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function addAdditionalTrackingInfo(string $property, string $param);

    /**
     * @return string
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function getPixel(): string;
}