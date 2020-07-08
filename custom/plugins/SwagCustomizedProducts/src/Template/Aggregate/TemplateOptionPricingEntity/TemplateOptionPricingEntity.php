<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPricingEntity;

use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAware\TemplateOptionPriceAwareInterface;

abstract class TemplateOptionPricingEntity extends Entity implements TemplateOptionPriceAwareInterface
{
    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected $ruleId;

    /**
     * @var RuleEntity|null
     */
    protected $rule;

    /**
     * @var float|null
     */
    protected $percentageSurcharge;

    /**
     * @var PriceCollection|null
     */
    protected $price;

    public function getRuleId(): ?string
    {
        return $this->ruleId;
    }

    public function setRuleId(?string $ruleId): void
    {
        $this->ruleId = $ruleId;
    }

    public function getRule(): ?RuleEntity
    {
        return $this->rule;
    }

    public function setRule(?RuleEntity $rule): void
    {
        $this->rule = $rule;
    }

    public function getPercentageSurcharge(): ?float
    {
        return $this->percentageSurcharge;
    }

    public function setPercentageSurcharge(?float $percentageSurcharge): void
    {
        $this->percentageSurcharge = $percentageSurcharge;
    }

    public function getPrice(): ?PriceCollection
    {
        return $this->price;
    }

    public function setPrice(?PriceCollection $price): void
    {
        $this->price = $price;
    }
}
