<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValuePrice;

use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPricingEntity\TemplateOptionPricingEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueEntity;

class TemplateOptionValuePriceEntity extends TemplateOptionPricingEntity
{
    /**
     * @var string
     */
    protected $templateOptionValueId;

    /**
     * @var TemplateOptionValueEntity
     */
    protected $templateOptionValue;

    public function getTemplateOptionValueId(): string
    {
        return $this->templateOptionValueId;
    }

    public function setTemplateOptionValueId(string $templateOptionValueId): void
    {
        $this->templateOptionValueId = $templateOptionValueId;
    }

    public function getTemplateOptionValue(): TemplateOptionValueEntity
    {
        return $this->templateOptionValue;
    }

    public function setTemplateOptionValue(TemplateOptionValueEntity $templateOptionValue): void
    {
        $this->templateOptionValue = $templateOptionValue;
    }
}
