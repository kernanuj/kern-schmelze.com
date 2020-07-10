<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Swag\CustomizedProducts\Core\Content\Product\ProductWrittenSubscriber;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Template\SalesChannel\Price\PriceService;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PriceService
     */
    private $priceService;

    public function __construct(EntityRepositoryInterface $productRepository, PriceService $priceService)
    {
        $this->productRepository = $productRepository;
        $this->priceService = $priceService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => [
                ['enrichOptionPriceAbleDisplayPrices', 100],
                ['removeCustomizedProductsTemplateFromNoneInheritedVariant', 200],
            ],
        ];
    }

    public function enrichOptionPriceAbleDisplayPrices(ProductPageLoadedEvent $event): void
    {
        /** @var TemplateEntity|null $customizedProductsTemplate */
        $customizedProductsTemplate = $event->getPage()->getProduct()->getExtension('swagCustomizedProductsTemplate');

        if ($customizedProductsTemplate === null) {
            return;
        }

        $this->priceService->calculateCurrencyPrices($customizedProductsTemplate, $event->getSalesChannelContext());
    }

    public function removeCustomizedProductsTemplateFromNoneInheritedVariant(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();
        $parentId = $product->getParentId();

        if ($parentId === null) {
            return;
        }

        $customFields = $product->getCustomFields();
        if ($customFields === null
            || !\array_key_exists(ProductWrittenSubscriber::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD, $customFields)
        ) {
            return;
        }

        if ($customFields[ProductWrittenSubscriber::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD]) {
            return;
        }

        if ($this->variantHasOwnTemplateAssigned($product->getId(), $event->getContext())) {
            return;
        }

        $product->removeExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
    }

    private function variantHasOwnTemplateAssigned(string $id, Context $context): bool
    {
        $considerInheritance = $context->considerInheritance();
        $criteria = new Criteria([$id]);
        $criteria->addAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
        $criteria->setLimit(1);
        $context->setConsiderInheritance(false);

        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search($criteria, $context)->first();
        $context->setConsiderInheritance($considerInheritance);

        if ($product === null) {
            return false;
        }

        return $product->hasExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
    }
}
