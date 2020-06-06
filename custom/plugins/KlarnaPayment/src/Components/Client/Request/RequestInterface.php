<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Request;

interface RequestInterface
{
    public function getSalesChannel(): ?string;

    public function getMethod(): string;

    public function getEndpoint(): string;

    public function jsonSerialize(): array;
}
