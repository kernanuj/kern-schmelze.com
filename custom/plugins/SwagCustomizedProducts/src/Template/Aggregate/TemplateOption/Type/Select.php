<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Symfony\Component\Validator\Constraints\Type;

class Select extends OptionType
{
    public const NAME = 'select';

    /**
     * @var array
     */
    private $elements;

    /**
     * @var bool
     */
    private $isMultiSelect;

    /**
     * @var bool
     */
    private $isDropDown;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

    public function isMultiSelect(): bool
    {
        return $this->isMultiSelect;
    }

    public function setIsMultiSelect(bool $isMultiSelect): void
    {
        $this->isMultiSelect = $isMultiSelect;
    }

    public function isDropDown(): bool
    {
        return $this->isDropDown;
    }

    public function setIsDropDown(bool $isDropDown): void
    {
        $this->isDropDown = $isDropDown;
    }

    public function getConstraints(): array
    {
        $constraints = parent::getConstraints();

        $constraints['isMultiSelect'] = [new Type('bool')];
        $constraints['isDropDown'] = [new Type('bool')];

        return $constraints;
    }
}
