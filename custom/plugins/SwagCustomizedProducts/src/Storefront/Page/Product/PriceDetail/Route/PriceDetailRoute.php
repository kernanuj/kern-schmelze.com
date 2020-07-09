<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route;

use OpenApi\Annotations as OA;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Event\CartCreatedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsCartError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsPriceCalculationError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AbstractAddCustomizedProductsToCartRoute;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class PriceDetailRoute extends AbstractPriceDetailRoute
{
    public const PRICE_DETAIL_CALCULATION_EXTENSION_KEY = 'price-detail-calculation';

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var AbstractAddCustomizedProductsToCartRoute
     */
    private $addCustomizedProductsToCartRoute;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        CartService $cartService,
        AbstractAddCustomizedProductsToCartRoute $addCustomizedProductsToCartRoute,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->cartService = $cartService;
        $this->addCustomizedProductsToCartRoute = $addCustomizedProductsToCartRoute;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getDecorated(): AbstractPriceDetailRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *     path="/customized-products/price-detail",
     *     description="Displays detailed price information",
     *     operationId="customizedProductPriceDetail",
     *     tags={"Store API", "Customized Products"},
     *     @OA\Parameter(
     *         parameter="customized-products-template",
     *         name="customized-products-template",
     *         in="body",
     *         description="The template configuration",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="id",
     *                 description="The template id this configuration is for",
     *                 type="string",
     *                 format="uuid"
     *             ),
     *             @OA\Property(
     *                 property="options",
     *                 description="An array of options and their values",
     *                 type="object"
     *             ),
     *             example={"id": "19489f5e16e14ac8b7c1dad26a258923", "options": { "b7d2554b0ce847cd82f3ac9bd1c0dfca": { "value": "Example textfield value" }}}
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Property(
     *                 property="productPrice",
     *                 type="float"
     *             ),
     *             @OA\Property(
     *                 property="totalPrice",
     *                 type="float"
     *             ),
     *             @OA\Property(
     *                 property="surchargesSubTotal",
     *                 type="float"
     *             ),
     *             @OA\Property(
     *                 property="oneTimeSurchargesSubTotal",
     *                 type="float"
     *             ),
     *             @OA\Property(
     *                 property="surcharges",
     *                 type="array"
     *             ),
     *             @OA\Property(
     *                 property="oneTimeSurcharges",
     *                 type="array"
     *             ),
     *             example={"productPrice": 10, "totalPrice": 25, "surchargesSubTotal": 5, "oneTimeSurchargesSubTotal": 10, "surcharges": {{"Option1": 5}}, "oneTimeSurcharges": {{"Option2": 10}}}
     *         )
     *     )
     * )
     *
     * @Route("/store-api/v{version}/customized-products/price-detail", name="store-api.customized-products.price-detail", methods={"POST"})
     */
    public function priceDetail(Request $request, SalesChannelContext $context): PriceDetailResponse
    {
        $cart = new Cart(Uuid::randomHex(), Uuid::randomHex());
        $cart->addExtension(
            self::PRICE_DETAIL_CALCULATION_EXTENSION_KEY,
            new PriceDetailCalculationExtension()
        );
        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart));
        $this->addCustomizedProductsToCartRoute->add(new RequestDataBag($request->request->all()), $request, $context, $cart);

        $customizedProductLineItem = $cart->getLineItems()->first();
        if ($customizedProductLineItem === null) {
            $customizedProductCartError = $cart->getErrors()->filterInstance(SwagCustomizedProductsCartError::class)->first();
            throw $customizedProductCartError ?? new SwagCustomizedProductsPriceCalculationError();
        }

        $productPrice = $this->getProductPrice($customizedProductLineItem);
        if ($productPrice === null) {
            throw new SwagCustomizedProductsPriceCalculationError($customizedProductLineItem->getId());
        }

        $customizedProductPrice = $customizedProductLineItem->getPrice();
        if ($customizedProductPrice === null) {
            throw new SwagCustomizedProductsPriceCalculationError($customizedProductLineItem->getId());
        }

        [$surcharges, $oneTimeSurcharges] = $this->getSurcharges($customizedProductLineItem);

        return new PriceDetailResponse(
            $productPrice->getTotalPrice(),
            $customizedProductPrice->getTotalPrice(),
            \array_sum($surcharges),
            \array_sum($oneTimeSurcharges),
            $surcharges,
            $oneTimeSurcharges
        );
    }

    private function getProductPrice(LineItem $customizedProductLineItem): ?CalculatedPrice
    {
        $productLineItem = $customizedProductLineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();
        if ($productLineItem === null) {
            return null;
        }

        return $productLineItem->getPrice();
    }

    private function getSurcharges(LineItem $customizedProductLineItem): array
    {
        $surcharges = [];
        $oneTimeSurcharges = [];
        $optionLineItems = $customizedProductLineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $optionValueLineItems = $optionLineItems->filterFlatByType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );

        $this->splitSurcharges($surcharges, $oneTimeSurcharges, $optionLineItems);
        $this->splitSurcharges($surcharges, $oneTimeSurcharges, new LineItemCollection($optionValueLineItems));

        return [
            $surcharges,
            $oneTimeSurcharges,
        ];
    }

    private function splitSurcharges(array &$surcharges, array &$oneTimeSurcharges, LineItemCollection $lineItems): void
    {
        foreach ($lineItems as $lineItem) {
            $price = $lineItem->getPrice();
            if ($price === null || $price->getTotalPrice() <= 0.0) {
                continue;
            }

            $label = $lineItem->getLabel();
            if ($label === null) {
                continue;
            }

            if ($lineItem->getPayloadValue('isOneTimeSurcharge')) {
                $oneTimeSurcharges[$label] = $price->getTotalPrice();
                continue;
            }

            $surcharges[$label] = $price->getTotalPrice();
        }
    }
}
