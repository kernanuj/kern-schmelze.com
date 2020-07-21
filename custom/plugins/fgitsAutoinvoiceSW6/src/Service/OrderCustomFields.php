<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 */
class OrderCustomFields
{
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
     * @param EntityRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(EntityRepositoryInterface $orderRepository, LoggerInterface $logger)
    {
        $this->orderRepository = $orderRepository;
        $this->logger          = $logger;
    }

    /**
     * Sets the given Order as processed by AutoInvoice plugin
     *
     * @param OrderEntity $order
     */
    public function processInvoice(OrderEntity $order): void
    {
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
}
