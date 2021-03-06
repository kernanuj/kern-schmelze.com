<?php declare(strict_types=1);

namespace InvMixerProduct;

use InvMixerProduct\Value\Amount;
use InvMixerProduct\Value\BaseProduct;
use InvMixerProduct\Value\Design;
use InvMixerProduct\Value\FillDelimiter;
use InvMixerProduct\Value\Weight;

/**
 * Class Constants
 * @package InvMixerProduct
 */
final class Constants
{

    /**
     *
     */
    public const PLUGIN_PREFIX_SNAKE_CASE = 'inv_mixer_product_';
    /**
     *
     */
    public const KEY_MIX_LABEL_CART_ITEM = self::PLUGIN_PREFIX_SNAKE_CASE . '_mix_label';
    public const KEY_IS_MIX_CONTAINER_PRODUCT = self::PLUGIN_PREFIX_SNAKE_CASE . '_is_mix_container_product';
    public const KEY_IS_MIX_BASE_PRODUCT = self::PLUGIN_PREFIX_SNAKE_CASE . '_is_mix_base_product';
    public const KEY_IS_MIX_CHILD_PRODUCT = self::PLUGIN_PREFIX_SNAKE_CASE . '_is_mix_child_product';
    public const KEY_CONTAINER_DEFINITION = self::PLUGIN_PREFIX_SNAKE_CASE . '_container_definition';
    public const KEY_MIX_ENTITY_ID = self::PLUGIN_PREFIX_SNAKE_CASE . '_entity_id';
    public const KEY_MIX_ITEM_ENTITY_ID = self::PLUGIN_PREFIX_SNAKE_CASE . '_item_entity_id';
    public const KEY_MIX_DISPLAY_ID = self::PLUGIN_PREFIX_SNAKE_CASE . '_display_id';


    public const LINE_ITEM_TYPE_IDENTIFIER = self::PLUGIN_PREFIX_SNAKE_CASE.'_product';
    public const CART_DATA_KEY_CONTAINER_SALES_CHANNEL_PRODUCT = self::PLUGIN_PREFIX_SNAKE_CASE.'_container_product';
    public const CART_DATA_KEY_BASE_SALES_CHANNEL_PRODUCT = self::PLUGIN_PREFIX_SNAKE_CASE.'_base_product';
    public const CART_DATA_KEY_CHILD_SALES_CHANNEL_PRODUCTS = self::PLUGIN_PREFIX_SNAKE_CASE.'_child_products';

    public const PRODUCT_CUSTOM_FIELD_KEY_DATA_INGREDIENTS = 'custom_product_data_ingredients';
    public const PRODUCT_CUSTOM_FIELD_KEY_DATA_ALLERGENS = 'custom_product_data_allergens';

    public const DEFAULT_MIX_CONTAINER_LABEL = 'Meine Schokoladentafel';
    /**
     *
     */
    public const WEIGHT_UNIT_GRAMS = 'g';

    /**
     *
     */
    public const LABEL_REGEX_PATTERN = "/^[a-z0-9\.,\-öäü\(\):\s]{1,}$/i";


    public const CONTAINER_PRODUCT_PRODUCT_NUMBER_PREFIX = 'impc_';

    public const CATALOG_PRODUCT_TAG_PRODUCT_ITEM = 'mixer-product-item';
    public const CATALOG_PRODUCT_TAG_CATEGORY_NUTS = 'mixer-product-category-nuts';
    public const CATALOG_PRODUCT_TAG_CATEGORY_SWEETS = 'mixer-product-category-sweets';
    public const CATALOG_PRODUCT_TAG_CATEGORY_DRIEDFRUIT = 'mixer-product-category-driedfruit';
    public const CATALOG_PRODUCT_TAG_CATEGORY_SPICES = 'mixer-product-category-spices';

    public const MIX_DISPLAY_ID_OFFSET = 100000;

    /**
     * @return Design[]
     */
    public static function allKSPackageDesigns(): array
    {
        return [
            self::KS_PACKAGE_DESIGN_1(),
            self::KS_PACKAGE_DESIGN_2(),
            self::KS_PACKAGE_DESIGN_3(),
            self::KS_PACKAGE_DESIGN_4(),
            self::KS_PACKAGE_DESIGN_5(),
            self::KS_PACKAGE_DESIGN_6()
        ];
    }

