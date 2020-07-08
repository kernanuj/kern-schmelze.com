<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Cart;

use Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Swag\CustomizedProducts\Core\Checkout\Cart\CustomizedProductsCartService;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class CustomizedProductsCartServiceTest extends TestCase
{
    use ServicesTrait;

    private const EXAMPLE_CUSTOMER_TEXT = 'exampleCustomerText';

    /**
     * @var CustomizedProductsCartService
     */
    private $customizedProductsCartService;

    protected function setUp(): void
    {
        $this->customizedProductsCartService = $this->getContainer()->get(CustomizedProductsCartService::class);
    }

    public function testValidateOptionValuesRemovesNotBelongingOptions(): void
    {
        $badId = Uuid::randomHex();
        $data = new RequestDataBag(
            [
                $badId => [
                    'value' => 'someVeryMaliciousPayload',
                ],
            ]
        );
        $validOption = new TemplateOptionEntity();
        $validOption->setId(Uuid::randomHex());

        $this->customizedProductsCartService->validateOptionValues(
            $data,
            new TemplateOptionCollection([$validOption])
        );

        static::assertNotTrue($data->has($badId));
    }

    public function testAddOptionsWillThrowExceptionWhenUnableToFindMatchingOption(): void
    {
        $customizedProductLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $data = new RequestDataBag(
            [
                Uuid::randomHex() => [
                    'value' => 'I may never be found',
                ],
            ]
        );
        $option = new TemplateOptionEntity();
        $option->setId(Uuid::randomHex());

        $this->expectException( Exception::class);
        $this->expectExceptionMessage('Option entity not found');
        $this->customizedProductsCartService->addOptions(
            $customizedProductLineItem,
            $data,
            1,
            new TemplateOptionCollection([$option])
        );
    }

    public function testAddOptionsWillNotAddOptionChildItemWithEmptyValue(): void
    {
        $customizedProductLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $optionId = Uuid::randomHex();
        $data = new RequestDataBag(
            [
                $optionId => [
                    'value' => null,
                ],
            ]
        );
        $option = new TemplateOptionEntity();
        $option->setId($optionId);
        $option->setType(Checkbox::NAME);

        $this->customizedProductsCartService->addOptions(
            $customizedProductLineItem,
            $data,
            1,
            new TemplateOptionCollection([$option])
        );

        static::assertCount(0, $customizedProductLineItem->getChildren());
    }

    public function testAddOptionsWithTextFieldValue(): void
    {
        $customizedProductLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $optionId = Uuid::randomHex();
        $data = new RequestDataBag(
            [
                $optionId => [
                    'value' => self::EXAMPLE_CUSTOMER_TEXT,
                ],
            ]
        );
        $option = new TemplateOptionEntity();
        $option->setId($optionId);
        $option->setType(TextField::NAME);

        $this->customizedProductsCartService->addOptions(
            $customizedProductLineItem,
            $data,
            1,
            new TemplateOptionCollection([$option])
        );

        static::assertCount(1, $customizedProductLineItem->getChildren());
        $optionLineItem = $customizedProductLineItem->getChildren()->first();
        static::assertNotNull($optionLineItem);
        static::assertSame(self::EXAMPLE_CUSTOMER_TEXT, $optionLineItem->getPayloadValue('value'));
    }
}
