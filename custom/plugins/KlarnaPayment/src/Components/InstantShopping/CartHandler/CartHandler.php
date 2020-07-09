<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\CartHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use KlarnaPayment\Components\InstantShopping\CustomerHandler\CustomerHandler;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartHandler implements CartHandlerInterface
{
    /** @var EntityRepositoryInterface */
    private $productRepository;

    /** @var CartService */
    private $cartService;

    /** @var Connection */
    private $connection;

    /** @var CustomerHandler */
    private $customerHandler;

    public function __construct(
        EntityRepositoryInterface $productRepository,
        CartService $cartService,
        Connection $connection,
        CustomerHandler $customerHandler
    ) {
        $this->productRepository = $productRepository;
        $this->cartService       = $cartService;
        $this->connection        = $connection;
        $this->customerHandler   = $customerHandler;
    }

    public function getInstantShoppingCartByToken(string $token, SalesChannelContext $context): Cart
    {
        $cart = $this->cartService->getCart($token, $context, $this->cartService::SALES_CHANNEL, false);

        /** @noinspection IsEmptyFunctionUsageInspection */
        if (empty($cart)) {
            throw new CartTokenNotFoundException('Cart not found: ' . $token);
        }

        return $cart;
    }

    public function getCustomerIdFromCart(Cart $cart): ?string
    {
        return $this->getCustomerIdFromCartToken($cart->getToken());
    }

    public function getCustomerIdFromCartToken(string $token): ?string
    {
        try {
            $content = $this->connection->fetchColumn(
                'SELECT `cart`.`customer_id` FROM `cart` INNER JOIN `customer` ON `customer`.`id` = `cart`.`customer_id` WHERE `token` = :token',
                ['token' => $token]
            );
        } catch (DBALException $e) {
            return '';
        }

        if (empty($content)) {
            return '';
        }

        return Uuid::fromBytesToHex((string) $content);
    }

    public function getCustomerFromCart(Cart $cart, SalesChannelContext $context): ?CustomerEntity
    {
        $customerId = $this->getCustomerIdFromCart($cart);

        return $this->customerHandler->getCustomerById($customerId, $context);
    }
}
