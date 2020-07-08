<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class OptionTypeClassNotFoundException extends ShopwareHttpException
{
    public function __construct(string $type)
    {
        parent::__construct(
            'Class for OptionType "{{ type }}" not found',
            ['type' => $type]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'SWAG_CUSTOMIZED_PRODUCTS__OPTION_TYPE_MISSING';
    }
}
