<?php
namespace CoeFacebookPixelSw6\FacebookPixel\PixelBuilder;

use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderServiceInterface;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Storefront\Event\StorefrontRenderEvent;

/**
 * Interface PixelBuilderInterface
 * @package CoeFacebookPixelSw6\FacebookPixel\Handler
 */
interface PixelBuilderInterface
{
    /**
     * @param StorefrontRenderEvent $event
     * @param DefinitionInstanceRegistry $registry
     * @return mixed
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function addPixel(StorefrontRenderEvent $event, DefinitionInstanceRegistry $registry, FacebookPixelConfig $config);

    /**
     * @return PixelBuilderInterface
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function getPixelBuilder() : PixelBuilderServiceInterface;

    /**
     * @return FacebookPixelConfig
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function getConfig() : FacebookPixelConfig;

}