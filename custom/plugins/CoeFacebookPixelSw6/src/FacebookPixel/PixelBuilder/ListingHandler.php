<?php
namespace CoeFacebookPixelSw6\FacebookPixel\PixelBuilder;

use CoeFacebookPixelSw6\CoeUtil\CoeSwUtil;
use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderServiceInterface;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Storefront\Event\StorefrontRenderEvent;

/**
 * Class ListingHandler
 * @package CoeFacebookPixelSw6\FacebookPixel\Handler
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class ListingHandler implements PixelBuilderInterface
{
    /** @var PixelBuilderServiceInterface */
    private $pixelBuilder;

    /** @var FacebookPixelConfig */
    private $config;

    /**
     * ListingHandler constructor.
     * @param PixelBuilderServiceInterface $pixelBuilder
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function __construct(PixelBuilderServiceInterface $pixelBuilder)
    {
        $this->pixelBuilder = $pixelBuilder;
    }

    /**
     * @param StorefrontRenderEvent $event
     * @param DefinitionInstanceRegistry $registry
     * @return mixed|void
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function addPixel(StorefrontRenderEvent $event, DefinitionInstanceRegistry $registry, FacebookPixelConfig $config){
        $this->config = $config;

        /** @var array $params */
        $params = $event->getParameters();

        /** @var CategoryEntity $category */
        $category = $params["page"]->getHeader()->getNavigation()->getActive();
        $category = CoeSwUtil::reloadEntityWithAssociations($category, ["nestedProducts"], $registry, $event->getContext());

        $name = $category->getName();
        $productIds = [];

        if(!$category->getNestedProducts()->count()){
            return;
        }

        /** @var ProductEntity $product */
        foreach($category->getNestedProducts() as $product){
            $productIds[] = $product->getProductNumber();
        }

        $this->pixelBuilder->setViewContentTracking($productIds, 'product_group', $name);
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