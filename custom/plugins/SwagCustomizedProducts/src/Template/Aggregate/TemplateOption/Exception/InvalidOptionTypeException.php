<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class InvalidOptionTypeException extends ShopwareHttpException
{
    public function __construct(string $invalidType)
    {
        parent::__construct(
            'Invalid template option type "{{ invalidType }}" is not supported',
            ['invalidType' => $invalidType]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'SWAG_CUSTOMIZED_PRODUCTS__INVALID_OPTION_TYPE';
    }
}
