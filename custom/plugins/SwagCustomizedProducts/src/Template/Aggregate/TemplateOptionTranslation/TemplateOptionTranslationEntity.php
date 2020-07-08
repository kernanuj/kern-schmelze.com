<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;

class TemplateOptionTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $swagCustomizedProductsTemplateOptionId;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var TemplateOptionEntity
     */
    protected $swagCustomizedProductsTemplateOption;

    public function getSwagCustomizedProductsTemplateOptionId(): string
    {
        return $this->swagCustomizedProductsTemplateOptionId;
    }

    public function setSwagCustomizedProductsTemplateOptionId(string $swagCustomizedProductsTemplateOptionId): void
    {
        $this->swagCustomizedProductsTemplateOptionId = $swagCustomizedProductsTemplateOptionId;
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

    public function getSwagCustomizedProductsTemplateOption(): TemplateOptionEntity
    {
        return $this->swagCustomizedProductsTemplateOption;
    }

    public function setSwagCustomizedProductsTemplateOption(TemplateOptionEntity $swagCustomizedProductsTemplateOption): void
    {
        $this->swagCustomizedProductsTemplateOption = $swagCustomizedProductsTemplateOption;
    }
}
