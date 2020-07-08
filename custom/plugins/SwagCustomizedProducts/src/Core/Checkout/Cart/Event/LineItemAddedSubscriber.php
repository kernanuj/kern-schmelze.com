<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\Event;

use Shopware\Core\Checkout\Cart\Event\LineItemAddedEvent;
use Shopware\Core\Content\Product\Cart\ProductNotFoundError;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LineItemAddedSubscriber implements EventSubscriberInterface
{
    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    public function __construct(SalesChannelRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LineItemAddedEvent::class => 'onLineItemAddedToCart',
        ];
    }

    public function onLineItemAddedToCart(LineItemAddedEvent $event): void
    {
        $lineItem = $event->getLineItem();

        if ($lineItem->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE) {
            return;
        }

        if ($lineItem->hasExtension(CustomizedProductsCartController::ADD_TO_CART_IDENTIFIER)) {
            return;
        }

        $referencedId = $lineItem->getReferencedId();
        if ($referencedId === null || $referencedId === '') {
            return;
        }

        $criteria = new Criteria([$referencedId]);
        $criteria->addAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);

        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search($criteria, $event->getContext())->first();

        if ($product === null || !$product->hasExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)) {
            return;
        }

        // Custom product is added by ordernumber and has to be removed
        $event->getCart()->remove($lineItem->getId());
        $event->getCart()->getErrors()->add(new ProductNotFoundError($referencedId));
    }
}
