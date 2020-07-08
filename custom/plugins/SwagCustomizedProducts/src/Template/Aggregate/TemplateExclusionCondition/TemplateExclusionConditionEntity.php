<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueCollection;

class TemplateExclusionConditionEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $templateExclusionId;

    /**
     * @var TemplateExclusionEntity
     */
    protected $templateExclusion;

    /**
     * @var string
     */
    protected $templateOptionId;

    /**
     * @var TemplateOptionEntity
     */
    protected $templateOption;

    /**
     * @var string
     */
    protected $templateExclusionOperatorId;

    /**
     * @var TemplateExclusionOperatorEntity
     */
    protected $templateExclusionOperator;

    /**
     * @var TemplateOptionValueCollection|null
     */
    protected $templateOptionValues;

    public function getTemplateExclusionId(): string
    {
        return $this->templateExclusionId;
    }

    public function setTemplateExclusionId(string $templateExclusionId): void
    {
        $this->templateExclusionId = $templateExclusionId;
    }

    public function getTemplateExclusion(): TemplateExclusionEntity
    {
        return $this->templateExclusion;
    }

    public function setTemplateExclusion(TemplateExclusionEntity $templateExclusion): void
    {
        $this->templateExclusion = $templateExclusion;
    }

    public function getTemplateOptionId(): string
    {
        return $this->templateOptionId;
    }

    public function setTemplateOptionId(string $templateOptionId): void
    {
        $this->templateOptionId = $templateOptionId;
    }

    public function getTemplateOption(): TemplateOptionEntity
    {
        return $this->templateOption;
    }

    public function setTemplateOption(TemplateOptionEntity $templateOption): void
    {
        $this->templateOption = $templateOption;
    }

    public function getTemplateExclusionOperatorId(): string
    {
        return $this->templateExclusionOperatorId;
    }

    public function setTemplateExclusionOperatorId(string $templateExclusionOperatorId): void
    {
        $this->templateExclusionOperatorId = $templateExclusionOperatorId;
    }

    public function getTemplateExclusionOperator(): TemplateExclusionOperatorEntity
    {
        return $this->templateExclusionOperator;
    }

    public function setTemplateExclusionOperator(TemplateExclusionOperatorEntity $templateExclusionOperator): void
    {
        $this->templateExclusionOperator = $templateExclusionOperator;
    }

    public function getTemplateOptionValues(): ?TemplateOptionValueCollection
    {
        return $this->templateOptionValues;
    }

    public function setTemplateOptionValues(TemplateOptionValueCollection $templateOptionValues): void
    {
        $this->templateOptionValues = $templateOptionValues;
    }
}
