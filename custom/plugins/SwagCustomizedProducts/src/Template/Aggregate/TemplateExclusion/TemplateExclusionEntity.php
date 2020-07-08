<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionCollection;
use Swag\CustomizedProducts\Template\TemplateEntity;

class TemplateExclusionEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var TemplateExclusionConditionCollection|null
     */
    protected $conditions;

    /**
     * @var string
     */
    protected $templateId;

    /**
     * @var TemplateEntity
     */
    protected $template;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConditions(): ?TemplateExclusionConditionCollection
    {
        return $this->conditions;
    }

    public function setConditions(TemplateExclusionConditionCollection $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    public function setTemplateId(string $templateId): void
    {
        $this->templateId = $templateId;
    }

    public function getTemplate(): TemplateEntity
    {
        return $this->template;
    }

    public function setTemplate(TemplateEntity $template): void
    {
        $this->template = $template;
    }
}
