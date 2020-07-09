<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\Route;

use OpenApi\Annotations as OA;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\CustomizedProductCartServiceInterface;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsCartError;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class AddCustomizedProductsToCartRoute extends AbstractAddCustomizedProductsToCartRoute
{
    /**
     * @var CustomizedProductCartServiceInterface
     */
    private $customizedProductCartService;

    /**
     * @var CartService
     */
    private $cartService;

    public function __construct(
        CustomizedProductCartServiceInterface $customizedProductCartService,
        CartService $cartService
    ) {
        $this->customizedProductCartService = $customizedProductCartService;
        $this->cartService = $cartService;
    }

    public function getDecorated(): AbstractAddCustomizedProductsToCartRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *     path="/customized-products/add-to-cart",
     *     description="Adds a custom product to the cart",
     *     operationId="addCustomizedProductToCart",
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
     *     )
     * )
     *
     * @Route("/store-api/v{version}/customized-products/add-to-cart", name="store-api.customized-products.add-to-cart", methods={"POST"})
     */
    public function add(
        RequestDataBag $requestDataBag,
        Request $request,
        SalesChannelContext $salesChannelContext,
        ?Cart $cart
    ): NoContentResponse {
        /** @var RequestDataBag|null $customizedProductsData */
        $customizedProductsData = $requestDataBag->get(
            CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER
        );
        if ($customizedProductsData === null) {
            throw new MissingRequestParameterException(
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER
            );
        }

        $templateId = $customizedProductsData->getAlnum('id');
        if ($templateId === '') {
            throw new MissingRequestParameterException('id');
        }

        if (!Uuid::isValid($templateId)) {
            throw new InvalidUuidException($templateId);
        }

        $cart = $cart ?? $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        $lineItems = $request->request->get('lineItems', []);
        /** @var array|false $product */
        $product = \reset($lineItems);
        if ($product === false) {
            throw new MissingRequestParameterException('lineItems');
        }

        $productQuantity = (int) $product['quantity'];

        $customizedProductsLineItem = $this->customizedProductCartService->createCustomizedProductsLineItem(
            $customizedProductsData->getAlnum('id'),
            $product['id'],
            $productQuantity
        );

        /** @var RequestDataBag|null $options */
        $options = $customizedProductsData->get('options');
        if ($options === null) {
            $options = new RequestDataBag();
        }

        $optionEntities = $this->customizedProductCartService->loadOptionEntities(
            $templateId,
            $options,
            $salesChannelContext->getContext()
        );
        $options = $this->customizedProductCartService->validateOptionValues($options, $optionEntities);

        try {
            $this->customizedProductCartService->addOptions(
                $customizedProductsLineItem,
                $options,
                $productQuantity,
                $optionEntities
            );
        } catch (SwagCustomizedProductsCartError $exception) {
            $cart->addErrors($exception);

            return new NoContentResponse();
        }

        // Used to identify whether or not the custom product is added to the cart via the product detail page
        $customizedProductsLineItem->addExtension(
            CustomizedProductsCartController::ADD_TO_CART_IDENTIFIER,
            null
        );

        $this->cartService->add($cart, $customizedProductsLineItem, $salesChannelContext);

        return new NoContentResponse();
    }
}
