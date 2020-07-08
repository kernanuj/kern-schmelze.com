<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionCollection;

interface CustomizedProductCartServiceInterface
{
    public function createCustomizedProductsLineItem(string $customizedProductsTemplateId, string $productId, int $productQuantity): LineItem;

    public function loadOptionEntities(string $templateId, RequestDataBag $options, Context $context): TemplateOptionCollection;

    public function validateOptionValues(RequestDataBag $options, TemplateOptionCollection $optionEntities): RequestDataBag;

    public function addOptions(LineItem $customizedProductsLineItem, RequestDataBag $options, int $productQuantity, TemplateOptionCollection $optionEntities): void;
}
