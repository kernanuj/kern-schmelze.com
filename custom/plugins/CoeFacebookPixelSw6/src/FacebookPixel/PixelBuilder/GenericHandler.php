<?php
namespace CoeFacebookPixelSw6\FacebookPixel\PixelBuilder;

use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderServiceInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Storefront\Event\StorefrontRenderEvent;

/**
 * Class GenericHandler
 * @package CoeFacebookPixelSw6\FacebookPixel\Handler
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class GenericHandler implements PixelBuilderInterface
{
    /** @var PixelBuilderServiceInterface */
    private $pixelBuilder;

    /** @var FacebookPixelConfig */
    private $config;

    /**
     * GenericHandler constructor.
     * @param PixelBuilderServiceInterface $pixelBuilder
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function __construct(PixelBuilderServiceInterface $pixelBuilder)
    {
        $this->pixelBuilder = $pixelBuilder;
    }

    /**
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function addPixel(StorefrontRenderEvent $event, DefinitionInstanceRegistry $registry, FacebookPixelConfig $config){

        $this->config = $config;

        /** @var CustomerEntity $customer */
        $customer = $event->getSalesChannelContext()->getCustomer();

        if(!$customer){
            return;
        }

        // Track new registration
        $isNewRegistration = $this->config->isNewRegistration();
        if($isNewRegistration){
            $this->pixelBuilder->setCompleteRegistrationTracking($customer->getCustomerNumber());
        }

        // Track payment method change
        $newPaymentMethodId = $this->config->getNewPaymentMethodId();
        if(!is_null($newPaymentMethodId) && is_string($newPaymentMethodId)){
            $this->pixelBuilder->setAddPaymentInfoTracking($newPaymentMethodId);
        }

        // Track newsletter lead
        $isNewNewsletterSubscription = $this->config->isNewNewsletterSubscription();
        if($isNewNewsletterSubscription){
            $this->pixelBuilder->setLeadTracking();
        }

        // Track latest added product
        $productId = $this->config->getLastAddedProductId();
        if($productId){
            /** @var EntityRepository $repo */
            $repo = $registry->getRepository("product");

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter("id", $productId));
            $criteria->setLimit(1);

            /** @var EntitySearchResult $product */
            $searchResult = $repo->search($criteria, $event->getContext());

            if(!$searchResult->count()){
                return;
            }

            $this->config->setProductNumber($searchResult->first()->getProductNumber());

            $this->pixelBuilder->setAddToCartTracking($searchResult->first()->getProductNumber());
        }
    }

    /**
     * @return PixelBuilderServiceInterface
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function getPixelBuilder() : PixelBuilderServiceInterface{
        return $this->pixelBuilder;
    }

    /**
     * @return FacebookPixelConfig
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function getConfig() : FacebookPixelConfig{
        return $this->config;
    }
}