<?php declare(strict_types=1);

namespace InvMixerProduct\Helper;


use InvMixerProduct\Constants;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;

/**
 * Class LineItemAccessor
 * @package InvMixerProduct\Helper
 */
final class LineItemAccessor
{

    /**
     * @param LineItem $lineItem
     * @return string|null
     */
    public static function getMixLabel(LineItem $lineItem): ?string
    {
        if (!is_array($lineItem->getPayload())) {
            return null;
        }

        return $lineItem->getPayload()[Constants::KEY_MIX_LABEL_CART_ITEM] ?? null;
    }

    /**
     * @param LineItem $lineItem
     * @return bool
     */
    public static function isContainsMixContainerProduct(LineItem $lineItem): bool
    {
        if (!is_array($lineItem->getPayload())) {
            return false;
        }

        return (bool)$lineItem->getPayload()[Constants::KEY_IS_MIX_CONTAINER_PRODUCT] ?? false;

    }
}
