<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper;

use KlarnaPayment\Components\Helper\PaymentHelper\PaymentHelperInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;

class OrderFetcher implements OrderFetcherInterface
{
    /** @var EntityRepositoryInterface */
    private $orderRepository;

    /** @var PaymentHelperInterface */
    private $paymentHelper;

    public function __construct(EntityRepositoryInterface $orderRepository, PaymentHelperInterface $paymentHelper)
    {
        $this->orderRepository = $orderRepository;
        $this->paymentHelper   = $paymentHelper;
    }

    public function getOrderFromOrderAddress(string $orderAddressId, Context $context): ?OrderEntity
    {
        $criteria = $this->getOrderCriteria();
        $criteria->addFilter(new EqualsFilter('addresses.id', Uuid::fromBytesToHex($orderAddressId)));

        return $this->orderRepository->search($criteria, $context)->first();
    }

    public function getOrderFromLineItem(string $lineItemId, Context $context): ?OrderEntity
    {
        $criteria = $this->getOrderCriteria();
        $criteria->addFilter(new EqualsFilter('lineItems.id', Uuid::fromBytesToHex($lineItemId)));

        return $this->orderRepository->search($criteria, $context)->first();
    }

    public function getOrderFromOrder(string $orderId, Context $context): ?OrderEntity
    {
        $criteria = $this->getOrderCriteria();
        $criteria->addFilter(new EqualsFilter('id', Uuid::fromBytesToHex($orderId)));

        return $this->orderRepository->search($criteria, $context)->first();
    }

    private function getOrderCriteria(): Criteria
    {
        $criteria = new Criteria();
        $criteria->addAssociation('addresses');
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('orderCustomer');
        $criteria->addAssociation('addresses');
        $criteria->addAssociation('addresses.salutation');
        $criteria->addAssociation('addresses.country');
        $criteria->addAssociation('deliveries');
        $criteria->addAssociation('deliveries.shippingOrderAddress');
        $criteria->addAssociation('deliveries.shippingOrderAddress.country');
        $criteria->addAssociation('deliveries.shippingOrderAddress.salutation');
        $criteria->addAssociation('deliveries.shippingOrderAddress.country');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('currency');
        $criteria->addFilter(new EqualsAnyFilter('transactions.paymentMethodId', $this->paymentHelper->getKlarnaPaymentMethodIds()));
        $criteria->addSorting(new FieldSorting('lineItems.createdAt'));

        return $criteria;
    }
}
