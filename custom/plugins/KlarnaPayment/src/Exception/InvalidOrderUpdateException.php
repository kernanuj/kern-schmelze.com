<?php

declare(strict_types=1);

namespace KlarnaPayment\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class InvalidOrderUpdateException extends ShopwareHttpException
{
    public function __construct(string $id)
    {
        parent::__construct('Order can not be updated correctly: {{ input }}', ['input' => $id]);
    }

    public function getErrorCode(): string
    {
        return 'KLARNAPAYMENT__INVALID_ORDER_UPDATE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
