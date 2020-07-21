<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class NumberField extends OptionType
{
    public const NAME = 'numberfield';

    /**
     * @var float
     */
    private $defaultValue;

    /**
     * @var float
     */
    private $minValue;

    /**
     * @var float
     */
    private $maxValue;

    /**
     * @var float
     */
    private $interval;

    /**
     * @var int
     */
    private $decimalPlaces;

    /**
     * @var string
     */
    private $placeholder;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefaultValue(): float
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(float $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function getMinValue(): float
    {
        return $this->minValue;
    }

    public function setMinValue(float $minValue): void
    {
        $this->minValue = $minValue;
    }

    public function getMaxValue(): float
    {
        return $this->maxValue;
    }

    public function setMaxValue(float $maxValue): void
    {
        $this->maxValue = $maxValue;
    }

    public function getInterval(): float
    {
        return $this->interval;
    }

    public function setInterval(float $interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @deprecated tag:v3.0.0 will be removed. Use getInterval instead
     */
    public function getDecimalPlaces(): int
    {
        return $this->decimalPlaces;
    }

    /**
     * @deprecated tag:v3.0.0 will be removed. Use setInterval instead
     */
    public function setDecimalPlaces(int $decimalPlaces): void
    {
        $this->decimalPlaces = $decimalPlaces;
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
        $constraints['defaultValue'] = [new NotBlank(), new Type('numeric')];
        $constraints['minValue'] = [new NotBlank(), new Type('numeric')];
        $constraints['maxValue'] = [new NotBlank(), new Type('numeric')];
        $constraints['interval'] = [new NotBlank(), new Type('numeric')];
        $constraints['placeholder'] = [new Type('string')];

        return $constraints;
    }
}
