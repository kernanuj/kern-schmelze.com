<?php

namespace Sendcloud\Shipping\Service\Business;

use Sendcloud\Shipping\Core\BusinessLogic\Entity\Order;
use Sendcloud\Shipping\Core\BusinessLogic\Entity\OrderItem;
use Sendcloud\Shipping\Core\BusinessLogic\Interfaces\OrderService as OrderServiceInterface;
use Sendcloud\Shipping\Core\Infrastructure\Logger\Logger;
use Sendcloud\Shipping\Entity\Currency\CurrencyRepository;
use Sendcloud\Shipping\Entity\Order\OrderDeliveryRepository;
use Sendcloud\Shipping\Entity\Order\OrderRepository;
use Sendcloud\Shipping\Entity\Product\ProductRepository;
use Sendcloud\Shipping\Entity\Shipment\ShipmentEntityRepository;
use Sendcloud\Shipping\Service\Utility\DeliveryStateMapper;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\Currency\CurrencyEntity;

/**
 * Class OrderService
 *
 * @package Sendcloud\Shipping\Service\Business
 */
class OrderService implements OrderServiceInterface
{

    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var OrderDeliveryRepository
     */
    private $orderDeliveryRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;
    /**
     * @var ShipmentEntityRepository
     */
    private $shipmentRepository;
    /**
     * @var DeliveryStateMapper
     */
    private $deliveryStateMapper;

