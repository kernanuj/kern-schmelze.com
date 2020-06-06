<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\UpdateOrder;

use KlarnaPayment\Components\Client\Hydrator\Struct\Delivery\OrderDeliveryStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\LineItem\OrderLineItemStructHydratorInterface;
use KlarnaPayment\Components\Client\Request\UpdateOrderRequest;
use LogicException;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;

class UpdateOrderRequestHydrator implements UpdateOrderRequestHydratorInterface
{
    /** @var OrderLineItemStructHydratorInterface */
    private $lineItemHydrator;

    /** @var OrderDeliveryStructHydratorInterface */
    private $deliveryHydrator;

    public function __construct(OrderLineItemStructHydratorInterface $lineItemHydrator, OrderDeliveryStructHydratorInterface $deliveryHydrator)
    {
        $this->lineItemHydrator = $lineItemHydrator;
        $this->deliveryHydrator = $deliveryHydrator;
    }

    public function hydrate(OrderEntity $orderEntity, Context $context): UpdateOrderRequest
    {
        if (null === $orderEntity->getLineItems() || null === $orderEntity->getDeliveries() || null === $orderEntity->getCurrency()) {
            throw new LogicException('could not find order via id');
        }

        $lineItems = $this->hydrateOrderLines(
            $orderEntity->getLineItems(),
            $orderEntity->getDeliveries(),
            $orderEntity->getCurrency(),
            $context
        );

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

    private function hydrateOrderLines(OrderLineItemCollection $lineItems, OrderDeliveryCollection $deliveries, CurrencyEntity $currency, Context $context): array
    {
        $orderLines = [];

        foreach ($lineItems as $item) {
            foreach ($this->lineItemHydrator->hydrate($item, $currency, $context) as $orderLine) {
                $orderLines[] = $orderLine;
            }
        }

        foreach ($deliveries as $delivery) {
            foreach ($this->deliveryHydrator->hydrate($delivery, $currency) as $orderLine) {
                $orderLines[] = $orderLine;
            }
        }

        return array_filter($orderLines);
    }

    private function getKlarnaOrderId(OrderEntity $orderEntity): string
    {
        /** @var OrderTransactionEntity[] $transactions */
        $transactions = $orderEntity->getTransactions();

        // TODO: Only one transaction per order is supported, this could change in the future.
        foreach ($transactions as $transaction) {
            if (empty($transaction->getCustomFields()['klarna_order_id'])) {
                continue;
            }

            return $transaction->getCustomFields()['klarna_order_id'];
        }

        throw new LogicException('could not locate the klarna_order_id field in any order transaction');
    }

    private function getTotalTaxAmount(CalculatedTaxCollection $taxes): float
    {
        $totalTaxAmount = 0;

        foreach ($taxes as $tax) {
            $totalTaxAmount += $tax->getTax();
        }

        return $totalTaxAmount;
    }
}
