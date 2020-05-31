<?php declare(strict_types=1);

namespace InvMixerProduct\Entity;

use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Content\Product\ProductEntity;

class MixItemEntity {

    /**
     * @var ProductEntity
     */
    private $product;

    /**
     * @var int
     */
    private $quantity;


}
