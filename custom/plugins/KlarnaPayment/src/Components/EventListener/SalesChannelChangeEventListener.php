<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use KlarnaPayment\Components\ButtonKeyHandler\ButtonKeyHandlerInterface;
use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Components\Exception\ButtonKeyCreationFailed;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelChangeEventListener implements EventSubscriberInterface
{
    /** @var ButtonKeyHandlerInterface */
    private $buttonKeyHandler;

    /** @var ConfigReaderInterface */
    private $configReader;

    /** @var Connection */
    private $connection;

    public function __construct(
        ButtonKeyHandlerInterface $buttonKeyHandler,
        ConfigReaderInterface $configReader,
        Connection $connection
    ) {
        $this->buttonKeyHandler = $buttonKeyHandler;
        $this->configReader     = $configReader;
        $this->connection       = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityWrittenContainerEvent::class => 'handleEntityWrittenEvents',
        ];
    }

    public function handleEntityWrittenEvents(EntityWrittenContainerEvent $containerEvent): void
    {
        $this->handleSalesChannelChanges($containerEvent);
        $this->handleSalesChannelDomainChanges($containerEvent);
    }

    private function handleSalesChannelChanges(EntityWrittenContainerEvent $containerEvent): void
    {
        $event = $containerEvent->getEventByEntityName(SalesChannelDefinition::ENTITY_NAME);

        if ($event === null) {
            return;
        }

        foreach ($event->getIds() as $key) {
            $this->buttonKeyHandler->deleteButtonKeysBySalesChannelId($key, $event->getContext());

            if (!$this->isInstantShoppingEnabled($key)) {
                continue;
            }

            if (!($event instanceof EntityDeletedEvent)) {
                try {
                    $this->buttonKeyHandler->createButtonKeysBySalesChannelId($key, $event->getContext());
                } catch (ButtonKeyCreationFailed $e) {
                    // no-op, error is logged
                }
            }
        }
    }

    private function handleSalesChannelDomainChanges(EntityWrittenContainerEvent $containerEvent): void
    {
        $event = $containerEvent->getEventByEntityName(SalesChannelDomainDefinition::ENTITY_NAME);

        if ($event === null) {
            return;
        }

        foreach ($event->getIds() as $key) {
            $this->buttonKeyHandler->deleteButtonKeysBySalesChannelDomainId($key, $event->getContext());

            if (!$this->isInstantShoppingEnabled($this->getSalesChannelFromDomain($key))) {
                continue;
            }

            if (!($event instanceof EntityDeletedEvent)) {
                try {
                    $this->buttonKeyHandler->createButtonKeysBySalesChannelDomainId($key, $event->getContext());
                } catch (ButtonKeyCreationFailed $e) {
                    // no-op, error is logged
                }
            }
        }
    }

    private function getSalesChannelFromDomain(string $salesChannelDomain): string
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('LOWER(HEX(domain.sales_channel_id))')
            ->from('sales_channel_domain', 'domain')
            ->where('domain.id = :domain')
            ->setParameter(':domain', Uuid::fromHexToBytes($salesChannelDomain));

        /** @var ResultStatement $result */
        $result = $queryBuilder->execute();

        return $result->fetch(FetchMode::COLUMN);
    }

    private function isInstantShoppingEnabled(string $salesChannel): bool
    {
        $configuration = $this->configReader->read($salesChannel);

        if (!$configuration->get('instantShoppingEnabled')) {
            return false;
        }

        return true;
    }
}
