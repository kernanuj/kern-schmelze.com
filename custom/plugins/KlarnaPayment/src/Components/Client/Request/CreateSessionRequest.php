<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Request;

use KlarnaPayment\Components\Client\Struct\Address;
use KlarnaPayment\Components\Client\Struct\Attachment;
use KlarnaPayment\Components\Client\Struct\Customer;
use KlarnaPayment\Components\Client\Struct\LineItem;
use KlarnaPayment\Components\Client\Struct\Options;
use Shopware\Core\Framework\Struct\Struct;

class CreateSessionRequest extends Struct implements RequestInterface
{
    /** @var string */
    protected $method = 'POST';

    /** @var string */
    protected $endpoint = '/payments/v1/sessions';

    /** @var ?string */
    protected $salesChannel;

    /** @var ?string */
    protected $acquiringChannel;

    /** @var null|Attachment */
    protected $attachment;

    /** @var string */
    protected $purchaseCountry;

    /** @var string */
    protected $purchaseCurrency;

    /** @var string */
    protected $locale;

    /** @var Options */
    protected $options;

    /** @var int */
    protected $precision = 0;

    /** @var float */
    protected $orderAmount = 0.0;

    /** @var float */
    protected $orderTaxAmount = 0.0;

    /** @var LineItem[] */
    protected $orderLines = [];

    /** @var null|Address */
    protected $billingAddress;

    /** @var null|Address */
    protected $shippingAddress;

    /** @var null|Customer */
    protected $customer;

    /** @var null|string */
    protected $merchantData;

    public function getAcquiringChannel(): ?string
    {
        return $this->acquiringChannel;
    }

    public function getAttachment(): ?Attachment
    {
        return $this->attachment;
    }

    public function getPurchaseCountry(): string
    {
        return $this->purchaseCountry;
    }

    public function getPurchaseCurrency(): string
    {
        return $this->purchaseCurrency;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getOrderAmount(): float
    {
        return $this->orderAmount;
    }

    public function getOrderTaxAmount(): float
    {
        return $this->orderTaxAmount;
    }

    public function getOrderLines(): array
    {
        return $this->orderLines;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function getMerchantData(): ?string
    {
        if (null === $this->merchantData) {
            return null;
        }

        return mb_substr($this->merchantData, 0, 1024);
    }

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

    public function jsonSerialize(): array
    {
        return [
            'acquiring_channel' => $this->getAcquiringChannel(),
            'attachment'        => $this->getAttachment(),
            'purchase_country'  => $this->getPurchaseCountry(),
            'purchase_currency' => $this->getPurchaseCurrency(),
            'locale'            => $this->getLocale(),
            'options'           => $this->getOptions(),
            'order_amount'      => (int) round($this->getOrderAmount() * (10 ** $this->getPrecision()), 0),
            'order_tax_amount'  => (int) round($this->getOrderTaxAmount() * (10 ** $this->getPrecision()), 0),
            'billing_address'   => $this->getBillingAddress(),
            'shipping_address'  => $this->getShippingAddress(),
            'order_lines'       => $this->getOrderLines(),
            'customer'          => $this->getCustomer(),
            'merchant_data'     => $this->getMerchantData(),
        ];
    }
}
