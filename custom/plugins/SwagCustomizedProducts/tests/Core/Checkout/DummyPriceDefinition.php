<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout;

use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;

class DummyPriceDefinition implements PriceDefinitionInterface
{
    public function getPrecision(): int
    {
        return 2;
    }

    public function getType(): string
    {
        return 'test-price';
    }

    public function getPriority(): int
    {
        return 0;
    }

    public static function getConstraints(): array
    {
        return [];
    }
}
