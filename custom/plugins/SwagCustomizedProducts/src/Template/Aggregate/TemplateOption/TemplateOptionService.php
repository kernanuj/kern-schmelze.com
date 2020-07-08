<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption;

use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\OptionTypeCollection;

class TemplateOptionService implements TemplateOptionServiceInterface
{
    /**
     * @var OptionTypeCollection
     */
    private $typeCollection;

    public function __construct(OptionTypeCollection $typeCollection)
    {
        $this->typeCollection = $typeCollection;
    }

    public function getSupportedTypes(): array
    {
        return $this->typeCollection->getNames();
    }
}
