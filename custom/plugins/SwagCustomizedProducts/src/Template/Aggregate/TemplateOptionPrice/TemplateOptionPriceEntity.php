<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPrice;

use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPricingEntity\TemplateOptionPricingEntity;

class TemplateOptionPriceEntity extends TemplateOptionPricingEntity
{
    /**
     * @var string
     */
    protected $templateOptionId;

    /**
     * @var TemplateOptionEntity
     */
    protected $templateOption;

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
}
