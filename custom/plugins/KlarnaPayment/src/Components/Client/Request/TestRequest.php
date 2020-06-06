<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Request;

class TestRequest extends GetOrderRequest
{
    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
