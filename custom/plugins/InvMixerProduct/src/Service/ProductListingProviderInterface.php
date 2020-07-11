<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Value\ListingProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Interface ProductListingProviderInterface
 * @package InvMixerProduct\Service
 */
interface ProductListingProviderInterface
{

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return ListingProductCollection
     */
    public function getDefaultListingProductCollection
    (
        SalesChannelContext $salesChannelContext
    ): ListingProductCollection;

}
