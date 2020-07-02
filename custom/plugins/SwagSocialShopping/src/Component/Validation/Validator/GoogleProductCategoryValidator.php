<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\Component\Validation\Validator;

use Shopware\Core\Content\Product\ProductEntity;
use SwagSocialShopping\Component\Network\Facebook;
use SwagSocialShopping\Component\Network\GoogleShopping;
use SwagSocialShopping\Component\Network\Instagram;
use SwagSocialShopping\Component\Validation\NetworkProductValidationResult;
use SwagSocialShopping\Component\Validation\NetworkProductValidatorInterface;
use SwagSocialShopping\Component\Validation\NetworkValidationError;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use SwagSocialShopping\Installer\CustomFieldInstaller;
use function in_array;

class GoogleProductCategoryValidator implements NetworkProductValidatorInterface
{
    public const GOOGLE_PRODUCT_CATEGORY_MISSING = 'swag-social-shopping.validation.google-product-category-missing';

    public function supports(string $networkName): bool
    {
        return in_array($networkName, [Facebook::class, Instagram::class, GoogleShopping::class], true);
    }

    public function validate(
        ProductEntity $productEntity,
        SocialShoppingSalesChannelEntity $socialShoppingSalesChannelEntity
    ): NetworkProductValidationResult {
        $validationResult = new NetworkProductValidationResult(self::class);

        if ($this->hasGoogleCategory($productEntity)
            || $this->hasDefaultGoogleCategory($socialShoppingSalesChannelEntity)
        ) {
            return $validationResult;
        }

        $error = new NetworkValidationError(
            self::GOOGLE_PRODUCT_CATEGORY_MISSING,
            [
                'productId' => $productEntity->getId(),
                'productName' => $productEntity->getName(),
            ]
        );
        $validationResult->getErrors()->add($error);

        return $validationResult;
    }

    private function hasGoogleCategory(ProductEntity $product): bool
    {
        $categoryCollection = $product->getCategories();

        if ($categoryCollection === null) {
            return false;
        }

        $firstCategory = $categoryCollection->first();

        if ($firstCategory === null) {
            return false;
        }

        $categoryTranslation = $firstCategory->getTranslated();

        return isset($categoryTranslation['customFields'][CustomFieldInstaller::SOCIAL_SHOPPING_CUSTOM_FIELD_GOOGLE_CATEGORY_NAME])
            && $categoryTranslation['customFields'][CustomFieldInstaller::SOCIAL_SHOPPING_CUSTOM_FIELD_GOOGLE_CATEGORY_NAME] !== '';
    }

    private function hasDefaultGoogleCategory(SocialShoppingSalesChannelEntity $socialShoppingSalesChannelEntity): bool
    {
        $config = $socialShoppingSalesChannelEntity->getConfiguration();

        return isset($config['defaultGoogleProductCategory']) && (bool) $config['defaultGoogleProductCategory'];
    }
}
