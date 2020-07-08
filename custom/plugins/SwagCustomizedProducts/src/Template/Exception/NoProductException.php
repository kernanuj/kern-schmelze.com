<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class NoProductException extends ShopwareHttpException
{
    public function __construct(string $templateId)
    {
        parent::__construct('The template with the ID {{ templateId }} has no product', ['templateId' => $templateId]);
    }

    public function getErrorCode(): string
    {
        return 'SWAG_CUSTOMIZED_PRODUCTS__TEMPLATE_WITHOUT_PRODUCT';
    }
}
