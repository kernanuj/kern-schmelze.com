<?php

namespace CoeFacebookPixelSw6\FacebookPixel;

use CoeFacebookPixelSw6\CoeUtil\ControllerDataStruct;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\AccountHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\CheckoutConfirmHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\CheckoutFinishHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\CheckoutHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\CheckoutShippingPaymentHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\EmotionHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\ListingHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\NewsletterHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\NullHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\PixelBuilderInterface;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\ProductDetailHandler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class FacebookPixelHandlerFactory
 * @package CoeFacebookPixelSw6\FacebookPixel
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class PixelBuilderFactory
{

    /**
     * @param ControllerDataStruct $controllerData
     * @param SalesChannelContext $context
     * @param FacebookPixelConfig $config
     * @return PixelBuilderInterface
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public static function create(
        ControllerDataStruct $controllerData
    ) : PixelBuilderInterface {

        /** @var PixelBuilderService $pixelBuilderService */
        $pixelBuilderService = new PixelBuilderService();

        switch($controllerData->controllerName){
            case 'NavigationController': return new ListingHandler($pixelBuilderService);     //listing ctrl in sw5
            case 'ProductController': return new ProductDetailHandler($pixelBuilderService);  //detail ctrl in sw5

            case 'CheckoutController':      //checkout ctrl in sw5
                switch ($controllerData->action) {
                    case 'finishPage':  return new CheckoutFinishHandler($pixelBuilderService);
                    case 'confirmPage':  return new CheckoutConfirmHandler($pixelBuilderService);
                }
        }

        return new NullHandler($pixelBuilderService);
    }
}