<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class PriceDetailResponse extends StoreApiResponse
{
    /**
     * @var ArrayStruct
     */
    protected $object;

    public function __construct(
        float $productPrice,
        float $totalPrice,
        float $surchargesSubTotal,
        float $oneTimeSurchargesSubTotal,
        array $surcharges,
        array $oneTimeSurcharges
    ) {
        parent::__construct(
            new ArrayStruct(
                [
                    'productPrice' => $productPrice,
                    'totalPrice' => $totalPrice,
                    'surchargesSubTotal' => $surchargesSubTotal,
                    'oneTimeSurchargesSubTotal' => $oneTimeSurchargesSubTotal,
                    'surcharges' => $surcharges,
                    'oneTimeSurcharges' => $oneTimeSurcharges,
                ]
            )
        );
    }

    public function getProductPrice(): float
    {
        return $this->object->get('productPrice');
    }

    public function getTotalPrice(): float
    {
        return $this->object->get('totalPrice');
    }

    public function getSurchargesSubTotal(): float
    {
        return $this->object->get('surchargesSubTotal');
    }

    public function getOneTimeSurchargesSubTotal(): float
    {
        return $this->object->get('oneTimeSurchargesSubTotal');
    }

    public function getSurcharges(): array
    {
        return $this->object->get('surcharges');
    }

    public function getOneTimeSurcharges(): array
    {
        return $this->object->get('oneTimeSurcharges');
    }
}
