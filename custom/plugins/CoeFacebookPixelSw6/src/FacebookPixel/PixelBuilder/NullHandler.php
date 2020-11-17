<?php
namespace CoeFacebookPixelSw6\FacebookPixel\PixelBuilder;

use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Storefront\Event\StorefrontRenderEvent;

/**
 * Class NullHandler
 * @package CoeFacebookPixelSw6\FacebookPixel\Handler
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class NullHandler implements PixelBuilderInterface
{
    /** @var PixelBuilderServiceInterface */
    private $pixelBuilder;

    /** @var FacebookPixelConfig */
    private $config;

    /**
     * NullHandler constructor.
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