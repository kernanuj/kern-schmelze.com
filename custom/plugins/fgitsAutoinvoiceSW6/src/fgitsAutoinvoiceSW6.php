<?php declare(strict_types=1);

namespace Fgits\AutoInvoice;

use Doctrine\DBAL\Connection;
use Fgits\AutoInvoice\Service\FgitsLibrary\PluginSetup;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 */
class fgitsAutoinvoiceSW6 extends Plugin
{
    const CUSTOM_FIELD_SET_NAME  = 'fgits_autoinvoice';
    const MAIL_TEMPLATE_CUSTOMER = 'fgits_autoinvoice_customer';
    const MAIL_TEMPLATE_ADMIN    = 'fgits_autoinvoice_admin';

    /**
     * @var PluginSetup $setup
     */
    private $setup;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function update(UpdateContext $context): void
    {
        // your code you need to execute while your plugin gets updated
    }

    public function activate(ActivateContext $context): void
    {
        $customFields = [
            ['name' => self::CUSTOM_FIELD_SET_NAME . '_cron_date', 'type' => CustomFieldTypes::DATETIME],
            ['name' => self::CUSTOM_FIELD_SET_NAME . '_processed', 'type' => CustomFieldTypes::BOOL],
            ['name' => self::CUSTOM_FIELD_SET_NAME . '_processed_date', 'type' => CustomFieldTypes::DATETIME]
        ];

        $this->setup->createCustomFieldSet(self::CUSTOM_FIELD_SET_NAME, $customFields);

        /** @var Connection $connection */
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        try {
            $templateTypeData['technicalName'] = self::MAIL_TEMPLATE_CUSTOMER;
            $templateTypeData['availableEntities'] = [
                'order' => 'order',
                'previousState' => 'state_machine_state',
                'newState' => 'state_machine_state',
                'salesChannel' => 'sales_channel'
            ];
            $templateTypeData['enName'] = 'AutoInvoice Customer E-Mail';
            $templateTypeData['deName'] = 'AutoInvoice Kunden E-Mail';

            $mailTemplateTypeId = $this->setup->createMailTemplateType($connection, $templateTypeData);

            $templateData['en-GB']['senderName']   = '{{ salesChannel.name }}';
            $templateData['en-GB']['subject']      = 'Your invoice for Order {{ order.orderNumber }}';
            $templateData['en-GB']['description']  = '';
            $templateData['en-GB']['contentHtml']  = $this->setup->getMailContent($this->getPath(), 'en-GB', 'customer', 'html');
            $templateData['en-GB']['contentPlain'] = $this->setup->getMailContent($this->getPath(), 'en-GB', 'customer', 'plain');

            $templateData['de-DE']['senderName']   = '{{ salesChannel.name }}';
            $templateData['de-DE']['subject']      = 'Ihre Rechnung zur Bestellung {{ order.orderNumber }}';
            $templateData['de-DE']['description']  = '';
            $templateData['de-DE']['contentHtml']  = $this->setup->getMailContent($this->getPath(), 'de-DE', 'customer', 'html');
            $templateData['de-DE']['contentPlain'] = $this->setup->getMailContent($this->getPath(), 'de-DE', 'customer', 'plain');

            $this->setup->createMailTemplate($connection, $mailTemplateTypeId, $templateData);
        } catch (\Exception $e) {
        }

        try {
            $templateTypeData['technicalName'] = self::MAIL_TEMPLATE_ADMIN;
            $templateTypeData['availableEntities'] = [
                'order' => 'order',
                'previousState' => 'state_machine_state',
                'newState' => 'state_machine_state',
                'salesChannel' => 'sales_channel'
            ];
            $templateTypeData['enName'] = 'AutoInvoice Admin E-Mail';
            $templateTypeData['deName'] = 'AutoInvoice Admin E-Mail';

            $mailTemplateTypeId = $this->setup->createMailTemplateType($connection, $templateTypeData);

            $templateData['en-GB']['senderName']   = '{{ salesChannel.name }}';
            $templateData['en-GB']['subject']      = 'Invoice / Delivery note for Order {{ order.orderNumber }}';
            $templateData['en-GB']['description']  = '';
            $templateData['en-GB']['contentHtml']  = $this->setup->getMailContent($this->getPath(), 'en-GB', 'admin', 'html');
            $templateData['en-GB']['contentPlain'] = $this->setup->getMailContent($this->getPath(), 'en-GB', 'admin', 'plain');

            $templateData['de-DE']['senderName']   = '{{ salesChannel.name }}';
            $templateData['de-DE']['subject']      = 'Rechnung / Lieferschein zur Bestellung {{ order.orderNumber }}';
            $templateData['de-DE']['description']  = '';
            $templateData['de-DE']['contentHtml']  = $this->setup->getMailContent($this->getPath(), 'de-DE', 'admin', 'html');
            $templateData['de-DE']['contentPlain'] = $this->setup->getMailContent($this->getPath(), 'de-DE', 'admin', 'plain');

            $this->setup->createMailTemplate($connection, $mailTemplateTypeId, $templateData);
        } catch (\Exception $e) {
        }
    }

    public function deactivate(DeactivateContext $context): void
    {
        $context = new Context(new SystemSource());

        try {
            $this->setup->deleteCustomFieldSet($context, self::CUSTOM_FIELD_SET_NAME);
        } catch (InconsistentCriteriaIdsException $e) {
        }

        try {
            $this->setup->deleteMailTemplate($context, self::MAIL_TEMPLATE_CUSTOMER);

            $this->setup->deleteMailTemplateType($context, self::MAIL_TEMPLATE_CUSTOMER);
        } catch (\Exception $e) {
        }

        try {
            $this->setup->deleteMailTemplate($context, self::MAIL_TEMPLATE_ADMIN);

            $this->setup->deleteMailTemplateType($context, self::MAIL_TEMPLATE_ADMIN);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param PluginSetup $setup
     *
     * @required
     */
    public function setPluginSetup(PluginSetup $setup)
    {
        $this->setup = $setup;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @required
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
