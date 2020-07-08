<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Framework\Uuid\Uuid;
use function sprintf;

abstract class SwagCustomizedProductsCartError extends Error
{
    protected const KEY = 'customizedProducts.addToCart.error.default';

    /**
     * @var string
     */
    private $id;

    public function __construct(?string $lineItemId = null)
    {
        parent::__construct();

        $this->id = $lineItemId ?? Uuid::randomHex();
    }

    public function getParameters(): array
    {
        return ['id' => $this->id];
    }

    public function blockOrder(): bool
    {
        return false;
    }

    public function getId(): string
    {
        return sprintf('%s-%s', static::KEY, $this->id);
    }

    public function getLevel(): int
    {
        return self::LEVEL_ERROR;
    }

    public function getMessageKey(): string
    {
        return static::KEY;
    }
}