    /**
     * @return array|string[]
     */
    public static function VALID_CATALOG_PRODUCT_TAGS():array {
        return [
            self::CATALOG_PRODUCT_TAG_CATEGORY_NUTS,
            self::CATALOG_PRODUCT_TAG_CATEGORY_SWEETS,
            self::CATALOG_PRODUCT_TAG_CATEGORY_DRIEDFRUIT,
            self::CATALOG_PRODUCT_TAG_CATEGORY_SPICES
        ];
    }
    /**
     * @return Design
     */
    public static function KS_PACKAGE_DESIGN_1(): Design
    {
        return Design::fromString('design_1');
    }

    /**
     * @return Design
     */
    public static function KS_PACKAGE_DESIGN_2(): Design
    {
        return Design::fromString('design_2');
    }

    /**
     * @return Design
     */
    public static function KS_PACKAGE_DESIGN_3(): Design
    {
        return Design::fromString('design_3');
    }

    /**
     * @return Design
     */
    public static function KS_PACKAGE_DESIGN_4(): Design
    {
        return Design::fromString('design_4');
    }

    /**
     * @return Design
     */
    public static function KS_PACKAGE_DESIGN_5(): Design
    {
        return Design::fromString('design_5');
    }

    /**
     * @return Design
     */
    public static function KS_PACKAGE_DESIGN_6(): Design
    {
        return Design::fromString('design_6');
    }

    /**
     * @return Weight[]
     */
    public static function allKSPackageSizes(): array
    {
        return [
            self::KS_PACKAGE_SIZE_100(),
            self::KS_PACKAGE_SIZE_500(),
        ];
    }

    /**
     * @return Weight
     */
    public static function KS_PACKAGE_SIZE_100(): Weight
    {
        return Weight::xGrams(100);
    }

    /**
     * @return Weight
     */
    public static function KS_PACKAGE_SIZE_500(): Weight
    {
        return Weight::xGrams(500);
    }

    /**
     * @return FillDelimiter[]
     */
    public static function allFillDelimiter(): array
    {
        return [
            self::KS_FILL_DELIMITER_100(),
            self::KS_FILL_DELIMITER_500()
        ];
    }

    /**
     * @return FillDelimiter
     */
    public static function KS_FILL_DELIMITER_100(): FillDelimiter
    {
        return new FillDelimiter(
            Weight::xGrams(100),
            Amount::fromInt(4)
        );
    }

    /**
     * @return FillDelimiter
     */
    public static function KS_FILL_DELIMITER_500(): FillDelimiter
    {
        return new FillDelimiter(
            Weight::xGrams(500),
            Amount::fromInt(8)
        );
    }

    /**
     * @return BaseProduct[]
     */
    public static function allKSPackageBaseProducts(): array
    {
        return [
            self::KS_BASEPRODUCT_MILCHSCHOKOLOADE(),
            self::KS_BASEPRODUCT_WEISSESCHOKOLADE(),
            self::KS_BASEPRODUCT_RUBYSCHOKOLADE(),
            self::KS_BASEPRODUCT_ZARBITTER(),
        ];
    }

    /**
     * @return BaseProduct
     */
    public static function KS_BASEPRODUCT_MILCHSCHOKOLOADE(): BaseProduct
    {
        return BaseProduct::fromString('Milchschokolade');
    }

    /**
     * @return BaseProduct
     */
    public static function KS_BASEPRODUCT_WEISSESCHOKOLADE(): BaseProduct
    {
        return BaseProduct::fromString('WeisseSchokolade');
    }

    /**
     * @return BaseProduct
     */
    public static function KS_BASEPRODUCT_RUBYSCHOKOLADE(): BaseProduct
    {
        return BaseProduct::fromString('RubySchokolade');
    }

    /**
     * @return BaseProduct
     */
    public static function KS_BASEPRODUCT_ZARBITTER(): BaseProduct
    {
        return BaseProduct::fromString('Zartbitterschokolade');
    }

}
