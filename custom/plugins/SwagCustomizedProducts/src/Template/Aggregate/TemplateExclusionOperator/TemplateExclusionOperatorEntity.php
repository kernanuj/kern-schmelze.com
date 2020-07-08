<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperatorTranslation\TemplateExclusionOperatorTranslationCollection;

class TemplateExclusionOperatorEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $templateOptionType;

    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var TemplateExclusionOperatorCollection|null
     */
    protected $templateExclusionConditions;

    /**
     * @var TemplateExclusionOperatorTranslationCollection|null
     */
    protected $translations;

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    public function getTemplateOptionType(): string
    {
        return $this->templateOptionType;
    }

    public function setTemplateOptionType(string $templateOptionType): void
    {
        $this->templateOptionType = $templateOptionType;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getTemplateExclusionConditions(): ?TemplateExclusionOperatorCollection
    {
        return $this->templateExclusionConditions;
    }

    public function setTemplateExclusionConditions(TemplateExclusionOperatorCollection $templateExclusionConditions): void
    {
        $this->templateExclusionConditions = $templateExclusionConditions;
    }

    public function getTranslations(): ?TemplateExclusionOperatorTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(TemplateExclusionOperatorTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}
