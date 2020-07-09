<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Struct;

use Shopware\Core\Framework\Struct\Struct;

class ShippingOption extends Struct
{
    /** @var string */
    protected $id = '';

    /** @var string */
    protected $name = '';

    /** @var string */
    protected $description = '';

    /** @var float */
    protected $price = 0.0;

    /** @var float */
    protected $taxRate = 0.0;

    /** @var float */
    protected $taxAmount = 0.0;

    /** @var int */
    protected $precision = 0;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'description' => $this->getDescription(),
            'price'       => (int) round($this->getPrice() * (10 ** $this->getPrecision()), 0),
            'tax_amount'  => (int) round($this->getTaxAmount() * (10 ** $this->getPrecision()), 0),
            'tax_rate'    => (int) $this->getTaxRate(),
        ];
    }
}
