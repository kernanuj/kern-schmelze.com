<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

interface OptionTypeInterface
{
    public function getName(): string;

    /**
     * Gets the constraints of the option type
     * Format:
     *  [
     *   'propertyName' => [new Constraint(), new OtherConstraint()],
     *   'propertyName2' => [new Constraint(), new OtherConstraint()],
     *  ]
     */
    public function getConstraints(): array;
}
