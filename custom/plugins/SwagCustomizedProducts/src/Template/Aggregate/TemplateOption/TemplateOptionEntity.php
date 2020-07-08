<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption;

use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAbleEntity\TemplateOptionPriceAbleEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionTranslation\TemplateOptionTranslationCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueCollection;
use Swag\CustomizedProducts\Template\TemplateEntity;

class TemplateOptionEntity extends TemplateOptionPriceAbleEntity
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $templateId;

    /**
     * @var array|null
     */
    protected $typeProperties;

    /**
     * @var string|null
     */
    protected $itemNumber;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var TemplateOptionTranslationCollection|null
     */
    protected $translations;

    /**
     * @var TemplateEntity|null
     */
    protected $template;

    /**
     * @var TemplateOptionValueCollection|null
     */
    protected $values;

    /**
     * @var TemplateExclusionConditionCollection|null
     */
    protected $templateExclusionConditions;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    public function setTemplateId(string $templateId): void
    {
        $this->templateId = $templateId;
    }

    public function getTypeProperties(): ?array
    {
        return $this->typeProperties;
    }

    public function setTypeProperties(?array $typeProperties): void
    {
        $this->typeProperties = $typeProperties;
    }

    public function getItemNumber(): ?string
    {
        return $this->itemNumber;
    }

    public function setItemNumber(string $itemNumber): void
    {
        $this->itemNumber = $itemNumber;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTranslations(): ?TemplateOptionTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(TemplateOptionTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getTemplate(): ?TemplateEntity
    {
        return $this->template;
    }

    public function setTemplate(?TemplateEntity $template): void
    {
        $this->template = $template;
    }

    public function getValues(): ?TemplateOptionValueCollection
    {
        return $this->values;
    }

    public function setValues(TemplateOptionValueCollection $values): void
    {
        $this->values = $values;
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
