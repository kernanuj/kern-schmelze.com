<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Service\FgitsLibrary;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Content\MailTemplate\Service\MailServiceInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\DataBag;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 * @version 1.4.0
 */
class Mailer
{
    /**
     * @var MailServiceInterface $mailService
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
     * @param MailServiceInterface $mailService
     * @param EntityRepositoryInterface $mailTemplateRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        MailServiceInterface $mailService,
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
     *
     * @return \Swift_Message|null
     */
    public function sendEmail(
        OrderEntity $order,
        MailTemplateEntity $mailTemplate,
        Context $context,
        array $recipients,
        array $templateData = array(),
        array $documents = array()
    ): ?\Swift_Message {
        $data = new DataBag();

        $data->set('recipients', $recipients);
        $data->set('senderName', $mailTemplate->getTranslation('senderName'));
        $data->set('salesChannelId', $order->getSalesChannelId());

        $data->set('templateId', $mailTemplate->getId());
        $data->set('customFields', $mailTemplate->getCustomFields());
        $data->set('contentHtml', $mailTemplate->getTranslation('contentHtml'));
        $data->set('contentPlain', $mailTemplate->getTranslation('contentPlain'));
        $data->set('subject', $mailTemplate->getTranslation('subject'));
        $data->set('mediaIds', []);

        $data->set('binAttachments', $documents);

        return $this->mailService->send(
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
     */
    public function getMailTemplate(OrderEntity $order, string $technicalName): ?MailTemplateEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('mailTemplateType.technicalName', $technicalName));
        $criteria->setLimit(1);

        $criteriaNoSalesChannel = clone $criteria;

        if ($order->getSalesChannelId()) {
            $criteria->addFilter(
                new EqualsFilter('mail_template.salesChannels.salesChannel.id', $order->getSalesChannelId())
            );
        }

        /** @var MailTemplateEntity|null $mailTemplate */
        if (!($mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first())) {
            $mailTemplate = $this->mailTemplateRepository->search($criteriaNoSalesChannel, $context)->first();
        }

        return $mailTemplate;
    }

    /**
     * @param string $templateId
     *
     * @return MailTemplateEntity|null
     */
    public function getMailTemplateById(string $templateId): ?MailTemplateEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria([$templateId]);
        $criteria->addAssociation('mailTemplateType');

        /** @var MailTemplateEntity|null $mailTemplate */
        $mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first();

        return $mailTemplate;
    }
}
