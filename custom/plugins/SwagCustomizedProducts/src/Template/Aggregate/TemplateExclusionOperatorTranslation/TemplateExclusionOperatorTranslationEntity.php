<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperatorTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorEntity;

class TemplateExclusionOperatorTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $swagCustomizedProductsTemplateExclusionOperatorId;

    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var TemplateExclusionOperatorEntity
     */
    protected $swagCustomizedProductsTemplateExclusionOperator;

    public function getSwagCustomizedProductsTemplateExclusionOperatorId(): string
    {
        return $this->swagCustomizedProductsTemplateExclusionOperatorId;
    }

    public function setSwagCustomizedProductsTemplateExclusionOperatorId(string $swagCustomizedProductsTemplateExclusionOperatorId): void
    {
        $this->swagCustomizedProductsTemplateExclusionOperatorId = $swagCustomizedProductsTemplateExclusionOperatorId;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getSwagCustomizedProductsTemplateExclusionOperator(): TemplateExclusionOperatorEntity
    {
        return $this->swagCustomizedProductsTemplateExclusionOperator;
    }

    public function setSwagCustomizedProductsTemplateExclusionOperator(TemplateExclusionOperatorEntity $swagCustomizedProductsTemplateExclusionOperator): void
    {
        $this->swagCustomizedProductsTemplateExclusionOperator = $swagCustomizedProductsTemplateExclusionOperator;
    }
}
