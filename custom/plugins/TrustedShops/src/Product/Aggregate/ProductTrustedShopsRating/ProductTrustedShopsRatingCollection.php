<?php declare(strict_types=1);

namespace TrustedShops\Product\Aggregate\ProductTrustedShopsRating;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ProductTrustedShopsRatingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductTrustedShopsRatingEntity::class;
    }
}
