<?php declare(strict_types=1);

namespace InvExportLabel;

use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;

/**
 * Class Constants
 * @package InvExportLabel
 */
final class Constants
{

    public const LABEL_TYPE_MIXER_PRODUCT = 'inv_mixer_product';

    public const SYSTEM_CONFIG_MIXER_PRODUCT_FILTER_ORDER_STATE = 'InvExportLabel.config.mixerProductFilterOrderState';
    public const SYSTEM_CONFIG_MIXER_PRODUCT_BEST_BEFORE_MONTHS = 'InvExportLabel.config.mixerProductBestBeforeMonths';

    /**
     * @see \Dompdf\Adapter\CPDF::$PAPER_SIZES
     */
    public const LABEL_PDF_PAPER_SIZE = '0,0,71,71';

    /**
     * @param string $type
     * @return bool
     */
    public static function isValidLabelType(string $type): bool
    {
        return in_array($type, self::allAvailableLabelTypes());
    }

    /**
     * @return array|string[]
     */
    public static function allAvailableLabelTypes(): array
    {
        return [
            self::LABEL_TYPE_MIXER_PRODUCT
        ];
    }

    /**
     * @return array
     */
    public static function allOrderStates():array {
        return [
            OrderStates::STATE_OPEN,
            OrderStates::STATE_IN_PROGRESS,
            OrderStates::STATE_COMPLETED,
            #OrderStates::STATE_CANCELLED,
        ];
    }

    /**
     * @return array
     */
    public static function allOrderTransactionStates():array {
        return [
            OrderTransactionStates::STATE_OPEN,
            OrderTransactionStates::STATE_PAID,
            OrderTransactionStates::STATE_PARTIALLY_PAID,
            #OrderTransactionStates::STATE_REFUNDED,
            #OrderTransactionStates::STATE_PARTIALLY_REFUNDED,
            #OrderTransactionStates::STATE_CANCELLED,
            #OrderTransactionStates::STATE_REMINDED,
            #OrderTransactionStates::STATE_FAILED,
            OrderTransactionStates::STATE_IN_PROGRESS,
        ];
    }

    /**
     * @return array
     */
    public static function allOrderDeliveryStates():array {
        return [
            OrderDeliveryStates::STATE_OPEN,
            OrderDeliveryStates::STATE_PARTIALLY_SHIPPED,
            OrderDeliveryStates::STATE_SHIPPED,
            #OrderDeliveryStates::STATE_RETURNED,
            #OrderDeliveryStates::STATE_PARTIALLY_RETURNED,
            #OrderDeliveryStates::STATE_CANCELLED,
        ];
    }
}
