<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateTranslation\TemplateTranslationCollection;

class TemplateEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $internalName;

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
    protected $mediaId;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var bool
     */
    protected $stepByStep;

    /**
     * @var bool
     */
    protected $confirmInput;

    /**
     * @var array|null
     */
    protected $decisionTree;

    /**
     * @var TemplateTranslationCollection|null
     */
    protected $translations;

    /**
     * @var MediaEntity|null
     */
    protected $media;

    /**
     * @var TemplateOptionCollection|null
     */
    protected $options;

    /**
     * @var ProductCollection|null
     */
    protected $products;

    /**
     * @var TemplateExclusionCollection|null
     */
    protected $exclusions;

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function setInternalName(string $internalName): void
    {
        $this->internalName = $internalName;
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

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isStepByStep(): bool
    {
        return $this->stepByStep;
    }

    public function setStepByStep(bool $stepByStep): void
    {
        $this->stepByStep = $stepByStep;
    }

    public function isConfirmInput(): bool
    {
        return $this->confirmInput;
    }

    public function setConfirmInput(bool $confirmInput): void
    {
        $this->confirmInput = $confirmInput;
    }

    public function getTranslations(): ?TemplateTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(TemplateTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getOptions(): ?TemplateOptionCollection
    {
        return $this->options;
    }

    public function setOptions(TemplateOptionCollection $options): void
    {
        $this->options = $options;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }

    public function getExclusions(): ?TemplateExclusionCollection
    {
        return $this->exclusions;
    }

    public function setExclusions(TemplateExclusionCollection $exclusions): void
    {
        $this->exclusions = $exclusions;
    }

    public function getDecisionTree(): ?array
    {
        return $this->decisionTree;
    }

    public function setDecisionTree(array $decisionTree): void
    {
        $this->decisionTree = $decisionTree;
    }
}
