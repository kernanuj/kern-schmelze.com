<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service\CustomFields;

use Fgits\AutoInvoice\Service\DB\Document;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class OrderCustomFields
{
    /**
     * @var Document $document
     */
    private $document;

    /**
     * @var EntityRepositoryInterface $orderRepository
     */
    private $orderRepository;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * OrderCustomFields constructor.
     *
     * @param Document $document
     * @param EntityRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Document $document,
        EntityRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->document        = $document;
        $this->orderRepository = $orderRepository;
        $this->logger          = $logger;
    }

    /**
     * Sets fgits_autoinvoice_exported
     *
     * @param OrderEntity $order
     * @param bool|null $exported
     */
    public function setExported(OrderEntity $order, ?bool $exported = true): void
    {
        $this->orderRepository->upsert([[
            'id' => $order->getId(),
            'customFields' => [
                'fgits_autoinvoice_exported' => $exported
            ]
        ]], new Context(new SystemSource()));
    }

    /**
     * Sets $order as processed by AutoInvoice plugin.
     *
     * @param OrderEntity $order
     * @param array|null $documents
     */
    public function processInvoice(OrderEntity $order, ?array $documents = []): void
    {
        foreach ($documents as $document) {
            $this->document->setSent(array($document['id']));
        }

        $this->orderRepository->upsert([[
            'id' => $order->getId(),
            'customFields' => [
                'fgits_autoinvoice_processed' => true,
                'fgits_autoinvoice_processed_date' => date('Y-m-d H:i:s')
            ]
        ]], new Context(new SystemSource()));
    }

    /**
     * Checks if the invoice is already processed.
     *
     * @param OrderEntity $order
     *
     * @return bool
     */
    public function isInvoiceProcessed(OrderEntity $order): bool
    {
        $customFields = $order->getCustomFields();

        if (isset($customFields['fgits_autoinvoice_processed'])) {
            return $customFields['fgits_autoinvoice_processed'];
        }
        else {
            return false;
        }
    }

    /**
     * Sets fgits_autoinvoice_order_confirmation_sent
     *
     * @param OrderEntity $order
     * @param bool|null $sent
     */
    public function setOrderConfirmationSent(OrderEntity $order, ?bool $sent = true): void
    {
        $this->orderRepository->upsert([[
            'id' => $order->getId(),
            'customFields' => [
                'fgits_autoinvoice_order_confirmation_sent' => $sent
            ]
        ]], new Context(new SystemSource()));
    }

    /**
     * Checks if the order confirmation email is already sent.
     *
     * @param OrderEntity $order
     *
     * @return bool
     */
    public function isOrderConfirmationSent(OrderEntity $order): bool
    {
        $customFields = $order->getCustomFields();

        if (isset($customFields['fgits_autoinvoice_order_confirmation_sent'])) {
            return $customFields['fgits_autoinvoice_order_confirmation_sent'];
        }
        else {
            return false;
        }
    }
}
