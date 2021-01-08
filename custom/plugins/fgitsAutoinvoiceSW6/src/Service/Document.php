<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Document\DocumentGenerator\DeliveryNoteGenerator;
use Shopware\Core\Checkout\Document\DocumentGenerator\InvoiceGenerator;
use Shopware\Core\Checkout\Document\DocumentService;
use Shopware\Core\Checkout\Document\Exception\InvalidDocumentException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class Document
{
    /**
     * @var DB\Document $document
     */
    private $document;

    /**
     * @var DocumentService $documentService
     */
    private $documentService;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Document constructor.
     *
     * @param DB\Document $document
     * @param DocumentService $documentService
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        DB\Document $document,
        DocumentService $documentService,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->document            = $document;
        $this->documentService     = $documentService;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
    }

    /**
     * @param OrderEntity $order
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidDocumentException
     */
    public function getInvoice(OrderEntity $order): array
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $order->getSalesChannelId());

        return $this->getDocument(
            $order,
            InvoiceGenerator::INVOICE,
            $this->buildFilename(
                $config['filenameInvoice'],
                $this->buildVariablesContext($order, InvoiceGenerator::INVOICE)
            )
        );
    }

    /**
     * @param OrderEntity $order
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidDocumentException
     */
    public function getDeliveryNote(OrderEntity $order): array
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $order->getSalesChannelId());

        return $this->getDocument(
            $order,
            DeliveryNoteGenerator::DELIVERY_NOTE,
            $this->buildFilename(
                $config['filenameDeliveryNote'],
                $this->buildVariablesContext($order, DeliveryNoteGenerator::DELIVERY_NOTE)
            )
        );
    }

    /**
     * @param OrderEntity $order
     * @param string $technicalName
     * @param string $fileName
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidDocumentException
     */
    private function getDocument(OrderEntity $order, string $technicalName, string $fileName): array
    {
        $context = new Context(new SystemSource());

        $documentEntity = $this->document->getDocumentEntity($order, $technicalName);

        $document = $this->documentService->getDocument($documentEntity, $context);

        return [
            'id'            => $documentEntity->getId(),
            'deepLinkCode'  => $documentEntity->getDeepLinkCode(),
            'technicalName' => $technicalName,
            'content'       => $document->getFileBlob(),
            'fileName'      => $fileName,
            'mimeType'      => $document->getContentType()
        ];
    }

    /**
     * Build context variables array for use in document filename templates
     *
     * @param OrderEntity $order
     * @param string $technicalName
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidDocumentException
     */
    private function buildVariablesContext(OrderEntity $order, string $technicalName): array
    {
        $documentEntity = $this->document->getDocumentEntity($order, $technicalName);
        $documentConfig = $documentEntity->getConfig();

        return array(
            'orderNumber'      => $order->getOrderNumber(),
            'orderDay'         => $order->getOrderDate()->format('d.m.Y'),
            'orderTime'        => $order->getOrderDateTime()->format('H:i'),
            'documentNumber'   => $documentConfig['documentNumber'],
            'documentPrefix'   => $documentConfig['filenamePrefix'],
            'documentName'     => $documentConfig['filenamePrefix'] . $documentConfig['documentNumber'],
            'documentDate'     => $documentEntity->getCreatedAt()->format('d.m.Y'),
            'customerEmail'    => $order->getOrderCustomer()->getEmail(),
            'documentAmount'   => number_format($order->getAmountTotal(), 2, ',', '.'),
            'fullname'         => $order->getOrderCustomer()->getFirstName() . ' ' . $order->getOrderCustomer()->getLastName(),
            'firstname'        => $order->getOrderCustomer()->getFirstName(),
            'lastname'         => $order->getOrderCustomer()->getLastName()
        );
    }

    /**
     * Compile the filename based on a template and given variables
     *
     * @param string $template
     * @param array $variables
     *
     * @return string
     */
    private function buildFilename(string $template, array $variables): string
    {
        $filename = str_replace(' ', '', $template);

        if (empty($filename))
        {
            $filename = '{$documentId}';
        }

        foreach ($variables as $key => $value) {
            $pattern = '/{\$' . $key . '}/';

            if (preg_match($pattern, $filename)) {
                $filename = preg_replace($pattern, $value, $filename);
            }
        }

        if (substr($filename, -4, 4) != '.pdf') {
            $filename .= '.pdf';
        }

        return $filename;
    }
}
