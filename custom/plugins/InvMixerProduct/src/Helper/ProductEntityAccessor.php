<?php declare(strict_types=1);

namespace InvMixerProduct\Helper;


use InvMixerProduct\Constants;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Content\Product\ProductEntity as Subject;

/**
 * Class SubjectAccessor
 * @package InvMixerProduct\Helper
 */
final class ProductEntityAccessor
{

    /**
     * @param Subject $subject
     * @return string|null
     */
    public static function fromCustomFieldsGetDataIngredients(Subject $subject): ?string
    {

        $customFields = $subject->getCustomFields();
        if (!$customFields) {
            return null;
        }

        return $customFields[Constants::PRODUCT_CUSTOM_FIELD_KEY_DATA_INGREDIENTS] ?? null;
    }

    /**
     * @param Subject $subject
     * @return string|null
     */
    public static function fromCustomFieldsGetDataAllergens(Subject $subject): ?string
    {

        $customFields = $subject->getCustomFields();
        if (!$customFields) {
            return null;
        }

        return $customFields[Constants::PRODUCT_CUSTOM_FIELD_KEY_DATA_ALLERGENS] ?? null;
    }

    /**
     * @param Subject $subject
     * @return Weight
     */
    public static function getWeight(Subject $subject): ?Weight
    {
        $purchaseUnit = $subject->getPurchaseUnit();

        if(!$purchaseUnit){
            return null;
        }

        return Weight::xGrams((int)$purchaseUnit);
    }
}
