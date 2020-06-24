<?php declare(strict_types=1);

namespace InvExportLabel;

/**
 * Class Constants
 * @package InvExportLabel
 */
final class Constants
{

    public const LABEL_TYPE_MIXER_PRODUCT = 'inv_mixer_product';

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
