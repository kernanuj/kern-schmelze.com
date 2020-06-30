<?php declare(strict_types=1);

namespace InvMixerProduct;

/**
 * Class Constants
 * @package InvMixerProduct
 */
final class Constants {

    public const PLUGIN_PREFIX_SNAKE_CASE = 'inv_mixer_product_';
    public const KEY_MIX_LABEL_CART_ITEM = self::PLUGIN_PREFIX_SNAKE_CASE.'_mix_label';
    public const DESIGN_BLACK = 'black';
    public const DESIGN_WHITE = 'white';

    public const WEIGHT_UNIT_GRAMS = 'g';

    public const LABEL_REGEX_PATTERN = "/^[a-z0-9\.,\-öäü\(\):]{1,}$/i";
}
