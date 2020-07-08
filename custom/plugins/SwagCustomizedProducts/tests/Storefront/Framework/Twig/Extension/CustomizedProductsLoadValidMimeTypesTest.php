<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Framework\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\FileMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\ImageMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Framework\Twig\Extension\CustomizedProductsLoadValidMimeTypes;

class CustomizedProductsLoadValidMimeTypesTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var CustomizedProductsLoadValidMimeTypes
     */
    private $filter;

    public function setUp(): void
    {
        $this->filter = $this->getContainer()->get(CustomizedProductsLoadValidMimeTypes::class);
    }

    public function testGetValidMimeTypes(): void
    {
        $imageValidator = new ImageMediaUploadValidator();
        $fileValidator = new FileMediaUploadValidator();

        static::assertSame($imageValidator->getMimeTypes(), $this->filter->getValidMimeTypes($imageValidator->getType()));
        static::assertSame($fileValidator->getMimeTypes(), $this->filter->getValidMimeTypes($fileValidator->getType()));
        static::assertEmpty($this->filter->getValidMimeTypes('invalid_type'), 'Return value of invalid type is not empty.');
    }
}
