<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Framework\Media\Validator;

use Shopware\Storefront\Framework\Media\Exception\FileTypeNotAllowedException;
use Shopware\Storefront\Framework\Media\Validator\MimeTypeValidationTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageMediaUploadValidator implements CustomizedProductsMediaUploadValidatorInterface
{
    use MimeTypeValidationTrait;

    public const TYPE = 'customized_products_images';

    /**
     * @return string[]
     */
    public function getMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
        ];
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function validate(UploadedFile $file): void
    {
        $fileMimeType = $file->getMimeType() ?? '';
        $valid = $this->checkMimeType($file, [
            'jpe|jpg|jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
        ]);

        if (!$valid) {
            throw new FileTypeNotAllowedException($fileMimeType, $this->getType());
        }

        // additional mime type validation
        // we detect the mime type over the `getimagesize` extension
        $imageSize = \getimagesize($file->getPath() . '/' . $file->getFileName());
        $mimeType = \is_array($imageSize) && isset($imageSize['mime']) ? $imageSize['mime'] : '';
        if ($mimeType !== $fileMimeType) {
            throw new FileTypeNotAllowedException($fileMimeType, $this->getType());
        }
    }
}
