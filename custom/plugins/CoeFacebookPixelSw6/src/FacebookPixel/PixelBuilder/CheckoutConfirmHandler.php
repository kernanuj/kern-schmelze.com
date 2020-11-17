<?php
namespace CoeFacebookPixelSw6\FacebookPixel\PixelBuilder;

use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Storefront\Event\StorefrontRenderEvent;

/**
 * Class CheckoutConfirmHandler
 * @package CoeFacebookPixelSw6\FacebookPixel\Handler
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class CheckoutConfirmHandler implements PixelBuilderInterface
{
    /** @var PixelBuilderServiceInterface */
    private $pixelBuilder;

    /** @var FacebookPixelConfig */
    private $config;

    /**
     * CheckoutConfirmHandler constructor.
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

        /** @var Cart $cart */
        $cart = $params["page"]->getCart();

        /** @var LineItemCollection $lineItems */
        $lineItems = $cart->getLineItems();

        $this->config->setCurrency($salesChannelContext->getCurrency()->getIsoCode());

        if(!$lineItems){
            return;
        }

        $lineItems = $lineItems->filter(function ($lineItem){
            return $lineItem->getType() == "product";
        });

        $productIds = [];

        /** @var LineItem $lineItem */
        foreach($lineItems as $lineItem){
            $payload = $lineItem->getPayload();
            $productIds[] = $payload["productNumber"];
        }

        /** @var string $contentType */
        $contentType = $lineItems->count() > 1 ? 'product_group' : 'product';

        $this->pixelBuilder->setInitiateCheckoutTracking(
            $productIds,
            $contentType,
            $lineItems->count(),
            $cart->getPrice()->getTotalPrice(),
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