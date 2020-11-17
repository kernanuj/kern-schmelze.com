<?php
namespace CoeFacebookPixelSw6\FacebookPixel;

/**
 * Class PixelBuilderService
 * @package CoeFacebookPixelSw6\FacebookPixel
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class PixelBuilderService implements PixelBuilderServiceInterface
{
    /** @var array  */
    private $pixelArray;

    /**
     * PixelBuilderService constructor.
     * @param array $pixelArray
     */
    public function __construct(array $pixelArray = [])
    {
        $this->pixelArray = $pixelArray;
    }

    /**
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setLeadTracking()
    {
        $this->addTracking('Lead');
    }

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
    ){
        $config = "
            content_ids: ['" . implode("','", $productIDs) . "'],
            content_type: '".$contentType."',
            content_name: '" . strtr($contentName, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/')) . "'";

        if($value) {$config .= ", value: " . $value;}
        if($currency) {$config .= ", currency: '" . $currency . "'";}

        $this->addTracking('ViewContent', $config);
    }

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
    ){
        $config = "
            content_ids: ['" . implode("','", $productIDs) . "'],
            content_type: '" . $contentType . "',
            num_items: " . $numItems . ",
            value: " . $value . ",
            currency: '" . $currency . "'";

        $this->addTracking('Purchase', $config);
    }

    /**
     * @param string $productID
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setAddToCartTracking(string $productID)
    {
        $config = "
            content_ids: ['" . $productID . "'],
            content_type: 'product'";

        $this->addTracking('AddToCart', $config);
    }

    /**
     * @param string $paymentID
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setAddPaymentInfoTracking(string $paymentID)
    {
        $config = "content_ids: ['" . $paymentID . "']";

        $this->addTracking('AddPaymentInfo', $config);
    }

    /**
     * @param string $customerNumber
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setCompleteRegistrationTracking(string $customerNumber)
    {
        $config = "
            content_name: '" . $customerNumber . "',
            status: 1";

        $this->addTracking('CompleteRegistration', $config);
    }

    /**
     * @param array $productIDs
     * @param string $contentType
     * @param int $numItems
     * @param string $value
     * @param string $currency
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function setInitiateCheckoutTracking(array $productIDs, string $contentType, int $numItems, string $value, string $currency)
    {
        if ($numItems <= 0) {
            return;
        }

        $config = "
        content_ids: ['".implode("','", $productIDs)."'],
        content_type: '".$contentType."',
        num_items: ".$numItems.",
        value: ".$value.",
        currency: '".$currency."'
        ";

        $this->addTracking('InitiateCheckout', $config);
    }

    /**
     * @return string
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function getPixel() : string
    {
        $trackingPixel = "";
        foreach($this->pixelArray as $tracking) {
            $trackingPixel .= $tracking."\n";
        }

        return $trackingPixel;
    }

    /**
     * Used to add additional tracking information by an FacebookPixeltBuiltEvent.
     * @param string $property
     * @param string $param
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function addAdditionalTrackingInfo(string $property, string $param){
        $this->addTracking($property, $param);
    }

    /**
     * @param string $name
     * @param string $parameters
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    private function addTracking(string $name, string $parameters = "")
    {
        $tracking = "fbq('track', '".$name."'";
        if($parameters) {
            $tracking .= " , { ".$parameters." }";
        }
        $tracking .= ");";

        $this->pixelArray[] = $tracking;
    }
}