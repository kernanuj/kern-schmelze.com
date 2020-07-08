<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAware;

use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;

interface TemplateOptionPriceAwareInterface
{
    public function getPrice(): ?PriceCollection;

    public function setPrice(PriceCollection $price): void;
}
