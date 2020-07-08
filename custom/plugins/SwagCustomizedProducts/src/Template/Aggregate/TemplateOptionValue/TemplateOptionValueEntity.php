<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue;

use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAbleEntity\TemplateOptionPriceAbleEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValueTranslation\TemplateOptionValueTranslationCollection;

class TemplateOptionValueEntity extends TemplateOptionPriceAbleEntity
{
    /**
     * @var string
     */
    protected $templateOptionId;

    /**
     * @var array|null
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var string|null
     */
    protected $itemNumber;

    /**
     * @var bool
     */
    protected $default;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var TemplateOptionEntity
     */
    protected $templateOption;

    /**
     * @var TemplateOptionValueTranslationCollection
     */
    protected $translations;

    /**
     * @var TemplateExclusionConditionCollection|null
     */
    protected $templateExclusionConditions;

    public function getTemplateOptionId(): string
    {
        return $this->templateOptionId;
    }

    public function setTemplateOptionId(string $templateOptionId): void
    {
        $this->templateOptionId = $templateOptionId;
    }

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(?array $value): void
    {
        $this->value = $value;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getItemNumber(): ?string
    {
        return $this->itemNumber;
    }

    public function setItemNumber(?string $itemNumber): void
    {
        $this->itemNumber = $itemNumber;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTemplateOption(): TemplateOptionEntity
    {
        return $this->templateOption;
    }

    public function setTemplateOption(TemplateOptionEntity $templateOption): void
    {
        $this->templateOption = $templateOption;
    }

    public function getTranslations(): TemplateOptionValueTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(TemplateOptionValueTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getTemplateExclusionConditions(): ?TemplateExclusionConditionCollection
    {
        return $this->templateExclusionConditions;
    }

    public function setTemplateExclusionConditions(TemplateExclusionConditionCollection $templateExclusionConditions): void
    {
        $this->templateExclusionConditions = $templateExclusionConditions;
    }
}
