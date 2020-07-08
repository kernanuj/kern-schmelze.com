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

class FileMediaUploadValidator implements CustomizedProductsMediaUploadValidatorInterface
{
    use MimeTypeValidationTrait;

    public const TYPE = 'customized_products_files';

    /**
     * @return string[]
     */
    public function getMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/x-pdf',
        ];
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function validate(UploadedFile $file): void
    {
        $valid = $this->checkMimeType($file, [
            'pdf' => ['application/pdf', 'application/x-pdf'],
        ]);

        if (!$valid) {
            $mimeType = $file->getMimeType() ?? '';
            throw new FileTypeNotAllowedException($mimeType, $this->getType());
        }
    }
}
