<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints\Type;

class DateTime extends OptionType
{
    public const NAME = 'datetime';

    /**
     * @var DateTimeInterface
     */
    private $minDate;

    /**
     * @var DateTimeInterface
     */
    private $maxDate;

    /**
     * @var string
     */
    private $placeholder;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMinDate(): DateTimeInterface
    {
        return $this->minDate;
    }

    public function setMinDate( DateTimeInterface $minDate): void
    {
        $this->minDate = $minDate;
    }

    public function getMaxDate(): DateTimeInterface
    {
        return $this->maxDate;
    }

    public function setMaxDate( DateTimeInterface $maxDate): void
    {
        $this->maxDate = $maxDate;
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
        $constraints['minDate'] = [new Type('string')];
        $constraints['maxDate'] = [new Type('string')];
        $constraints['placeholder'] = [new Type('string')];

        return $constraints;
    }
}
