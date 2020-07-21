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
 * @author Fabian Golle <fabian@golle-it.de>
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
     * @var Document $document
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
     * @param Document $document
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        NumberRangeValueGeneratorInterface $valueGenerator,
        DocumentService $documentService,
        Document $document,
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
     */
    public function createInvoice(OrderEntity $order)
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config');

        if ($this->document->orderHasDocument($order, InvoiceGenerator::INVOICE)) {
            return;
        }

        $documentNumber = $this->generateDocumentNumber($order, 'document_invoice');

        $documentConfig = [
            'custom' => [
                'invoiceNumber' => $documentNumber
            ],
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
     */
    public function createDeliveryNote(OrderEntity $order)
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config');

        if ($this->document->orderHasDocument($order, DeliveryNoteGenerator::DELIVERY_NOTE)) {
            return;
        }

        $documentNumber = $this->generateDocumentNumber($order, 'document_delivery_note');

        $documentConfig = [
            'custom' => [
                'deliveryNoteNumber' => $documentNumber
            ],
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
     * @return string
     *
     * @throws DocumentGenerationException
     * @throws InvalidDocumentGeneratorTypeException
     * @throws InvalidFileGeneratorTypeException
     */
    private function createDocument(OrderEntity $order, string $documentTypeName, array $documentConfig = [])
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
    private function generateDocumentNumber(OrderEntity $order, string $type)
    {
        $context = new Context(new SystemSource());

        return $this->valueGenerator->getValue($type, $context, $order->getSalesChannelId());
    }
}
