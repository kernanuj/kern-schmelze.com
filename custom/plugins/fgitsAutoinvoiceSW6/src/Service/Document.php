<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\DocumentGenerator\DeliveryNoteGenerator;
use Shopware\Core\Checkout\Document\DocumentGenerator\InvoiceGenerator;
use Shopware\Core\Checkout\Document\DocumentService;
use Shopware\Core\Checkout\Document\Exception\InvalidDocumentException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 */
class Document
{
    /**
     * @var EntityRepository $documentRepository
     */
    private $documentRepository;

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
     * @param EntityRepository $documentRepository
     * @param DocumentService $documentService
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepository $documentRepository,
        DocumentService $documentService,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->documentRepository  = $documentRepository;
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
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config');

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
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config');

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

        $documentEntity = $this->getDocumentEntity($order, $technicalName);

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
     * @param OrderEntity $order
     * @param string $technicalName
     *
     * @return DocumentEntity|null
     *
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidDocumentException
     */
    private function getDocumentEntity(OrderEntity $order, string $technicalName): ?DocumentEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $criteria->addAssociation('documentMediaFile');
        $criteria->addAssociation('documentType');
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $criteria->addFilter(new EqualsFilter('document.documentType.technicalName', $technicalName));
        $criteria->setLimit(1);

        /** @var DocumentEntity|null $documentEntity */
        $documentEntity = $this->documentRepository->search($criteria, $context)->first();

        if ($documentEntity === null) {
            throw new InvalidDocumentException($technicalName);
        }

        return $documentEntity;
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
        $documentEntity = $this->getDocumentEntity($order, $technicalName);
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

    /**
     * Checks if the given Order has existing document(s)
     *
     * @param OrderEntity $order
     * @param string $technicalName
     *
     * @return bool
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function orderHasDocument(OrderEntity $order, string $technicalName)
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $criteria->addFilter(new EqualsFilter('document.documentType.technicalName', $technicalName));
        $criteria->setLimit(1);

        return (bool) $this->documentRepository->search($criteria, $context)->getEntities()->count();
    }
}
