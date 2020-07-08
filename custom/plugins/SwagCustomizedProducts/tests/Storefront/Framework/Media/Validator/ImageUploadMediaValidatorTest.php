<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Framework\Media\Validator;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Storefront\Framework\Media\Exception\FileTypeNotAllowedException;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\ImageMediaUploadValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadMediaValidatorTest extends TestCase
{
    use KernelTestBehaviour;

    public const FIXTURE_DIR = __DIR__ . '/fixtures';

    public function testUploadImage(): void
    {
        $file = $this->getUploadFixture('image.png');
        $validator = $this->getContainer()->get(ImageMediaUploadValidator::class);
        $validator->validate($file);
        static::assertTrue(true);
    }

    public function testUploadDocument(): void
    {
        $file = $this->getUploadFixture('empty.pdf');
        $validator = $this->getContainer()->get(ImageMediaUploadValidator::class);
        self::expectException(FileTypeNotAllowedException::class);

        $validator->validate($file);
    }

    public function testUploadImageWithInvalidMimeType(): void
    {
        $file = new UploadedFile(self::FIXTURE_DIR . '/empty.pdf', 'invalid.png', 'image/png', null, true);
        $validator = $this->getContainer()->get(ImageMediaUploadValidator::class);
        self::expectException(FileTypeNotAllowedException::class);

        $validator->validate($file);
    }

    private function getUploadFixture(string $filename): UploadedFile
    {
        return new UploadedFile(self::FIXTURE_DIR . '/' . $filename, $filename, null, null, true);
    }
}
