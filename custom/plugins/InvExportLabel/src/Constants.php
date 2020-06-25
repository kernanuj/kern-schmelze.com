<?php declare(strict_types=1);

namespace InvExportLabel;

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
}
