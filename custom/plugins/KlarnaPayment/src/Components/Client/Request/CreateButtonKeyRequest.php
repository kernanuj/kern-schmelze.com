<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Request;

use KlarnaPayment\Components\Client\Struct\Options;
use Shopware\Core\Framework\Struct\Struct;

class CreateButtonKeyRequest extends Struct implements RequestInterface
{
    /** @var string */
    protected $method = 'POST';

    /** @var string */
    protected $endpoint = '/instantshopping/v1/buttons';

    /** @var ?string */
    protected $salesChannel;

    /** @var string */
    protected $name = '';

    /** @var array */
    protected $merchantUrls = [];

    /** @var Options */
    protected $options;

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getSalesChannel(): ?string
    {
        return $this->salesChannel;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMerchantUrls(): array
    {
        return $this->merchantUrls;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function jsonSerialize(): array
    {
        return [
            'name'          => $this->getName(),
            'disabled'      => false,
            'merchant_urls' => $this->getMerchantUrls(),
            'options'       => $this->getOptions(),
        ];
    }
}
