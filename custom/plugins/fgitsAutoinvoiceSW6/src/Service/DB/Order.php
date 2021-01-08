<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service\DB;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class Order
{
    /**
     * @var EntityRepositoryInterface $orderRepository
     */
    private $orderRepository;

    /**
     * @var Connection $connection
     */
    private $connection;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Order constructor.
     *
     * @param EntityRepositoryInterface $orderRepository
     * @param Connection $connection
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $orderRepository,
        Connection $connection,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->connection      = $connection;
        $this->logger          = $logger;
    }

    /**
     * @param string $orderId
     * @param Context $context
     *
     * @return OrderEntity
     */
    public function getOrderById(string $orderId, Context $context): OrderEntity
    {
        $criteria = new Criteria([$orderId]);

        return $this->orderRepository->search($criteria, $context)->get($orderId);
    }

    /**
     * @param Context $context
     *
     * @return OrderEntity
     */
    public function getLastOrder(Context $context): OrderEntity
    {
        $order = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('`order`')
            ->orderBy('order_number', 'DESC')
            ->setMaxResults(1)
            ->execute()
            ->fetch();

        return $this->getOrderById(Uuid::fromBytesToHex($order['id']), $context);
    }

    /**
     * @param SalesChannelEntity $salesChannel
     *
     * @return OrderCollection
     */
    public function getProcessedOrders(SalesChannelEntity $salesChannel): OrderCollection
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannel->getId()));
        $criteria->addFilter(new EqualsFilter('customFields.fgits_autoinvoice_processed', true));

        /** @var OrderCollection $orders */
        $orders = $this->orderRepository->search($criteria, $context)->getEntities();

        return $orders;
    }

    /**
     * @param SalesChannelEntity $salesChannel
     *
     * @return OrderCollection
     */
    public function getNotExportedOrders(SalesChannelEntity $salesChannel): OrderCollection
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannel->getId()));
        $criteria->addFilter(new EqualsFilter('customFields.fgits_autoinvoice_processed', true));
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new EqualsFilter('customFields.fgits_autoinvoice_exported', null),
                    new EqualsFilter('customFields.fgits_autoinvoice_exported', false)
                ]
            )
        );

        /** @var OrderCollection $orders */
        $orders = $this->orderRepository->search($criteria, $context)->getEntities();

        return $orders;
    }
}
