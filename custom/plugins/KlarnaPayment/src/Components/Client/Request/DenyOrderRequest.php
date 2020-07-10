<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Request;

use Shopware\Core\Framework\Struct\Struct;

class DenyOrderRequest extends Struct implements RequestInterface
{
    /** @var string */
    protected $method = 'DELETE';

    /** @var string */
    protected $endpoint = '/instantshopping/v1/authorizations/{authorization_token}';

    /** @var ?string */
    protected $salesChannel;

    /** @var string */
    protected $authorizationToken;

    /** @var string */
    protected $denyCode = 'other';

    /** @var string */
    protected $denyMessage = 'Something went wrong';

    /** @var bool */
    protected $isError = false;

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getEndpoint(): string
    {
        return str_replace('{authorization_token}', $this->getAuthorizationToken(), $this->endpoint);
    }

    public function getSalesChannel(): ?string
    {
        return $this->salesChannel;
    }

    public function getAuthorizationToken(): string
    {
        return $this->authorizationToken;
    }

    public function getDenyCode(): string
    {
        return $this->denyCode;
    }

    public function getDenyMessage(): string
    {
        return $this->denyMessage;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    public function jsonSerialize(): array
    {
        return [
            'deny_code'    => $this->getDenyCode(),
            'deny_message' => $this->getDenyMessage(),
        ];
    }
}
