<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPriceDetailRoute
{
    abstract public function getDecorated(): AbstractPriceDetailRoute;

    abstract public function priceDetail(Request $request, SalesChannelContext $context): PriceDetailResponse;
}
