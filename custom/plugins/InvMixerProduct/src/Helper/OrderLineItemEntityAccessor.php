<?php declare(strict_types=1);

namespace InvMixerProduct\Helper;


use InvMixerProduct\Constants;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity as Subject;

/**
 * Class OrderLineItemAccessor
 * @package InvMixerProduct\Helper
 */
final class OrderLineItemEntityAccessor
{

    /**
     * @param Subject $subject
     * @return string|null
     */
    public static function getMixLabel(Subject $subject): ?string
    {
        if (!is_array($subject->getPayload())) {
            return null;
        }

        return $subject->getPayload()[Constants::KEY_MIX_LABEL_CART_ITEM] ?? null;
    }

    /**
     * @param Subject $subject
     * @return bool
     */
    public static function isContainsMixContainerProduct(Subject $subject): bool
    {

        $payload = $subject->getPayload();
        if (!is_array($payload)) {
            return false;
        }

        $value = $payload[Constants::KEY_IS_MIX_CONTAINER_PRODUCT] ?? false;

        return (bool)$value;

    }

    /**
     * @param Subject $subject
     * @return bool
     */
    public static function isContainsMixBaseProduct(Subject $subject): bool
    {

        $payload = $subject->getPayload();
        if (!is_array($payload)) {
            return false;
        }

        $value = $payload[Constants::KEY_IS_MIX_BASE_PRODUCT] ?? false;

        return (bool)$value;
    }

    /**
     * @param Subject $subject
     * @return bool
     */
    public static function isContainsMixChildProduct(Subject $subject): bool
    {

        $payload = $subject->getPayload();
        if (!is_array($payload)) {
            return false;
        }

        $value = $payload[Constants::KEY_IS_MIX_CHILD_PRODUCT] ?? false;

        return (bool)$value;
    }
}
