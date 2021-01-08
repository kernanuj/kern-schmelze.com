<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service\DB;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\Exception\InvalidDocumentException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class Document
{
    /**
     * @var EntityRepositoryInterface $documentRepository
     */
    private $documentRepository;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Product constructor.
     *
     * @param EntityRepositoryInterface $documentRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $documentRepository,
        LoggerInterface $logger
    ) {
        $this->documentRepository = $documentRepository;
        $this->logger             = $logger;
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
    public function getDocumentEntity(OrderEntity $order, string $technicalName): ?DocumentEntity
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
     * Checks if the given Order has existing document(s)
     *
     * @param OrderEntity $order
     * @param string $technicalName
     *
     * @return bool
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function orderHasDocument(OrderEntity $order, string $technicalName): bool
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $criteria->addFilter(new EqualsFilter('document.documentType.technicalName', $technicalName));
        $criteria->setLimit(1);

        return (bool) $this->documentRepository->search($criteria, $context)->getEntities()->count();
    }

    /**
     * Sets documents as sent.
     *
     * @param array $documentIds
     */
    public function setSent(array $documentIds): void
    {
        $context = new Context(new SystemSource());

        $writes = array_map(static function ($id) {
            return ['id' => $id, 'sent' => true];
        }, $documentIds);

        if (!empty($writes)) {
            $this->documentRepository->update($writes, $context);
        }
    }
}
