<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Exception;

use Exception;

class ButtonKeyCreationFailed extends Exception
{
    /** @var string */
    private $errorCode;

    public function __construct(string $message, string $errorCode)
    {
        $this->errorCode = $errorCode;

        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
