<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service;

use Fgits\AutoInvoice\Service\CustomFields\OrderCustomFields;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Document\DocumentGenerator\InvoiceGenerator;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class ConditionChecker
{
    /** @var EntityRepositoryInterface $customerRepository */
    private $customerRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderTransactionRepository;

    /**
     * @var OrderCustomFields $orderCustomFields
     */
    private $orderCustomFields;

    /**
     * @var DB\Document $document
     */
    private $document;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * ConditionChecker constructor.
     *
     * @param EntityRepositoryInterface $customerRepository
     * @param EntityRepositoryInterface $orderTransactionRepository
     * @param OrderCustomFields $orderCustomFields
     * @param DB\Document $document
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $customerRepository,
        EntityRepositoryInterface $orderTransactionRepository,
        OrderCustomFields $orderCustomFields,
        DB\Document $document,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->customerRepository         = $customerRepository;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->orderCustomFields          = $orderCustomFields;
        $this->document                   = $document;
        $this->systemConfigService        = $systemConfigService;
        $this->logger                     = $logger;
    }

    /**
     * Determine whether to generate and send the invoice or not based on plugin settings and order state
     *
     * @param OrderEntity $order
     * @param bool $isCronjob
     *
     * @return bool
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function shouldSendInvoice(OrderEntity $order, bool $isCronjob)
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $order->getSalesChannelId());

        $invoiceDocumentExists = $this->document->orderHasDocument($order, InvoiceGenerator::INVOICE);

        $orderStatus = $order->getStateId();

        $transaction = $this->getTransaction($order);
        $paymentStatus = $transaction->getStateMachineState()->getId();
        $paymentMethodId = $transaction->getPaymentMethodId();

        if (isset($config['conditionPaymentStatus'])) {
            $configPaymentStatus = is_array($config['conditionPaymentStatus']) ? $config['conditionPaymentStatus'] : array($config['conditionPaymentStatus']);
        }

        if (isset($config['conditionOrderStatus'])) {
            $configOrderStatus = is_array($config['conditionOrderStatus']) ? $config['conditionOrderStatus'] : array($config['conditionOrderStatus']);
        }

        if (!($order instanceof OrderEntity)) {
            return false;
        }

        if ($order->getStateMachineState()->getTechnicalName() === OrderStates::STATE_CANCELLED) {
            return false;
        }

        if ($this->orderCustomFields->isInvoiceProcessed($order))
        {
            return false;
        }

        if ($invoiceDocumentExists && !$config['sendExistingInvoices']) {
            $this->orderCustomFields->processInvoice($order);

            return false;
        }

        if (isset($config['conditionExcludeCustomerGroup']) && is_array($config['conditionExcludeCustomerGroup']) && in_array($this->getCustomer($order->getOrderCustomer())->getGroupId(), $config['conditionExcludeCustomerGroup'])) {
            return false;
        }

        if (!$isCronjob && isset($config['conditionExcludePaymentMethodFromEvents']) && is_array($config['conditionExcludePaymentMethodFromEvents']) && in_array($paymentMethodId, $config['conditionExcludePaymentMethodFromEvents'])) {
            return false;
        }

        if (isset($config['conditionExcludePaymentMethod']) && is_array($config['conditionExcludePaymentMethod']) && in_array($paymentMethodId, $config['conditionExcludePaymentMethod'])) {
            return false;
        }

        if (isset($config['paymentMethodInvoice']) && is_array($config['paymentMethodInvoice']) && in_array($paymentMethodId, $config['paymentMethodInvoice'])) {
            return true;
        }

        if (!empty($configPaymentStatus) && !in_array($paymentStatus, $configPaymentStatus)) {
            return false;
        }

        if (!empty($configOrderStatus) && !in_array($orderStatus, $configOrderStatus)) {
            return false;
        }

        return true;
    }

    /**
     * Returns transaction.
     *
     * @param OrderEntity $order
     *
     * @return OrderTransactionEntity|null
     *
     * @throws InconsistentCriteriaIdsException
     */
    private function getTransaction(OrderEntity $order): ?OrderTransactionEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));

        return $this->orderTransactionRepository->search($criteria, $context)->first();
    }

    /**
     * Returns customer.
     *
     * @param OrderCustomerEntity $orderCustomer
     *
     * @return CustomerEntity
     *
     * @throws InconsistentCriteriaIdsException
     */
    private function getCustomer(OrderCustomerEntity $orderCustomer): CustomerEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $orderCustomer->getCustomerId()));

        return $this->customerRepository->search($criteria, $context)->first();
    }
}
