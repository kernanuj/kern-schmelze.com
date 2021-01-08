<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopware\Core\Checkout\Document\DocumentGenerator\DeliveryNoteGenerator;
use Shopware\Core\Checkout\Document\DocumentGenerator\InvoiceGenerator;
use Shopware\Core\Checkout\Document\DocumentService;
use Shopware\Core\Checkout\Document\Exception\DocumentGenerationException;
use Shopware\Core\Checkout\Document\Exception\InvalidDocumentGeneratorTypeException;
use Shopware\Core\Checkout\Document\Exception\InvalidFileGeneratorTypeException;
use Shopware\Core\Checkout\Document\FileGenerator\FileTypes;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class DocumentCreator
{
    /**
     * @var NumberRangeValueGeneratorInterface $valueGenerator
     */
    private $valueGenerator;

    /**
     * @var DocumentService $documentService
     */
    private $documentService;

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
     * DocumentCreator constructor.
     *
     * @param NumberRangeValueGeneratorInterface $valueGenerator
     * @param DocumentService $documentService
     * @param DB\Document $document
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        NumberRangeValueGeneratorInterface $valueGenerator,
        DocumentService $documentService,
        DB\Document $document,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->valueGenerator      = $valueGenerator;
        $this->documentService     = $documentService;
        $this->document            = $document;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
    }

    /**
     * Create invoice document
     *
     * @param OrderEntity $order
     *
     * @throws InconsistentCriteriaIdsException
     * @throws \Exception
     */
    public function createInvoice(OrderEntity $order): void
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $order->getSalesChannelId());

        if ($this->document->orderHasDocument($order, InvoiceGenerator::INVOICE)) {
            return;
        }

        $documentNumber = $this->generateDocumentNumber($order, 'document_invoice');

        $documentConfig = [
            'custom' => [
                'invoiceNumber' => $documentNumber
            ],
            'documentDate' => gmdate('Y-m-d\TH:i:s.v\Z', (new \DateTime())->getTimestamp()),
            'documentNumber' => $documentNumber
        ];

        try {
            $this->createDocument($order, InvoiceGenerator::INVOICE, $documentConfig);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to create invoice document for order %s', $order->getOrderNumber()), (array) $e);
        }
    }

    /**
     * Create delivery note document
     *
     * @param OrderEntity $order
     *
     * @throws InconsistentCriteriaIdsException
     * @throws \Exception
     */
    public function createDeliveryNote(OrderEntity $order): void
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $order->getSalesChannelId());

        if ($this->document->orderHasDocument($order, DeliveryNoteGenerator::DELIVERY_NOTE)) {
            return;
        }

        $documentNumber = $this->generateDocumentNumber($order, 'document_delivery_note');

        $documentConfig = [
            'custom' => [
                'deliveryNoteNumber' => $documentNumber
            ],
            'documentDate' => gmdate('Y-m-d\TH:i:s.v\Z', (new \DateTime())->getTimestamp()),
            'documentNumber' => $documentNumber
        ];

        try {
            $this->createDocument($order, DeliveryNoteGenerator::DELIVERY_NOTE, $documentConfig);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to create delivery note document for order %s', $order->getOrderNumber()), (array) $e);
        }
    }

    /**
     * Creates a document based on the parameters provided.
     *
     * @param OrderEntity $order
     * @param string $documentTypeName
     * @param array $documentConfig
     *
     * @return string
     *
     * @throws DocumentGenerationException
     * @throws InvalidDocumentGeneratorTypeException
     * @throws InvalidFileGeneratorTypeException
     */
    private function createDocument(OrderEntity $order, string $documentTypeName, array $documentConfig = []): string
    {
        $context = new Context(new SystemSource());

        $config = DocumentConfigurationFactory::createConfiguration($documentConfig);

        $documentIdStruct = $this->documentService->create(
            $order->getId(),
            $documentTypeName,
            FileTypes::PDF,
            $config,
            $context
        );

        return $documentIdStruct->getId();
    }

    /**
     * @param OrderEntity $order
     * @param string $type
     *
     * @return string
     */
    private function generateDocumentNumber(OrderEntity $order, string $type): string
    {
        $context = new Context(new SystemSource());

        return $this->valueGenerator->getValue($type, $context, $order->getSalesChannelId());
    }
}
