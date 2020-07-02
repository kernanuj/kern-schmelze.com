<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\SocialShopping\Test\Validation\Validator;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagSocialShopping\Component\Network\Facebook;
use SwagSocialShopping\Component\Network\Instagram;
use SwagSocialShopping\Component\Validation\NetworkValidationError;
use SwagSocialShopping\Component\Validation\Validator\GoogleProductCategoryValidator;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use SwagSocialShopping\Installer\CustomFieldInstaller;

class GoogleProductCategoryValidatorTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testSupportsTestNetwork(): void
    {
        $googleProductCategoryValidator = new GoogleProductCategoryValidator();

        static::assertTrue($googleProductCategoryValidator->supports(Facebook::class));
        static::assertTrue($googleProductCategoryValidator->supports(Instagram::class));
    }

    public function testValidationSuccessful(): void
    {
        $testProduct = $this->createProduct();
        $category = $this->createCategory();
        $category->setTranslated(
            [
                'customFields' => [
                    CustomFieldInstaller::SOCIAL_SHOPPING_CUSTOM_FIELD_GOOGLE_CATEGORY_NAME => 'some value',
                ],
            ]
        );
        $categoryCollection = $this->createCategoryCollection($category);

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            new SocialShoppingSalesChannelEntity()
        );

        static::assertFalse($validationResult->hasErrors());
    }

    public function testValidationHasErrorsNoCustomFields(): void
    {
        $testProduct = $this->createProduct();
        $categoryCollection = $this->createCategoryCollection();

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            new SocialShoppingSalesChannelEntity()
        );

        static::assertTrue($validationResult->hasErrors());
    }

    public function testValidationHasErrorsWrongCustomFields(): void
    {
        $testProduct = $this->createProduct();
        $category = $this->createCategory();
        $category->setTranslated(
            [
                'customFields' => [
                    'foo' => 'bar',
                ],
            ]
        );
        $categoryCollection = $this->createCategoryCollection($category);

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            new SocialShoppingSalesChannelEntity()
        );

        static::assertTrue($validationResult->hasErrors());
    }

    public function testValidationHasErrorsInvalidValueNull(): void
    {
        $testProduct = $this->createProduct();
        $category = $this->createCategory();
        $category->setTranslated(
            [
                'customFields' => [
                    CustomFieldInstaller::SOCIAL_SHOPPING_CUSTOM_FIELD_GOOGLE_CATEGORY_NAME => null,
                ],
            ]
        );
        $categoryCollection = $this->createCategoryCollection($category);

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            new SocialShoppingSalesChannelEntity()
        );

        static::assertTrue($validationResult->hasErrors());
    }

    public function testValidationHasErrorsInvalidValueEmptyString(): void
    {
        $testProduct = $this->createProduct();
        $category = $this->createCategory();
        $category->setTranslated(
            [
                'customFields' => [
                    CustomFieldInstaller::SOCIAL_SHOPPING_CUSTOM_FIELD_GOOGLE_CATEGORY_NAME => '',
                ],
            ]
        );
        $categoryCollection = $this->createCategoryCollection($category);

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            new SocialShoppingSalesChannelEntity()
        );

        static::assertTrue($validationResult->hasErrors());
    }

    public function testValidationSuccessfulForDefault(): void
    {
        $testProduct = $this->createProduct();
        $categoryCollection = $this->createCategoryCollection();

        $socialShoppingSalesChannel = new SocialShoppingSalesChannelEntity();
        $socialShoppingSalesChannel->setConfiguration([
            'defaultGoogleProductCategory' => 123456,
        ]);

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            $socialShoppingSalesChannel
        );

        static::assertFalse($validationResult->hasErrors());
    }

    public function testValidationFailedForDefault(): void
    {
        $testProduct = $this->createProduct();
        $categoryCollection = $this->createCategoryCollection();

        $socialShoppingSalesChannel = new SocialShoppingSalesChannelEntity();
        $socialShoppingSalesChannel->setConfiguration([
            'defaultGoogleProductCategory' => 0,
        ]);

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            $socialShoppingSalesChannel
        );

        static::assertTrue($validationResult->hasErrors());
    }

    public function testValidationFailedEmptyDefault(): void
    {
        $testProduct = $this->createProduct();
        $categoryCollection = $this->createCategoryCollection();
        $socialShoppingSalesChannel = new SocialShoppingSalesChannelEntity();

        $testProduct->setCategories($categoryCollection);
        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            $socialShoppingSalesChannel
        );

        static::assertTrue($validationResult->hasErrors());
    }

    public function testValidationFailed(): void
    {
        $identifier = Uuid::randomHex();
        $testProduct = $this->createProduct($identifier);

        $categoryCollection = $this->createCategoryCollection();
        $testProduct->setCategories($categoryCollection);

        $validationResult = (new GoogleProductCategoryValidator())->validate(
            $testProduct,
            new SocialShoppingSalesChannelEntity()
        );

        static::assertTrue($validationResult->hasErrors());
        $errors = $validationResult->getErrors();
        static::assertCount(1, $errors);
        $firstError = $errors->first();
        static::assertInstanceOf(NetworkValidationError::class, $firstError);
        $firstErrorParams = $firstError->getParams();
        static::assertEquals('testproduct', $firstErrorParams['productName']);
        static::assertEquals($identifier, $firstErrorParams['productId']);
        static::assertEquals(GoogleProductCategoryValidator::class, $validationResult->getValidatorName());
    }

    private function createProduct(?string $uuid = null): ProductEntity
    {
        $uuid = $uuid ?? Uuid::randomHex();
        $testProduct = new ProductEntity();
        $testProduct->setId($uuid);
        $testProduct->setName('testproduct');

        return $testProduct;
    }

    private function createCategory(): CategoryEntity
    {
        $category = new CategoryEntity();
        $category->setId(Uuid::randomHex());

        return $category;
    }

    private function createCategoryCollection(?CategoryEntity $category = null): CategoryCollection
    {
        $category = $category ?? $this->createCategory();

        return new CategoryCollection([$category]);
    }
}
