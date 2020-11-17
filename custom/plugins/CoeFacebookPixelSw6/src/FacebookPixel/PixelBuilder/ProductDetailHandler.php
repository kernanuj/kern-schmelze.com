<?php
namespace CoeFacebookPixelSw6\FacebookPixel\PixelBuilder;

use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderServiceInterface;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Storefront\Event\StorefrontRenderEvent;

/**
 * Class ProductDetailHandler
 * @package CoeFacebookPixelSw6\FacebookPixel\Handler
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class ProductDetailHandler implements PixelBuilderInterface
{
    /** @var PixelBuilderServiceInterface */
    private $pixelBuilder;

    /** @var FacebookPixelConfig */
    private $config;

    /**
     * ProductDetailHandler constructor.
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

        /** @var SalesChannelProductEntity $product */
        $product = $params["page"]->getProduct();

        /** @var string $productName */
        $productName = $product->getName();

        if(is_null($productName) && !is_null($product->getParentId())){
            $productName = $this->getParentProductName($product->getParentId(), $event->getContext(), $registry);
        }

        $tree = $product->getCategoryTree();
        $this->config->setCurrency($salesChannelContext->getCurrency()->getIsoCode());
        $this->config->setProductNumber($product->getProductNumber());
        $this->config->setCategoryId(array_pop($tree));


        $this->pixelBuilder->setViewContentTracking(
            [$this->config->getProductNumber()],
            'product',
            $productName,
            $product->getCalculatedPrice()->getUnitPrice(),
            $this->config->getCurrency()
        );
    }

    /**
     * When processing variant products, get the name of the parent.
     * @param String $parentId
     * @param Context $context
     * @param DefinitionInstanceRegistry $registry
     * @return string
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    private function getParentProductName(String $parentId, Context $context, DefinitionInstanceRegistry $registry) : string{
        /** @var EntityRepository $repo */
        $repo = $registry->getRepository("product");

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter("id", $parentId));
        $criteria->setLimit(1);

        return ($repo->search($criteria, $context))->first()->getName();
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