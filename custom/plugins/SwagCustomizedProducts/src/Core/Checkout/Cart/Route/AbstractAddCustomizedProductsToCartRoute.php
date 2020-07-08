<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\Route;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAddCustomizedProductsToCartRoute
{
    abstract public function getDecorated(): AbstractAddCustomizedProductsToCartRoute;

    abstract public function add(RequestDataBag $requestDataBag, Request $request, SalesChannelContext $salesChannelContext, ?Cart $cart): NoContentResponse;
}
