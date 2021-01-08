<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Subscriber;

use Fgits\AutoInvoice\Service\ConditionChecker;
use Fgits\AutoInvoice\Service\CustomFields\OrderCustomFields;
use Fgits\AutoInvoice\Service\DB\Order;
use Fgits\AutoInvoice\Service\Document;
use Fgits\AutoInvoice\Service\DocumentCreator;
use Fgits\AutoInvoice\Service\FgitsLibrary\Mailer as FgitsMailer;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\MailTemplate\MailTemplateTypes;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeSentEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class MailSubscriber implements EventSubscriberInterface
{
    /**
     * @var Order $order
     */
    private $order;

    /**
     * @var ConditionChecker $conditionChecker
     */
    private $conditionChecker;

    /**
     * @var Document $document
     */
    private $document;

    /**
     * @var DocumentCreator $documentCreator
     */
    private $documentCreator;

    /**
     * @var FgitsMailer $mailer
     */
    private $mailer;

    /**
     * @var OrderCustomFields $orderCustomFields
     */
    private $orderCustomFields;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * MailSubscriber constructor.
     *
     * @param Order $order
     * @param ConditionChecker $conditionChecker
     * @param Document $document
     * @param DocumentCreator $documentCreator
     * @param FgitsMailer $mailer
     * @param OrderCustomFields $orderCustomFields
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Order $order,
        ConditionChecker $conditionChecker,
        Document $document,
        DocumentCreator $documentCreator,
        FgitsMailer $mailer,
        OrderCustomFields $orderCustomFields,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->order               = $order;
        $this->conditionChecker    = $conditionChecker;
        $this->document            = $document;
        $this->documentCreator     = $documentCreator;
        $this->mailer              = $mailer;
        $this->orderCustomFields   = $orderCustomFields;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return[
            MailBeforeSentEvent::class => 'onAfterCreateMessage'
        ];
    }

    public function onAfterCreateMessage(MailBeforeSentEvent $event)
    {
        $data = $event->getData();

        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $data['salesChannelId']);

        if (!empty($config['sendCustomerEmail']) && !empty($config['attachOrderEmail']) && isset($data['templateId'])) {
            $mailTemplate = $this->mailer->getMailTemplateById($data['templateId']);
            $mailTemplateType = $mailTemplate->getMailTemplateType();

            if ($mailTemplateType->getTechnicalName() == MailTemplateTypes::MAILTYPE_ORDER_CONFIRM) {
                $order = $this->order->getLastOrder($event->getContext());

                if ($this->conditionChecker->shouldSendInvoice($order, false)) {
                    $this->documentCreator->createInvoice($order);

                    $invoice = $this->document->getInvoice($order);

                    $attachment = new \Swift_Attachment(
                        $invoice['content'],
                        $invoice['fileName'],
                        $invoice['mimeType']
                    );

                    $event->getMessage()->attach($attachment);

                    $this->orderCustomFields->processInvoice($order, array($invoice));
                }

                $this->orderCustomFields->setOrderConfirmationSent($order);
            }
        }
    }
}
