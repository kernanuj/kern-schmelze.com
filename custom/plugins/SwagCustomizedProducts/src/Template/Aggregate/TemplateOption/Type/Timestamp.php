<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints\Type;

class Timestamp extends OptionType
{
    public const NAME = 'timestamp';

    /**
     * @var DateTimeInterface
     */
    private $startTime;

    /**
     * @var DateTimeInterface
     */
    private $endTime;

    /**
     * @var string
     */
    private $placeholder;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getStartTime(): DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime( DateTimeInterface $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime( DateTimeInterface $endTime): void
    {
        $this->endTime = $endTime;
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
        $constraints['startTime'] = [new Type('string')];
        $constraints['endTime'] = [new Type('string')];
        $constraints['placeholder'] = [new Type('string')];

        return $constraints;
    }
}
