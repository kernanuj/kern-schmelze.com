<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use Shopware\Core\Content\MailTemplate\Service\MailSenderInterface;
use Shopware\Core\Content\MailTemplate\Service\MessageFactoryInterface;

/**
 * Class LabelExporter
 * @package InvExportLabel\Service
 */
class LabelSender
{
    /**
     * @var MessageFactoryInterface
     */
    private $messageFactory;

    /**
     * @var MailSenderInterface
     */
    private $mailSender;

    /**
     * LabelExporter constructor.
     * @param MessageFactoryInterface $messageFactory
     * @param MailSenderInterface $mailSender
     */
    public function __construct(MessageFactoryInterface $messageFactory, MailSenderInterface $mailSender)
    {
        $this->messageFactory = $messageFactory;
        $this->mailSender = $mailSender;
    }

    /**
     * @param ExportRequestConfiguration $exportRequestConfiguration
     * @param ExportResult $exportResult
     */
    public function run(
        ExportRequestConfiguration $exportRequestConfiguration,
        ExportResult $exportResult
    ) {
        $recipients = [];
        foreach ($exportRequestConfiguration->getRecipientEmailAddresses() as $recipientAddress) {
            $recipients[$recipientAddress] = 'KernSchmelze.com Labels';
        }

        $body = [
            $exportRequestConfiguration->getRecipientEmailBody()
        ];

        $message = $this->messageFactory->createMessage(
            'KernSchmelze Labels ' . date('Y-m-d H:i:s'),
            ['labels@kern-schmelze.com' => 'Kernschmelze Labels'],
            $recipients,
            $body,
            [],
            []
        );

        // have to attach files manually since factory uses flysystem with public as root
        foreach ($exportResult->getCreatedFiles() as $file) {
            $attachment = new \Swift_Attachment(
                file_get_contents($file->getPathname()),
                basename($file->getPathname()),
                'application/pdf'
            );

            $message->attach($attachment);
        }

        $this->mailSender->send($message);
    }
}
