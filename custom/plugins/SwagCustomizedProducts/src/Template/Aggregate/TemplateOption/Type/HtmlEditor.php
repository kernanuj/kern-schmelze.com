<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Symfony\Component\Validator\Constraints\Type;

class HtmlEditor extends OptionType
{
    public const NAME = 'htmleditor';

    public const ALLOWED_ELEMENTS = [
        'p',
        'b',
        'i',
        'u',
        'strike',
    ];

    public const ALLOWED_ATTRIBUTES = [];

    /**
     * @var string
     */
    private $placeholder;

    public function getName(): string
    {
        return self::NAME;
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
