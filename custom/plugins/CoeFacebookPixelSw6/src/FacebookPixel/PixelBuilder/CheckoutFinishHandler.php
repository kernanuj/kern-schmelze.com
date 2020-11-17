<?php
namespace CoeFacebookPixelSw6\FacebookPixel\PixelBuilder;

use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderServiceInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Storefront\Event\StorefrontRenderEvent;

/**
 * Class CheckoutFinishHandler
 * @package CoeFacebookPixelSw6\FacebookPixel\Handler
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class CheckoutFinishHandler implements PixelBuilderInterface
{
    /** @var PixelBuilderServiceInterface */
    private $pixelBuilder;

    /** @var FacebookPixelConfig */
    private $config;

    /**
     * CheckoutFinishHandler constructor.
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

        /** @var SalesChannelContext $context */
        $salesChannelContext = $event->getSalesChannelContext();

        /** @var array $params */
        $params = $event->getParameters();

        /** @var OrderEntity $order */
        $order = $params["page"]->getOrder();

        $this->config->setOrderNumber($order->getOrderNumber());
        $this->config->setCurrency($salesChannelContext->getCurrency()->getIsoCode());

        /** @var OrderLineItemCollection $lineItems */
        $lineItems = $order->getLineItems();

        if(!$lineItems || !$lineItems->count()){
            return;
        }

        /** @var string $contentType */
        $contentType = $lineItems->count() > 1 ? 'product_group' : 'product';

        // We only want to add "product" items (no discounts or something else)
        $lineItems = $lineItems->filter(function ($lineItem){
            return $lineItem->getType() == "product";
        });

        if(!$lineItems->count()){
            return;
        }

        $productIds = [];

        /** @var OrderLineItemEntity $lineItem */
        foreach($lineItems as $lineItem){
            $payload = $lineItem->getPayload();
            $productIds[] = $payload["productNumber"];
        }

        $this->pixelBuilder->setPurchaseTracking(
            $productIds,
            $contentType,
            $lineItems->count(),
            $order->getAmountTotal(),
            $this->config->getCurrency()
        );

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