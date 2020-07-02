<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class NoProductStreamAssignedException extends ShopwareHttpException
{
    public function __construct(string $entityClass, string $id)
    {
        parent::__construct(
            '{{ entityClass }} "{{ id }}" has no product stream assigned.',
            ['entityClass' => $entityClass, 'id' => $id]
        );
    }

    public function getErrorCode(): string
    {
        return 'SWAG_SOCIAL_SHOPPING__NO_PRODUCT_STREAM_ASSIGNED';
    }
}
