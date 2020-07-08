<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

class Checkbox implements OptionTypeInterface
{
    public const NAME = 'checkbox';

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $description;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getConstraints(): array
    {
        return [];
    }
}
