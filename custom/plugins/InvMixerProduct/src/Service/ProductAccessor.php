<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Value\Price;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Content\Product\ProductEntity as Subject;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ProductAccessor
 * @package InvMixerProduct\Service
 */
class ProductAccessor implements ProductAccessorInterface
{
    /**
     * @todo implement checks to verify that the product may be added to a mix.
     *
     * @inheritDoc
     */
    public function isEligibleProduct(
        Subject $subject,
        SalesChannelContext $context
    ): bool {

        return true;
    }

    /**
     * @inheritDoc
     */

    public function accessProductAvailableStock(
        Subject $subject,
        SalesChannelContext $context
    ): int {
        return $subject->getAvailableStock();
    }

    /**
     * @inheritDoc
     */
    public function accessProductWeight(
        Subject $subject,
        SalesChannelContext $context
    ): Weight {

        return Weight::xGrams(
            (int)($subject->getWeight() * $subject->getReferenceUnit())
        );
    }

    /**
     * @inheritDoc
     */
    public function accessProductPrice(
        Subject $subject,
        SalesChannelContext $context
    ): Price {
        die(__METHOD__);
    }


}
