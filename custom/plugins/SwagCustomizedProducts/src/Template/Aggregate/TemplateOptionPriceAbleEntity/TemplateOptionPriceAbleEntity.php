<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAbleEntity;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\Tax\TaxEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAware\TemplateOptionPriceAwareInterface;

abstract class TemplateOptionPriceAbleEntity extends Entity implements TemplateOptionPriceAwareInterface
{
    use EntityIdTrait;

    /**
     * @var bool
     */
    protected $oneTimeSurcharge;

    /**
     * @var bool
     */
    protected $relativeSurcharge;

    /**
     * @var bool
     */
    protected $advancedSurcharge;

    /**
     * @var string|null
     */
    protected $taxId;

    /**
     * @var TaxEntity|null
     */
    protected $tax;

    /**
     * @var PriceCollection|null
     */
    protected $price;

    /**
     * @var CalculatedPrice|null
     */
    protected $calculatedPrice;

    /**
     * @var float|null
     */
    protected $percentageSurcharge;

    /**
     * @var EntityCollection|null
     */
    protected $prices;

    public function isOneTimeSurcharge(): bool
    {
        return $this->oneTimeSurcharge;
    }

    public function setOneTimeSurcharge(bool $oneTimeSurcharge): void
    {
        $this->oneTimeSurcharge = $oneTimeSurcharge;
    }

    public function isRelativeSurcharge(): bool
    {
        return $this->relativeSurcharge;
    }

    public function setRelativeSurcharge(bool $relativeSurcharge): void
    {
        $this->relativeSurcharge = $relativeSurcharge;
    }

    public function isAdvancedSurcharge(): bool
    {
        return $this->advancedSurcharge;
    }

    public function setAdvancedSurcharge(bool $advancedSurcharge): void
    {
        $this->advancedSurcharge = $advancedSurcharge;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(string $taxId): void
    {
        $this->taxId = $taxId;
    }

    public function getTax(): ?TaxEntity
    {
        return $this->tax;
    }

    public function setTax(TaxEntity $tax): void
    {
        $this->tax = $tax;
    }

    public function getPrice(): ?PriceCollection
    {
        return $this->price;
    }

    public function setPrice(PriceCollection $price): void
    {
        $this->price = $price;
    }

    public function getCalculatedPrice(): ?CalculatedPrice
    {
        return $this->calculatedPrice;
    }

    public function setCalculatedPrice(CalculatedPrice $calculatedPrice): void
    {
        $this->calculatedPrice = $calculatedPrice;
    }

    public function getPercentageSurcharge(): ?float
    {
        return $this->percentageSurcharge;
    }

    public function setPercentageSurcharge(float $percentageSurcharge): void
    {
        $this->percentageSurcharge = $percentageSurcharge;
    }

    public function getPrices(): ?EntityCollection
    {
        return $this->prices;
    }

    public function setPrices(EntityCollection $prices): void
    {
        $this->prices = $prices;
    }
}
