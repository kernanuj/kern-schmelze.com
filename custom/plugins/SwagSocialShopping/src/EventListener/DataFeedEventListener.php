<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\EventListener;

use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use SwagSocialShopping\Component\DataFeed\DataFeedHandler;
use SwagSocialShopping\SwagSocialShopping;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataFeedEventListener implements EventSubscriberInterface
{
    /**
     * @var DataFeedHandler
     */
    private $dataFeedHandler;

    public function __construct(DataFeedHandler $dataFeedHandler)
    {
        $this->dataFeedHandler = $dataFeedHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SwagSocialShopping::SOCIAL_SHOPPING_SALES_CHANNEL_WRITTEN_EVENT => 'afterWrite',
        ];
    }

    public function afterWrite(EntityWrittenEvent $event): void
    {
        foreach ($event->getWriteResults() as $writeResult) {
            if (!$this->needsDataFeed($writeResult)) {
                continue;
            }

            $this->dataFeedHandler->createDataFeedForWriteResult($writeResult, $event);
        }
    }

    private function needsDataFeed(EntityWriteResult $writeResult): bool
    {
        return $writeResult->getEntityName() === 'swag_social_shopping_sales_channel'
            && $writeResult->getOperation() !== EntityWriteResult::OPERATION_DELETE
            && (
                (
                    isset($writeResult->getPayload()['network'])
                    && \in_array($writeResult->getPayload()['network'], DataFeedHandler::RELEVANT_NETWORKS, true)
                )
                || !empty($writeResult->getPayload()['id'])
            );
    }
}
