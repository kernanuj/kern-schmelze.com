<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Request;

use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Framework\Struct\Struct;

class UpdateOrderRequest extends Struct implements RequestInterface
{
    /** @var string */
    protected $method = 'PATCH';

    /** @var string */
    protected $endpoint = '/ordermanagement/v1/orders/{order_id}/authorization';

    /** @var null|string */
    protected $salesChannel;

    /** @var string */
    protected $orderId = '';

    /** @var LineItem[] */
    protected $lineItems = [];

    /** @var int */
    protected $precision = 0;

    /** @var float */
    protected $orderAmount = 0.0;

    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getOrderAmount(): float
    {
        return $this->orderAmount;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getEndpoint(): string
    {
        return str_replace('{order_id}', $this->orderId, $this->endpoint);
    }

    public function getSalesChannel(): ?string
    {
        return $this->salesChannel;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function jsonSerialize(): array
    {
        return [
            'order_amount' => (int) round($this->getOrderAmount() * (10 ** $this->getPrecision()), 0),
            'order_lines'  => $this->getLineItems(),
        ];
    }
}
