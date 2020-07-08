<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

class ImageSelect extends Select
{
    public const NAME = 'imageselect';

    public function getName(): string
    {
        return self::NAME;
    }
}
