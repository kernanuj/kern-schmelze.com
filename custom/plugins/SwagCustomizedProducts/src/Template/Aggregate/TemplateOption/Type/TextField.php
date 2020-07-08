<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class TextField extends OptionType
{
    public const NAME = 'textfield';

    /**
     * @var int
     */
    private $minLength = 0;

    /**
     * @var int
     */
    private $maxLength;

    /**
     * @var string
     */
    private $placeholder;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMinLength(): int
    {
        return $this->minLength;
    }

    public function setMinLength(int $minLength): void
    {
        $this->minLength = $minLength;
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function setMaxLength(int $maxLength): void
    {
        $this->maxLength = $maxLength;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function getConstraints(): array
    {
        $constraints = parent::getConstraints();
        $constraints['minLength'] = [new NotBlank(), new Type('int')];
        $constraints['maxLength'] = [new NotBlank(), new Type('int')];
        $constraints['placeholder'] = [new Type('string')];

        return $constraints;
    }
}
