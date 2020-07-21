<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Service;

use Fgits\AutoInvoice\fgitsAutoinvoiceSW6;
use Fgits\AutoInvoice\Service\FgitsLibrary\Mailer as FgitsMailer;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\Exception\SalesChannelNotFoundException;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Salutation\SalutationEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 */
class OrderProcessor
{
    const ADMIN_MODE_SEND_BOTH               = 'send_both';
    const ADMIN_MODE_SEND_INVOICE_ONLY       = 'send_invoice_only';
    const ADMIN_MODE_SEND_DELIVERY_NOTE_ONLY = 'send_delivery_note_only';
    const ADMIN_MODE_SEND_NONE               = 'send_none';

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
     * @var EntityRepositoryInterface $salutationRepository
     */
    private $salutationRepository;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * OrderProcessor constructor.
     *
     * @param ConditionChecker $conditionChecker
     * @param Document $document
     * @param DocumentCreator $documentCreator
     * @param FgitsMailer $mailer
     * @param OrderCustomFields $orderCustomFields
     * @param EntityRepositoryInterface $salutationRepository
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConditionChecker $conditionChecker,
        Document $document,
        DocumentCreator $documentCreator,
        FgitsMailer $mailer,
        OrderCustomFields $orderCustomFields,
        EntityRepositoryInterface $salutationRepository,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->conditionChecker       = $conditionChecker;
        $this->document               = $document;
        $this->documentCreator        = $documentCreator;
        $this->mailer                 = $mailer;
        $this->orderCustomFields      = $orderCustomFields;
        $this->salutationRepository   = $salutationRepository;
        $this->systemConfigService    = $systemConfigService;
        $this->logger                 = $logger;
    }

    /**
     * Process an Order based on plugin configuration
     *
     * @param OrderEntity $order
     * @param bool $isFirstPersister
     * @param bool $isCronjob
     *
     * @throws InconsistentCriteriaIdsException
     * @throws SalesChannelNotFoundException
     */
    public function processOrder(OrderEntity $order, $isFirstPersister = false, $isCronjob = false)
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config');

        if (!$this->conditionChecker->shouldSendInvoice($order, $isCronjob)) {
            if ($isFirstPersister && $config['sendDeliveryNoteOnOrderCreation']) {
                $this->forceCreationAndSendingOfDeliveryNote($order);
            }

            return;
        }

        if ($config['sendCustomerEmail']) {
            $this->documentCreator->createInvoice($order);

            $this->sendCustomerEmail($order);
        } elseif ($config['alwaysCreateDocuments']) {
            $this->documentCreator->createInvoice($order);
        }

        if (in_array($config['adminEmailType'], array(self::ADMIN_MODE_SEND_DELIVERY_NOTE_ONLY, self::ADMIN_MODE_SEND_BOTH))) {
            $this->documentCreator->createDeliveryNote($order);
        }

        if ($config['adminEmailType'] !== self::ADMIN_MODE_SEND_NONE) {
            $this->sendAdminEmail($order);
        }
    }

    /**
     * Force creation and sending of order documents
     *
     * @param OrderEntity $order
     *
     * @throws SalesChannelNotFoundException
     * @throws InconsistentCriteriaIdsException
     */
    public function forceCreationAndSendingOfDeliveryNote(OrderEntity $order)
    {
        $this->documentCreator->createDeliveryNote($order);
        $this->sendAdminEmail($order, false);
    }

    /**
     * Send invoice to customer
     *
     * @param OrderEntity $order
     *
     * @throws SalesChannelNotFoundException
     * @throws InconsistentCriteriaIdsException
     */
    public function sendCustomerEmail(OrderEntity $order)
    {
        $context = new Context(new SystemSource());

        try {
            $documents[] = $this->document->getInvoice($order);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Invoice document for order %s not found. Cannot send customer email.', $order->getOrderNumber()));

            return;
        }

        try {
            $mailTemplate = $this->mailer->getMailTemplate($order, fgitsAutoinvoiceSW6::MAIL_TEMPLATE_CUSTOMER);
        } catch (InconsistentCriteriaIdsException $e) {
            $this->logger->error(sprintf('No email template for order %s found. Cannot send customer email.', $order->getOrderNumber()));

            return;
        }

        $customer = $order->getOrderCustomer();

        if ($customer === null) {
            $this->logger->error(sprintf('No customer for order %s found. Cannot send customer email.', $order->getOrderNumber()));

            return;
        }

        $customer->setSalutation($this->getSalutation($customer->getSalutationId()));

        $recipients = [
            $customer->getEmail() => $customer->getFirstName() . ' ' . $customer->getLastName()
        ];

        $this->mailer->sendEmail($order, $mailTemplate, $context, $recipients, [], $documents);

        $this->orderCustomFields->processInvoice($order);
    }

    /**
     * Send invoice and delivery note to admin
     *
     * @param OrderEntity $order
     * @param bool $attachInvoice
     *
     * @throws SalesChannelNotFoundException
     */
    private function sendAdminEmail(OrderEntity $order, $attachInvoice = true)
    {
        $context = new Context(new SystemSource());

        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config');

        if (!isset($config['adminEmail']) || empty(trim($config['adminEmail']))) {
            $this->logger->error('Plugin is configured to send email to admin, but no admin email is specified.');

            return;
        }

        $documents = array();

        try {
            if ($attachInvoice && in_array($config['adminEmailType'], array(self::ADMIN_MODE_SEND_INVOICE_ONLY, self::ADMIN_MODE_SEND_BOTH)))
            {
                $documents[] = $this->document->getInvoice($order);
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Invoice document for order %s not found. Cannot send admin email.', $order->getOrderNumber()));

            return;
        }

        try {
            if (in_array($config['adminEmailType'], array(self::ADMIN_MODE_SEND_DELIVERY_NOTE_ONLY, self::ADMIN_MODE_SEND_BOTH)))
            {
                $documents[] = $this->document->getDeliveryNote($order);
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Delivery note document for order %s not found. Cannot send admin email.', $order->getOrderNumber()));

            return;
        }

        try {
            $mailTemplate = $this->mailer->getMailTemplate($order, fgitsAutoinvoiceSW6::MAIL_TEMPLATE_ADMIN);
        } catch (InconsistentCriteriaIdsException $e) {
            $this->logger->error(sprintf('No email template for order %s found. Cannot send admin email.', $order->getOrderNumber()));

            return;
        }

        $recipients = array();

        foreach (explode(',', $config['adminEmail']) as $email) {
            $recipients = [
                trim($email) => ''
            ];
        }

        $this->mailer->sendEmail($order, $mailTemplate, $context, $recipients, [], $documents);

        $this->orderCustomFields->processInvoice($order);
    }

    /**
     * @param $salutationId
     *
     * @return SalutationEntity|null
     *
     * @throws InconsistentCriteriaIdsException
     */
    private function getSalutation($salutationId): ?SalutationEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $salutationId));
        $criteria->setLimit(1);

        /** @var SalutationEntity|null $salutation */
        $salutation = $this->salutationRepository->search($criteria, $context)->first();

        return $salutation;
    }
}
