<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper;

use function getenv;
use function hash_hmac;
use function json_encode;
use KlarnaPayment\Components\Client\Request\RequestInterface;
use LogicException;

class RequestHasher implements RequestHasherInterface
{
    public function getHash(RequestInterface $request): string
    {
        $json = json_encode($request, JSON_PRESERVE_ZERO_FRACTION);

        if (empty($json)) {
            throw new LogicException('could not generate hash');
        }

        $secret = getenv('APP_SECRET');

        if (empty($secret)) {
            throw new LogicException('empty app secret');
        }

        return hash_hmac('sha256', $json, $secret);
    }
}