    /**
     * OrderService constructor.
     *
     * @param OrderRepository $orderRepository
     * @param ProductRepository $productRepository
     * @param CurrencyRepository $currencyRepository
     * @param ShipmentEntityRepository $shipmentRepository
     * @param OrderDeliveryRepository $orderDeliveryRepository
     * @param DeliveryStateMapper $deliveryStateMapper
     */
    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CurrencyRepository $currencyRepository,
        ShipmentEntityRepository $shipmentRepository,
        OrderDeliveryRepository $orderDeliveryRepository,
        DeliveryStateMapper $deliveryStateMapper
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->currencyRepository = $currencyRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderDeliveryRepository = $orderDeliveryRepository;
        $this->deliveryStateMapper = $deliveryStateMapper;
    }

    /**
     * Gets all order IDs from source system.
     *
     * @return string[]
     */
    public function getAllOrderIds(): array
    {
        $orderIds = [];
        try {
            $orderIds = $this->orderRepository->getOrderIds();
        } catch (\Exception $exception) {
            Logger::logError("An error occurred when fetching order ids from database: {$exception->getMessage()}", 'Integration');
        }

        return  $orderIds;
    }

    /**
     * Gets all orders for passed batch ids formatted in the proper way.
     *
     * @param array $batchOrderIds
     *
     * @return Order[] based on passed ids
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getOrders(array $batchOrderIds): array
    {
        $orders = [];
        $sourceOrders = $this->orderRepository->getOrders($batchOrderIds);
        /** @var OrderEntity $sourceOrder */
        foreach ($sourceOrders as $sourceOrder) {
            $order = $this->buildOrderEntity($sourceOrder);

            $orders[] = $order;
        }

        return  $orders;
    }

    /**
     * Returns order for passed id or null if order is not found.
     *
     * @param int|string $orderId
     *
     * @return Order|null
     * @throws InconsistentCriteriaIdsException
     */
    public function getOrderById($orderId): ?Order
    {
        return $this->getOrderByNumber($orderId);
    }

    /**
     * Returns order for passed order number or null if order is not found. In most systems order ID and
     * order number are the same. SendCloud doesn't send external order ID in some webhook payloads.
     *
     * @param int|string $orderNumber
     *
     * @return Order|null
     * @throws InconsistentCriteriaIdsException
     */
    public function getOrderByNumber($orderNumber): ?Order
    {
        $sourceOrder = $this->orderRepository->getOrderByNumber($orderNumber);

        return $sourceOrder ? $this->buildOrderEntity($sourceOrder) : null;
    }

    /**
     * Updates order information on the host system
     *
     * @param Order $order
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function updateOrderStatus(Order $order): void
    {
        try {
            $this->shipmentRepository->updateShipment(
                $order->getNumber(),
                $order->getSendCloudStatus(),
                $order->getToServicePoint(),
                $order->getSendCloudTrackingNumber(),
                $order->getSendCloudTrackingUrl()
            );

            $sourceOrder = $this->orderRepository->getOrderByNumber((string)$order->getNumber());
            if (!$sourceOrder) {
                return;
            }

            $deliveryCollection = $sourceOrder->getDeliveries();
            $delivery = $deliveryCollection ? $deliveryCollection->first() : null;
            if (!$delivery) {
                return;
            }

            $id = $delivery->getId();
            $this->orderDeliveryRepository->updateTrackingNumber(
                $id,
                (string)$order->getSendCloudTrackingNumber(),
                Context::createDefaultContext()
            );
            $this->deliveryStateMapper->updateStatus($id, $order->getSendCloudStatusId());
        } catch (\Exception $exception) {
            Logger::logError("Failed to update order status: {$exception->getMessage()}", 'Integration');
        }
    }

    /**
     * Informs service about completed synchronization of provided orders (IDs).
     *
     * @param array $orderIds
     */
    public function orderSyncCompleted(array $orderIds): void
    {
        // Intentionally left empty. We do not need this functionality
    }

    /**
     * Calculates order weight
     *
     * @param LineItemCollection $lineItemCollection
     *
     * @return float
     * @throws InconsistentCriteriaIdsException
     */
    public function calculateTotalWeight(LineItemCollection $lineItemCollection): float
    {
        $totalWeight = 0;
        $productsMap = $this->createProductsMap($lineItemCollection);

        /** @var LineItem $sourceItem */
        foreach ($lineItemCollection as $sourceItem) {
            $quantity = $sourceItem->getQuantity();
            $productId = $sourceItem->getId();
            if (array_key_exists($productId, $productsMap)) {
                /** @var ProductEntity $productEntity */
                $productEntity = $productsMap[$productId];
                $totalWeight += $quantity * $productEntity->getWeight();
            }
        }

        return round(((float)$totalWeight), 2);
    }

    /**
     * Creates order entity
     *
     * @param OrderEntity $sourceOrder
     *
     * @return Order
     * @throws InconsistentCriteriaIdsException
     * @throws \Exception
     */
    private function buildOrderEntity(OrderEntity $sourceOrder): Order
    {
        $order = new Order();
        $order->setId($sourceOrder->getId());
        $order->setNumber($sourceOrder->getOrderNumber());
        $this->setOrderState($sourceOrder, $order);
        $this->setPayment($sourceOrder, $order);
        $customer = $sourceOrder->getOrderCustomer();
        if ($customer) {
            $name = $customer->getFirstName();
            if (!empty($customer->getLastName())) {
                $name .= ' ' . $customer->getLastName();
            }

            $order->setCustomerName($name);
            $order->setEmail($customer->getEmail());
        }

        $order->setCurrency('EUR');
        $this->setDates($sourceOrder, $order);

        $delivery = $sourceOrder->getDeliveries() ? $sourceOrder->getDeliveries()->first() : null;
        if ($delivery) {
            $this->setDeliveryInformation($delivery, $order);
        }

        $shipment = $this->shipmentRepository->getShipmentByOrderNumber($sourceOrder->getOrderNumber());
        if ($shipment && ($servicePointId = $shipment->get('servicePointId'))) {
            $order->setToServicePoint((int)$servicePointId);
        }

        $this->setItemsAndValues($sourceOrder->getLineItems(), $order, $sourceOrder->getCurrency());

        if (!$order->getTelephone()) {
            $this->setFallbackPhone($sourceOrder, $order);
        }

        return $order;
    }

    /**
     * Set order items, total value and weight
     *
     * @param OrderLineItemCollection $sourceItems
     * @param Order $order
     * @param CurrencyEntity|null $currencyEntity
     *
     * @throws InconsistentCriteriaIdsException
     */
    private function setItemsAndValues(OrderLineItemCollection $sourceItems, Order $order, ?CurrencyEntity $currencyEntity): void
    {
        $orderItems = [];
        $totalWeight = 0;
        $totalValue = 0;
        if (!$currencyEntity || $currencyEntity->getIsoCode() === CurrencyRepository::EURO) {
            $factor = 1;
        } else {
            $factor = $this->getEurosFactor($currencyEntity->getFactor());
        }
        /** @var OrderLineItemEntity $sourceItem */
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getType() !== 'product') {
                continue;
            }

            $productId = $sourceItem->getIdentifier();
            $orderItem = new OrderItem();
            $orderItem->setProductId($productId);
            $orderItem->setDescription($sourceItem->getLabel());
            $quantity = $sourceItem->getQuantity();
            $orderItem->setQuantity($quantity);
            $value = $sourceItem->getUnitPrice();
            $totalValue += $quantity * $value;
            $orderItem->setValue(round($factor * $value, 2));
            $productEntity = $sourceItem->getProduct();
            if ($productEntity) {
                $weight = $productEntity->getWeight();
                $orderItem->setSku($productEntity->getProductNumber());
                $totalWeight += $quantity * $weight;
                $orderItem->setWeight(round($weight, 2));
                
                $orderItemProperties = [];
                foreach ($productEntity->getOptions() as $option) {
                    $group = $option->getGroup();

                    if ($group) {
                        $orderItemProperties[$group->getTranslation('name')] = $option->getTranslation('name');
                    }
                }

                $orderItem->setProperties($orderItemProperties);
            }

            $orderItems[] = $orderItem;
        }

        $order->setItems($orderItems);
        $order->setTotalValue($factor * $totalValue);
        $order->setWeight($totalWeight);
    }

    /**
     * Set shipping method and shipping address information
     *
     * @param OrderDeliveryEntity $delivery
     * @param Order $order
     */
    private function setDeliveryInformation(OrderDeliveryEntity $delivery, Order $order): void
    {
        $shippingMethod = $delivery->getShippingMethod();
        if ($shippingMethod) {
            $order->setCheckoutShippingName($shippingMethod->getTranslation('name'));
        }


        $shippingAddress = $delivery->getShippingOrderAddress();
        if ($shippingAddress) {
            $this->setAddressData($order, $shippingAddress);
        }
    }

    /**
     * Set shipping address information
     *
     * @param Order $order
     * @param OrderAddressEntity $shippingAddress
     */
    private function setAddressData(Order $order, OrderAddressEntity $shippingAddress): void
    {
        $country = $shippingAddress->getCountry();
        $order->setCountryCode($country ? $country->getIso() : '');

        $state = $shippingAddress->getCountryState();
        if ($state) {
            $toStateParts = explode('-', (string)$state->getShortCode());
            // Remove country code from state code if it exist
            if (count($toStateParts) > 1) {
                array_shift($toStateParts);
            }
            $toState = implode('-', $toStateParts);

            $order->setToState($toState);
        }

        $address = $shippingAddress->getStreet();
        if (!empty($shippingAddress->getAdditionalAddressLine1())) {
            $address .= ' ' . $shippingAddress->getAdditionalAddressLine1();
        }

        if (!empty($shippingAddress->getAdditionalAddressLine2())) {
            $address .= ' ' . $shippingAddress->getAdditionalAddressLine2();
        }

        $name = $shippingAddress->getFirstName();
        if (!empty($shippingAddress->getLastName())) {
            $name .= ' ' . $shippingAddress->getLastName();
        }

        $order->setCustomerName($name);
        $order->setAddress($address);
        $order->setPostalCode($shippingAddress->getZipcode());
        $order->setCity($shippingAddress->getCity());
        $order->setCompanyName((string)$shippingAddress->getCompany());
        $order->setTelephone($shippingAddress->getPhoneNumber());
        $order->setHouseNumber('');
    }

    /**
     * Set payment information
     *
     * @param OrderEntity $sourceOrder
     * @param Order $order
     */
    private function setPayment(OrderEntity $sourceOrder, Order $order): void
    {
        $transaction = $sourceOrder->getTransactions() ? $sourceOrder->getTransactions()->first() : null;
        if ($transaction && $transaction->getStateMachineState()) {
            $order->setPaymentStatusId($transaction->getStateMachineState()->getId());
            $order->setPaymentStatusName($transaction->getStateMachineState()->getTranslation('name'));
        }
    }

    /**
     * Set order state information
     *
     * @param OrderEntity $sourceOrder
     * @param Order $order
     */
    private function setOrderState(OrderEntity $sourceOrder, Order $order): void
    {
        $state = $sourceOrder->getStateMachineState();
        if ($state) {
            $order->setStatusId($state->getId());
            $order->setStatusName($state->getTranslation('name'));
        }
    }

    /**
     * Returns product map with id as key ['productId' => productEntity]
     *
     * @param OrderLineItemCollection|LineItemCollection|null $sourceItems
     *
     * @return ProductEntity[]
     * @throws InconsistentCriteriaIdsException
     */
    private function createProductsMap($sourceItems): array
    {
        if (!$sourceItems) {
            return [];
        }

        $productIds = $sourceItems->map(function ($sourceItem) {
            /** @var OrderLineItemEntity|LineItem $sourceItem */
            return ($sourceItem instanceof OrderLineItemEntity) ? $sourceItem->getIdentifier() : $sourceItem->getId();
        });

        return $this->productRepository->getProducts($productIds)->getElements();
    }

    /**
     * @param float $productCurrencyFactor
     *
     * @return float
     * @throws InconsistentCriteriaIdsException
     */
    private function getEurosFactor(float $productCurrencyFactor): float
    {
        $euro = $this->currencyRepository->getEuroCurrency();
        $factor = $euro ? ($productCurrencyFactor / $euro->getFactor()) : 1;

        return round($factor, 2);
    }

    /**
     * Set create and update dates
     *
     * @param OrderEntity $sourceOrder
     * @param Order $order
     *
     * @throws \Exception
     */
    private function setDates(OrderEntity $sourceOrder, Order $order): void
    {
        $createdAt = $sourceOrder->getCreatedAt() ?: new \DateTime();
        $order->setCreatedAt(new \DateTime("@{$createdAt->getTimestamp()}"));
        $updatedAt = $sourceOrder->getUpdatedAt() ?: $createdAt;
        $order->setUpdatedAt(new \DateTime("@{$updatedAt->getTimestamp()}"));
    }

    /**
     * Set phone number from billing address
     *
     * @param OrderEntity $sourceOrder
     * @param Order $order
     */
    private function setFallbackPhone(OrderEntity $sourceOrder, Order $order): void
    {
        $billingAddressId = $sourceOrder->getBillingAddressId();
        $addresses = $sourceOrder->getAddresses();
        if ($addresses) {
            $billingAddress = $addresses->filter(function ($address) use ($billingAddressId) {
                /** @var OrderAddressEntity $address */
                return $address->getId() === $billingAddressId;
            })->first();

            $phone = $billingAddress ? (string)$billingAddress->getPhoneNumber() : '';
            $order->setTelephone($phone);
        }
    }
}
