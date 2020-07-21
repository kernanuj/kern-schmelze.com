<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\OrderValidator;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderEntity;

class OrderValidator implements OrderValidatorInterface
{
    public function isKlarnaOrder(OrderEntity $orderEntity): bool
    {
        if (null === $orderEntity->getTransactions()) {
            return false;
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

            return true;
        }

        return false;
    }
}
