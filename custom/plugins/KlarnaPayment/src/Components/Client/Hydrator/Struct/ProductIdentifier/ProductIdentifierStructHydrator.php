<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\ProductIdentifier;

use KlarnaPayment\Components\Client\Struct\ProductIdentifier;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductIdentifierStructHydrator implements ProductIdentifierStructHydratorInterface
{
    public function hydrate(ProductEntity $product, SalesChannelContext $context): ?ProductIdentifier
    {
        $identifier = new ProductIdentifier();
        $identifier->assign([
            'categoryPath'           => $this->buildCategoryPath($product),
            'globalTradeItemNumber'  => $product->getEan(),
            'manufacturerPartNumber' => $product->getManufacturerNumber(),
        ]);

        if (null !== $product->getManufacturer()) {
            $identifier->assign([
                'brand' => $product->getManufacturer()->getName(),
            ]);
        }

        return $identifier;
    }

    private function buildCategoryPath(ProductEntity $product): ?string
    {
        if (null === $product->getCategories()) {
            return null;
        }

        /** @var null|CategoryEntity $category */
        $category = $product->getCategories()->first();

        if (null === $category || empty($category->getBreadcrumb())) {
            return null;
        }

        $breadcrumbs = array_slice($category->getBreadcrumb(), 1);

        return implode(' > ', $breadcrumbs);
    }
}
