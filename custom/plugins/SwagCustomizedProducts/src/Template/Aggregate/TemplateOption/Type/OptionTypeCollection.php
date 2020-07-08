<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Shopware\Core\Framework\Struct\Collection;

/**
 * @method void                            set(?string $key, OptionTypeInterface $entity)
 * @method \Generator<OptionTypeInterface> getIterator()
 * @method OptionTypeInterface[]           getElements()
 * @method OptionTypeInterface|null        first()
 * @method OptionTypeInterface|null        last()
 */
class OptionTypeCollection extends Collection
{
    public function getNames(): array
    {
        $names = [];
        foreach ($this->getIterator() as $type) {
            $names[] = $type->getName();
        }

        return $names;
    }
}
