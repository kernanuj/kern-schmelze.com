<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Content\MailTemplate\Service\Event;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateTypes;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;

class OrderConfirmationSubscriber
{
    /**
     * @var EntityRepositoryInterface
     */
    private $mailTemplateRepository;

    public function __construct(EntityRepositoryInterface $mailTemplateRepository)
    {
        $this->mailTemplateRepository = $mailTemplateRepository;
    }

    public function __invoke(MailBeforeValidateEvent $event): void
    {
        $data = $event->getData();
        if ($data['templateId'] !== $this->getOrderConfirmationMailTemplateId($event->getContext())) {
            return;
        }

        $order = $event->getTemplateData()['order'];
        if ($order === null || !$order instanceof OrderEntity) {
            return;
        }

        $orderLineItemCollection = $order->getLineItems();
        if ($orderLineItemCollection === null) {
            return;
        }

        $templateLineItems = $orderLineItemCollection->filterByType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        foreach ($templateLineItems as $templateLineItem) {
            $childLineItems = $orderLineItemCollection->filterByProperty('parentId', $templateLineItem->getId());
            $templateLabel = $templateLineItem->getLabel();

            $productLineItems = $childLineItems->filterByType(LineItem::PRODUCT_LINE_ITEM_TYPE);
            $customizedProductOptionLineItems = $childLineItems->filterByType(
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
            );
            $customizedProductOptionValueLineItems = $childLineItems->filterByType(
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
            );

            $this->adjustPercentageQuantity($orderLineItemCollection, $customizedProductOptionLineItems);
            $this->adjustPercentageQuantity($orderLineItemCollection, $customizedProductOptionValueLineItems);

            // ToDo@SEG Remove with PT-11847
            $this->addTemplateNameToChildLabel($productLineItems, $templateLabel);
            $this->addTemplateNameToChildLabel($customizedProductOptionLineItems, $templateLabel);
            $this->addTemplateNameToChildLabel($customizedProductOptionValueLineItems, $templateLabel);
        }

        // ToDo@SEG Remove with PT-11847
        $orderLineItemCollection = $orderLineItemCollection->filter(static function (OrderLineItemEntity $lineItem) {
            return $lineItem->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE;
        });
        $order->setLineItems($orderLineItemCollection);
    }

    private function getOrderConfirmationMailTemplateId(Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('mailTemplateType.technicalName', MailTemplateTypes::MAILTYPE_ORDER_CONFIRM)
        );

        return $this->mailTemplateRepository->searchIds($criteria, $context)->firstId();
    }

    private function adjustPercentageQuantity(
        OrderLineItemCollection $originalLineItems,
        OrderLineItemCollection $orderLineItemCollection
    ): void {
        foreach ($orderLineItemCollection as $orderLineItemEntity) {
            if (!$orderLineItemEntity->getPriceDefinition() instanceof PercentagePriceDefinition) {
                continue;
            }

            $lineItem = $originalLineItems->get($orderLineItemEntity->getId());
            if ($lineItem === null) {
                continue;
            }

            $lineItem->setQuantity(1);
        }
    }

    private function addTemplateNameToChildLabel(
        OrderLineItemCollection $orderLineItemCollection,
        string $templateLabel
    ): void {
        foreach ($orderLineItemCollection as $orderLineItemEntity) {
            $orderLineItemEntity->setLabel(\sprintf('%s (%s)', $orderLineItemEntity->getLabel(), $templateLabel));
        }
    }
}
