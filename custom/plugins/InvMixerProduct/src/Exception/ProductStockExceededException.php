<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

use Exception;
use Shopware\Core\Content\Product\ProductEntity;

/**
 * Class ProductStockExceededException
 * @package InvMixerProduct\Exception
 */
class ProductStockExceededException extends Exception
{

    /**
     * @param ProductEntity $product
     * @param int $currentStock
     * @param int $requestedStock
     * @return static
     */
    public static function fromProductAndRequestedStock(
        ProductEntity $product,
    int $currentStock,
    int $requestedStock
    ): self {
        return new self(
            sprintf(
                'The stock for product %s is %d and to low for a request of %d',
                $product->getId(),
                $currentStock,
                $requestedStock
            )
        );
    }
}
