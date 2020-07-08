<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Framework\Media\Validator;

use Shopware\Storefront\Framework\Media\StorefrontMediaValidatorInterface;

interface CustomizedProductsMediaUploadValidatorInterface extends StorefrontMediaValidatorInterface
{
    /**
     * @return string[]
     */
    public function getMimeTypes(): array;
}
