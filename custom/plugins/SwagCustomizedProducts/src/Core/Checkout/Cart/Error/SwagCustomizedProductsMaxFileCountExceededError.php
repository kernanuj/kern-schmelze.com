<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\Error;

class SwagCustomizedProductsMaxFileCountExceededError extends SwagCustomizedProductsCartError
{
    protected const KEY = 'customizedProducts.addToCart.error.maxFileCountExceeded';
}
