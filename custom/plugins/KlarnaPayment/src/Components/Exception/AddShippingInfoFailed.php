<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Exception;

use Exception;

class AddShippingInfoFailed extends Exception
{
    /** @var string */
    private $errorCode;

    /** @var array */
    private $response;

    public function __construct(string $errorCode, array $response)
    {
        $this->errorCode = $errorCode;
        $this->response  = $response;

        parent::__construct('An error occured while adding shipping information to capture');
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}
