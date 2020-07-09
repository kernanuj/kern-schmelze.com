<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\UpdateOrder;

use KlarnaPayment\Components\Client\Hydrator\Struct\Delivery\DeliveryStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\LineItem\LineItemStructHydratorInterface;
use KlarnaPayment\Components\Client\Request\UpdateOrderRequest;
use KlarnaPayment\Components\Helper\OrderFetcherInterface;
use LogicException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;

class UpdateOrderRequestHydrator implements UpdateOrderRequestHydratorInterface
{
    /** @var OrderConverter */
    private $orderConverter;

    /** @var OrderFetcherInterface */
    private $orderFetcher;

    /** @var LineItemStructHydratorInterface */
    private $lineItemHydrator;

    /** @var DeliveryStructHydratorInterface */
    private $deliveryHydrator;

    public function __construct(
        OrderConverter $orderConverter,
        OrderFetcherInterface $orderFetcher,
        LineItemStructHydratorInterface $lineItemHydrator,
        DeliveryStructHydratorInterface $deliveryHydrator
    ) {
        $this->orderConverter   = $orderConverter;
        $this->orderFetcher     = $orderFetcher;
        $this->lineItemHydrator = $lineItemHydrator;
        $this->deliveryHydrator = $deliveryHydrator;
    }

    public function hydrate(OrderEntity $orderEntity, Context $context): UpdateOrderRequest
    {
        if (null === $orderEntity->getCurrency()) {
            throw new LogicException('could not find order currency');
        }

        $order = $this->orderFetcher->getOrderFromOrder($orderEntity->getId(), $context);

        if (null === $order) {
            throw new LogicException('could not find order via id');
        }

        $cart = $this->orderConverter->convertToCart($order, $context);

        $lineItems = $this->hydrateOrderLines($cart, $orderEntity->getCurrency(), $context);

        $request = new UpdateOrderRequest();
        $request->assign([
            'orderId'      => $this->getKlarnaOrderId($orderEntity),
            'salesChannel' => $orderEntity->getSalesChannelId(),
            'lineItems'    => $lineItems,
            'precision'    => $orderEntity->getCurrency()->getDecimalPrecision(),
            'orderAmount'  => $orderEntity->getPrice()->getTotalPrice(),
        ]);

        return $request;
    }

    private function hydrateOrderLines(Cart $cart, CurrencyEntity $currency, Context $context): array
    {
        $orderLines = [];

        $lineItems  = $cart->getLineItems();
        $deliveries = $cart->getDeliveries();

        foreach ($this->lineItemHydrator->hydrate($lineItems, $currency, $context) as $orderLine) {
            $orderLines[] = $orderLine;
        }

        foreach ($this->deliveryHydrator->hydrate($deliveries, $currency, $context) as $orderLine) {
            $orderLines[] = $orderLine;
        }

        return array_filter($orderLines);
    }

    private function getKlarnaOrderId(OrderEntity $orderEntity): string
    {
        if (null === $orderEntity->getTransactions()) {
            throw new LogicException('could not locate the klarna_order_id field in any order transaction');
        }

        foreach ($orderEntity->getTransactions() as $transaction) {
            if (null === $transaction->getStateMachineState()) {
                continue;
            }

            if ($transaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_CANCELLED) {
                continue;
            }

            if (empty($transaction->getCustomFields()['klarna_order_id'])) {
                continue;
            }

            return $transaction->getCustomFields()['klarna_order_id'];
        }

        throw new LogicException('could not locate the klarna_order_id field in any order transaction');
    }
}
