<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Content\MailTemplate\Service\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateTypes;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Content\MailTemplate\Service\Event\OrderConfirmationSubscriber;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class OrderConfirmationSubscriberTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var OrderConfirmationSubscriber
     */
    private $orderConfirmationSubscriber;

    protected function setUp(): void
    {
        $this->orderConfirmationSubscriber = $this->getContainer()->get(OrderConfirmationSubscriber::class);
    }

    public function testItEarlyReturnsIfNotOrderConfirmationMail(): void
    {
        /**
         * @var MockObject|MailBeforeValidateEvent $event
         */
        $event = $this->getMockBuilder(MailBeforeValidateEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getData')
            ->willReturn(
                [
                    'templateId' => Uuid::randomHex(),
                ]
            );
        $event->expects(static::never())
            ->method('getTemplateData');

        $this->orderConfirmationSubscriber->__invoke($event);
    }

    public function testItEarlyReturnsIfNoOrderInTemplateData(): void
    {
        /**
         * @var MockObject|MailBeforeValidateEvent $event
         */
        $event = $this->getMockBuilder(MailBeforeValidateEvent::class)->disableOriginalConstructor()->getMock();
        $validConfirmationMailTemplateId = $this->getValidConfirmationMailTemplateId();
        static::assertNotNull($validConfirmationMailTemplateId);
        $event->method('getData')
            ->willReturn(
                [
                    'templateId' => $validConfirmationMailTemplateId,
                ]
            );
        $event->expects(static::once())
            ->method('getTemplateData')
            ->willReturn(
                [
                    'order' => null,
                ]
            );

        $this->orderConfirmationSubscriber->__invoke($event);
    }

    public function testItEarlyReturnsIfNoOrderLineItems(): void
    {
        /**
         * @var MockObject|MailBeforeValidateEvent $event
         */
        $event = $this->getMockBuilder(MailBeforeValidateEvent::class)->disableOriginalConstructor()->getMock();
        $validConfirmationMailTemplateId = $this->getValidConfirmationMailTemplateId();
        static::assertNotNull($validConfirmationMailTemplateId);
        $event->method('getData')
            ->willReturn(
                [
                    'templateId' => $validConfirmationMailTemplateId,
                ]
            );

        $order = $this->getMockBuilder(OrderEntity::class)->getMock();
        $order->expects(static::once())
            ->method('getLineItems')
            ->willReturn(null);
        $event->expects(static::once())
            ->method('getTemplateData')
            ->willReturn(
                [
                    'order' => $order,
                ]
            );

        $this->orderConfirmationSubscriber->__invoke($event);
    }

    public function testThatOtherPriceDefinitionLineItemsDontGetChanged(): void
    {
        $templateLineItemId = Uuid::randomHex();
        $customizedProductTemplateLineItem = new OrderLineItemEntity();
        $customizedProductTemplateLineItem->setId($templateLineItemId);
        $customizedProductTemplateLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductTemplateLineItem->setLabel('TestTemplate');
        $customizedProductOptionLineItem = new OrderLineItemEntity();
        $customizedProductOptionLineItem->setId(Uuid::randomHex());
        $customizedProductOptionLineItem->setParentId($templateLineItemId);
        $customizedProductOptionLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductOptionLineItem->setPriceDefinition(new AbsolutePriceDefinition(19.99, 2));
        $customizedProductOptionLineItem->setQuantity(5);
        $customizedProductOptionLineItem->setLabel('TestOption');
        $orderLineItems = new OrderLineItemCollection(
            [
                $customizedProductTemplateLineItem,
                $customizedProductOptionLineItem,
            ]
        );
        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(
            $orderLineItems
        );
        $event = new MailBeforeValidateEvent(
            [
                'templateId' => $this->getValidConfirmationMailTemplateId(),
            ],
            Context::createDefaultContext(),
            [
                'order' => $order,
            ]
        );
        $this->orderConfirmationSubscriber->__invoke($event);

        static::assertSame(5, $customizedProductOptionLineItem->getQuantity());
    }

    public function testThatPercentagePriceDefinitionLineItemsGetChanged(): void
    {
        $templateLineItemId = Uuid::randomHex();
        $customizedProductTemplateLineItem = new OrderLineItemEntity();
        $customizedProductTemplateLineItem->setId($templateLineItemId);
        $customizedProductTemplateLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductTemplateLineItem->setLabel('TestTemplate');
        $customizedProductOptionLineItem = new OrderLineItemEntity();
        $customizedProductOptionLineItem->setId(Uuid::randomHex());
        $customizedProductOptionLineItem->setParentId($templateLineItemId);
        $customizedProductOptionLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductOptionLineItem->setPriceDefinition(new PercentagePriceDefinition(10, 2));
        $customizedProductOptionLineItem->setQuantity(5);
        $customizedProductOptionLineItem->setLabel('TestOption');
        $orderLineItems = new OrderLineItemCollection(
            [
                $customizedProductTemplateLineItem,
                $customizedProductOptionLineItem,
            ]
        );
        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(
            $orderLineItems
        );
        $event = new MailBeforeValidateEvent(
            [
                'templateId' => $this->getValidConfirmationMailTemplateId(),
            ],
            Context::createDefaultContext(),
            [
                'order' => $order,
            ]
        );
        $this->orderConfirmationSubscriber->__invoke($event);

        static::assertSame(1, $customizedProductOptionLineItem->getQuantity());
    }

    private function getValidConfirmationMailTemplateId(): ?string
    {
        $mailTemplateRepository = $this->getContainer()->get('mail_template.repository');
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('mailTemplateType.technicalName', MailTemplateTypes::MAILTYPE_ORDER_CONFIRM)
        );

        return $mailTemplateRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }
}
