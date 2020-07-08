<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\Route;

use OpenApi\Annotations as OA;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException;
use Shopware\Core\Checkout\Cart\Exception\OrderNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Order\Transformer\LineItemTransformer;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class ReOrderCustomizedProductsRoute extends AbstractReOrderCustomizedProductsRoute
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    public function __construct(CartService $cartService, EntityRepositoryInterface $orderRepository)
    {
        $this->cartService = $cartService;
        $this->orderRepository = $orderRepository;
    }

    public function getDecorated(): AbstractReOrderCustomizedProductsRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *     path="/customized-products/reorder/orderId",
     *     description="Reorders an order with support for Custom Products",
     *     operationId="reorderCustomizedProduct",
     *     tags={"Store API", "Customized Products"},
     *     @OA\Parameter(
     *         parameter="orderId",
     *         name="orderId",
     *         in="url",
     *         description="Id of the order you want to repeat",
     *         @OA\Schema(type="string", format="uuid"),
     *     ),
     *     @OA\Response(
     *         response="200",
     *     )
     * )
     *
     * @throws InvalidUuidException
     * @throws OrderNotFoundException
     * @throws LineItemNotFoundException
     *
     * @Route("/store-api/v{version}/customized-products/reorder/{orderId}", name="store-api.customized-products.reorder", methods={"POST"})
     */
    public function reOrder(
        string $orderId,
        Request $request,
        SalesChannelContext $salesChannelContext,
        ?Cart $cart
    ): NoContentResponse {
        $cart = $cart ?? $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        if (!Uuid::isValid($orderId)) {
            throw new InvalidUuidException($orderId);
        }

        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        $order = $this->orderRepository->search($criteria, $salesChannelContext->getContext())->get($orderId);

        if (!$order instanceof OrderEntity) {
            throw new OrderNotFoundException($orderId);
        }

        $orderLineItems = $order->getLineItems();
        if (!$orderLineItems instanceof OrderLineItemCollection || $orderLineItems->count() <= 0) {
            throw new LineItemNotFoundException($orderId);
        }

        $lineItems = LineItemTransformer::transformFlatToNested($orderLineItems);
        if ($lineItems->count() <= 0) {
            throw new LineItemNotFoundException($orderId);
        }

        $referenceIdQuantityMap = [];
        foreach ($orderLineItems as $orderLineItem) {
            $referencedId = $orderLineItem->getReferencedId();
            if ($referencedId === null) {
                continue;
            }

            $referenceIdQuantityMap[$referencedId] = $orderLineItem->getQuantity();
        }

        $this->sanitizeQuantities($referenceIdQuantityMap, $lineItems);

        foreach ($lineItems as $lineItem) {
            $this->removeOriginalIdExtensionFromLineItem($lineItem);
            $this->cartService->add($cart, $lineItem, $salesChannelContext);
        }

        return new NoContentResponse();
    }

    private function removeOriginalIdExtensionFromLineItem(LineItem $lineItem): void
    {
        $lineItem->removeExtension(OrderConverter::ORIGINAL_ID);

        foreach ($lineItem->getChildren() as $child) {
            $this->removeOriginalIdExtensionFromLineItem($child);
        }
    }

    private function sanitizeQuantities(array $referenceIdQuantityMap, LineItemCollection $lineItems): void
    {
        foreach ($lineItems as $lineItem) {
            $referencedId = $lineItem->getReferencedId();
            if ($referencedId === null) {
                continue;
            }

            $children = $lineItem->getChildren();
            $lineItem->setChildren(new LineItemCollection());
            $lineItem->setStackable(true);
            $lineItem->setQuantity($referenceIdQuantityMap[$referencedId]);
            $lineItem->setChildren($children);

            if ($lineItem->hasChildren()) {
                $this->sanitizeQuantities($referenceIdQuantityMap, $lineItem->getChildren());
            }
        }
    }
}
