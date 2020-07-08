<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class FileUpload extends OptionType
{
    public const NAME = 'fileupload';

    /**
     * @var int
     */
    private $maxCount;

    /**
     * @var int
     */
    private $maxFileSize;

    /**
     * @var array
     */
    private $supportedExtensions;

    /**
     * @var array
     */
    private $files;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMaxCount(): int
    {
        return $this->maxCount;
    }

    public function setMaxCount(int $maxCount): void
    {
        $this->maxCount = $maxCount;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    public function setMaxFileSize(int $maxFileSize): void
    {
        $this->maxFileSize = $maxFileSize;
    }

    public function getSupportedExtensions(): array
    {
        return $this->supportedExtensions;
    }

    public function setSupportedExtensions(array $supportedExtensions): void
    {
        $this->supportedExtensions = $supportedExtensions;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function getConstraints(): array
    {
        $constraints = parent::getConstraints();
        $constraints['maxCount'] = [new NotBlank(), new Type('int')];
        $constraints['maxFileSize'] = [new NotBlank(), new Type('int')];

        return $constraints;
    }
}
