<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

use Exception;
use Shopware\Core\Content\Product\ProductEntity;

/**
 * Class NotEligibleProductException
 * @package InvMixerProduct\Exception
 */
class NotEligibleProductException extends Exception
{

    /**
     * @param ProductEntity $product
     * @return static
     */
    public static function fromProductEntity(
        ProductEntity $product
    ): self {
        return new self(
            sprintf(
                'The product with id %s is not a mixable product',
                $product->getId()
            )
        );
    }
}
