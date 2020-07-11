<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Constants;
use InvMixerProduct\Value\ListingProductCollection;
use InvMixerProduct\Value\ListingProductGroup;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class DefaultProductListingProvider
 * @package InvMixerProduct\Service
 */
class DefaultProductListingProvider implements ProductListingProviderInterface
{

    /**
     * @var ProductListingLoader
     */
    private $productListingLoader;

    /**
     * DefaultProductListingProvider constructor.
     * @param ProductListingLoader $productListingLoader
     */
    public function __construct(ProductListingLoader $productListingLoader)
    {
        $this->productListingLoader = $productListingLoader;
    }


    /**
     * @param SalesChannelContext $salesChannelContext
     * @return ListingProductCollection
     */
    public function getDefaultListingProductCollection
    (
        SalesChannelContext $salesChannelContext
    ): ListingProductCollection {

        $collection = new ListingProductCollection();
        foreach (Constants::VALID_CATALOG_PRODUCT_TAGS() as $_tagIdentifier) {
            $productListing = $this->productListingLoader->load(
                (new Search\Criteria()),
                $salesChannelContext
            );
            $collection->addGroup(
                ListingProductGroup::fromEntitySearchResult(
                    $_tagIdentifier,
                    $productListing
                )
            );
        }

        return $collection;
    }

}
