<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Swag\CustomizedProducts\Template\TemplateEntity;

class TemplateTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $swagCustomizedProductsTemplateId;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var TemplateEntity|null
     */
    protected $swagCustomizedProductsTemplate;

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

    public function getSwagCustomizedProductsTemplateId(): string
    {
        return $this->swagCustomizedProductsTemplateId;
    }

    public function setSwagCustomizedProductsTemplateId(string $swagCustomizedProductsTemplateId): void
    {
        $this->swagCustomizedProductsTemplateId = $swagCustomizedProductsTemplateId;
    }

    public function getSwagCustomizedProductsTemplate(): ?TemplateEntity
    {
        return $this->swagCustomizedProductsTemplate;
    }

    public function setSwagCustomizedProductsTemplate(?TemplateEntity $swagCustomizedProductsTemplate): void
    {
        $this->swagCustomizedProductsTemplate = $swagCustomizedProductsTemplate;
    }
}
