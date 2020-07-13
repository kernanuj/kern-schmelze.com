<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Constants;
use InvMixerProduct\Repository\TagRepository;
use InvMixerProduct\Value\ListingProductCollection;
use InvMixerProduct\Value\ListingProductGroup;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\Tag\TagEntity;

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
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * DefaultProductListingProvider constructor.
     * @param ProductListingLoader $productListingLoader
     * @param TagRepository $tagRepository
     */
    public function __construct(ProductListingLoader $productListingLoader, TagRepository $tagRepository)
    {
        $this->productListingLoader = $productListingLoader;
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return ListingProductCollection
     */
    public function getDefaultListingProductCollection
    (
        SalesChannelContext $salesChannelContext
    ): ListingProductCollection {

        $requiredTag = $this->tagRepository->findOneByName(
            Constants::CATALOG_PRODUCT_TAG_PRODUCT_ITEM,
            $salesChannelContext->getContext()
        );

        $collection = new ListingProductCollection();
        foreach (Constants::VALID_CATALOG_PRODUCT_TAGS() as $_tagIdentifier) {

            $tags = $this->tagRepository->findAllByName(
                $_tagIdentifier,
                $salesChannelContext->getContext()
            );

            $tagFilters = [];
            foreach ($tags as $tag) {
                \assert($tag instanceof TagEntity);
                $tagFilters[] = new Search\Filter\ContainsFilter('tagIds', $tag->getId());
            }

            $productListing = $this->productListingLoader->load(
                (new Search\Criteria())
                    ->addFilter(
                        new Search\Filter\MultiFilter(
                            Search\Filter\MultiFilter::CONNECTION_OR,
                            $tagFilters
                        )
                    )
                    ->addFilter(
                        new Search\Filter\ContainsFilter(
                            'tagIds',
                            $requiredTag->getId()
                        )
                    ),
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
