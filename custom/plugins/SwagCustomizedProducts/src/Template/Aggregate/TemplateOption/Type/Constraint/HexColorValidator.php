<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use function preg_match;

class HexColorValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof HexColor) {
            throw new UnexpectedTypeException($constraint, HexColor::class);
        }

        if (empty($value)) {
            return;
        }

        if (! preg_match("/^\#([0-9a-fA-F]{6})$/", $value)
            && ! preg_match("/^\#([0-9a-fA-F]{8})$/", $value)
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
