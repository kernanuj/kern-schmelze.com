<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Symfony\Component\Validator\Constraints\Type;

class ColorPicker extends OptionType
{
    public const NAME = 'colorpicker';

    /**
     * @var string
     */
    private $color = '#000000';

    /**
     * @var string
     */
    private $colorMode = 'rgb';

    /**
     * @var string
     */
    private $placeholder;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getColorMode(): string
    {
        return $this->colorMode;
    }

    public function setColorMode(string $colorMode): void
    {
        $this->colorMode = $colorMode;
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
        $constraints['placeholder'] = [new Type('string')];

        return $constraints;
    }
}
