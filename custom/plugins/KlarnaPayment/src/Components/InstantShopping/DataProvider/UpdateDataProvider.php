<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use Exception;
use KlarnaPayment\Components\CartHasher\CartHasherInterface;
use KlarnaPayment\Components\Client\Struct\Attachment;
use KlarnaPayment\Components\InstantShopping\CartHandler\CartHandlerInterface;
use KlarnaPayment\Components\InstantShopping\ContextHandler\ContextHandlerInterface;
use KlarnaPayment\Components\InstantShopping\CustomerHandler\CustomerHandlerInterface;
use KlarnaPayment\Components\InstantShopping\OrderHandler\OrderHandlerInterface;
use KlarnaPayment\Components\Struct\ExtraMerchantData;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UpdateDataProvider implements UpdateDataProviderInterface
{
    public const ATTACHMENT_CONTENT_TYPE = 'content_type: "application/vnd.klarna.internal.emd-v2+json';

    /** @var CartService */
    protected $cartService;

    /** @var CartHandlerInterface */
    protected $cartHandler;

    /** @var ContextHandlerInterface */
    protected $contextHandler;

    /** @var CustomerHandlerInterface */
    protected $customerHandler;

    /** @var OrderHandlerInterface */
    protected $orderHandler;

    /** @var CartHasherInterface */
    protected $cartHasher;

    /** @var RouterInterface */
    protected $router;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductLineItemFactory */
    protected $lineItemFactory;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        CartService $cartService,
        CartHasherInterface $cartHasher,
        CartHandlerInterface $cartHandler,
        ContextHandlerInterface $contextHandler,
        CustomerHandlerInterface $customerHandler,
        OrderHandlerInterface $orderHandler,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        ProductLineItemFactory $lineItemFactory,
        LoggerInterface $logger
    ) {
        $this->cartService     = $cartService;
        $this->cartHasher      = $cartHasher;
        $this->cartHandler     = $cartHandler;
        $this->contextHandler  = $contextHandler;
        $this->customerHandler = $customerHandler;
        $this->orderHandler    = $orderHandler;
        $this->router          = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->lineItemFactory = $lineItemFactory;
        $this->logger          = $logger;
    }

    public function buildAttachment(ExtraMerchantData $extraMerchantData): ?Attachment
    {
        if (!empty($extraMerchantData->getAttachment())) {
            $attachment = new Attachment();
            $attachment->assign([
                'data'         => $extraMerchantData->getAttachment(),
                'content_type' => self::ATTACHMENT_CONTENT_TYPE,
            ]);

            return $attachment;
        }

        return null;
    }

    public function createInstantShoppingCart(string $productId, int $productQuantity, SalesChannelContext $context): ?Cart
    {
        $cart = $this->cartService->createNew(Uuid::randomHex());

        try {
            $lineItem = $this->lineItemFactory->create($productId, ['quantity' => $productQuantity]);

            $cart = $this->cartService->add($cart, $lineItem, $context);
        } catch (Exception $e) {
            $this->logger->error('Error adding product to new cart', [$e->getMessage()]);

            return null;
        }

        return $cart;
    }

    public function getInstantShoppingCart(SalesChannelContext $context): ?Cart
    {
        try {
            return $this->cartService->getCart($context->getToken(), $context);
        } catch (Exception $e) {
            $this->logger->error('Error retrieving cart', [$e->getMessage()]);

            return null;
        }
    }
}
