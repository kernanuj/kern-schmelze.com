<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Service\FgitsLibrary;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Content\MailTemplate\Service\MailService;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\DataBag;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 * @version 1.2.0
 */
class Mailer
{
    /**
     * @var MailService $mailService
     */
    private $mailService;

    /**
     * @var EntityRepositoryInterface $mailTemplateRepository
     */
    private $mailTemplateRepository;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Mailer constructor.
     *
     * @param MailService $mailService
     * @param EntityRepositoryInterface $mailTemplateRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        MailService $mailService,
        EntityRepositoryInterface $mailTemplateRepository,
        LoggerInterface $logger
    ) {
        $this->mailService            = $mailService;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->logger                 = $logger;
    }

    /**
     * @param OrderEntity $order
     * @param MailTemplateEntity $mailTemplate
     * @param Context $context
     * @param array $recipients
     * @param array $templateData
     * @param array $documents
     */
    public function sendEmail(
        OrderEntity $order,
        MailTemplateEntity $mailTemplate,
        Context $context,
        array $recipients,
        array $templateData = array(),
        array $documents = array()
    ) {
        $data = new DataBag();

        $data->set('recipients', $recipients);
        $data->set('senderName', $mailTemplate->getSenderName());
        $data->set('salesChannelId', $order->getSalesChannelId());

        $data->set('contentHtml', $mailTemplate->getContentHtml());
        $data->set('contentPlain', $mailTemplate->getContentPlain());
        $data->set('subject', $mailTemplate->getSubject());

        $data->set('binAttachments', $documents);

        $this->mailService->send(
            $data->all(),
            $context,
            array_merge([
                'order' => $order,
                'salesChannel' => $order->getSalesChannel()
            ], $templateData));
    }

    /**
     * @param OrderEntity $order
     * @param string $technicalName
     *
     * @return MailTemplateEntity|null
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getMailTemplate(OrderEntity $order, string $technicalName): ?MailTemplateEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('mailTemplateType.technicalName', $technicalName));
        $criteria->setLimit(1);

        if ($order->getSalesChannelId()) {
            $criteria->addFilter(
                new EqualsFilter('mail_template.salesChannels.salesChannel.id', $order->getSalesChannelId())
            );
        }

        /** @var MailTemplateEntity|null $mailTemplate */
        $mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first();

        return $mailTemplate;
    }
}
