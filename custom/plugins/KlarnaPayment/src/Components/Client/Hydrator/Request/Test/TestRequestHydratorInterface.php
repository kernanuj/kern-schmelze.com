<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\Test;

use KlarnaPayment\Components\Client\Request\TestRequest;

interface TestRequestHydratorInterface
{
    public function hydrate(string $username, string $password, ?string $salesChannel): TestRequest;
}
