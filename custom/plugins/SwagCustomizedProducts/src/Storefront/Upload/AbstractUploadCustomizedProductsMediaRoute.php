<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Upload;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractUploadCustomizedProductsMediaRoute
{
    abstract public function getDecorated(): AbstractUploadCustomizedProductsMediaRoute;

    abstract public function upload(Request $request, SalesChannelContext $salesChannelContext): UploadCustomizedProductsMediaRouteResponse;
}
