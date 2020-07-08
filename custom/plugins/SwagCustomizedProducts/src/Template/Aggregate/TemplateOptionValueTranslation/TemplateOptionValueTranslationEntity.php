<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValueTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueEntity;

class TemplateOptionValueTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $swagCustomizedProductsTemplateOptionValueId;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var TemplateOptionValueEntity
     */
    protected $swagCustomizedProductsTemplateOptionValue;

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getSwagCustomizedProductsTemplateOptionValueId(): string
    {
        return $this->swagCustomizedProductsTemplateOptionValueId;
    }

    public function setSwagCustomizedProductsTemplateOptionValueId(string $swagCustomizedProductsTemplateOptionValueId): void
    {
        $this->swagCustomizedProductsTemplateOptionValueId = $swagCustomizedProductsTemplateOptionValueId;
    }

    public function getSwagCustomizedProductsTemplateOptionValue(): TemplateOptionValueEntity
    {
        return $this->swagCustomizedProductsTemplateOptionValue;
    }

    public function setSwagCustomizedProductsTemplateOptionValue(TemplateOptionValueEntity $swagCustomizedProductsTemplateOptionValue): void
    {
        $this->swagCustomizedProductsTemplateOptionValue = $swagCustomizedProductsTemplateOptionValue;
    }
}
