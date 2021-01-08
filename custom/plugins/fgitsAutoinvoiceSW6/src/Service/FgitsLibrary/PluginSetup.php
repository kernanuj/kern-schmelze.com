<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Service\FgitsLibrary;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 * @version 1.3.2
 */
class PluginSetup
{
    /**
     * @var SalesChannel $salesChannel
     */
    private $salesChannel;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * PluginSetup constructor.
     *
     * @param SalesChannel $salesChannel
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(SalesChannel $salesChannel, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->salesChannel = $salesChannel;
        $this->container    = $container;
        $this->logger       = $logger;
    }

    /**
     * @param string $pointer
     * @param array $options
     * @param bool|null $reset
     */
    public function initSystemConfig(string $pointer, array $options, ?bool $reset = false): void
    {
        $params = [
            'pointer' => $pointer,
            'reset'   => $reset
        ];

        array_walk($options, function ($value, $key, $params) {
            $this->setSystemConfigOption($key, $value, $params);
        }, $params);
    }

    /**
     * @param string $key
     * @param $value
     * @param array $params
     */
    private function setSystemConfigOption(string $key, $value, array $params): void
    {
        /** @var SystemConfigService $systemConfigService */
        $systemConfigService = $this->container->get('Shopware\Core\System\SystemConfig\SystemConfigService');

        foreach ($this->salesChannel->fetchAll() as $salesChannelId => $salesChannel) {
            $config = $systemConfigService->get($params['pointer'], $salesChannelId);

            if (!isset($config[$key]) || $params['reset']) {
                $systemConfigService->set($params['pointer'] . '.' . $key, $value, $salesChannelId);
            }
        }

        $config = $systemConfigService->get($params['pointer']);

        if (!isset($config[$key]) || $params['reset']) {
            $systemConfigService->set($params['pointer'] . '.' . $key, $value);
        }
    }

    /**
     * @param string $pointer
     * @param array $options
     */
    public function destroySystemConfig(string $pointer, array $options): void
    {
        array_walk($options, function ($key, $placeholder, $pointer) {
            $this->unsetSystemConfigOption($key, $pointer);
        }, $pointer);
    }

    /**
     * @param string $key
     * @param string $pointer
     */
    private function unsetSystemConfigOption(string $key, string $pointer): void
    {
        /** @var SystemConfigService $systemConfigService */
        $systemConfigService = $this->container->get('Shopware\Core\System\SystemConfig\SystemConfigService');

        foreach ($this->salesChannel->fetchAll() as $salesChannelId => $salesChannel) {
            $systemConfigService->delete($pointer . '.' . $key, $salesChannelId);
        }

        $systemConfigService->delete($pointer . '.' . $key);
    }

    /**
     * @param string $name
     * @param array $customFields
     */
    public function createCustomFieldSet(string $name, array $customFields): void
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $customFieldSetRepository->create([[
            'name' => $name,
            'customFields' => $customFields
        ]], new Context(new SystemSource()));
    }

    /**
     * @param Connection $connection
     * @param array $data
     *
     * @return string
     *
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    public function createMailTemplateType(Connection $connection, array $data): string
    {
        $mailTemplateTypeId = Uuid::randomHex();

        $connection->insert('mail_template_type', [
            'id' => Uuid::fromHexToBytes($mailTemplateTypeId),
            'technical_name' => $data['technicalName'],
            'available_entities' => json_encode($data['availableEntities']),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
        ]);

        $this->createMailTemplateTypeTranslations($connection, $mailTemplateTypeId, $data);

        return $mailTemplateTypeId;
    }

    private function createMailTemplateTypeTranslations(Connection $connection, string $mailTemplateTypeId, array $data): void
    {
        foreach ($this->getLocales() as $locale) {
            try {
                $connection->insert('mail_template_type_translation', [
                    'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
                    'language_id' => Uuid::fromHexToBytes($this->getLanguageIdByLocale($locale)),
                    'name' => $data[$locale]['name'],
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
                ]);
            } catch (Exception $e) {
                $this->logger->notice('[#fgits] fgitsAutoinvoiceSW6: ' . __CLASS__ . '::' . __FUNCTION__ . '(): ' . $e->getMessage());
            }
        }
    }

    /**
     * @param Connection $connection
     * @param string $mailTemplateTypeId
     * @param array $data
     *
     * @return void
     *
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    public function createMailTemplate(Connection $connection, string $mailTemplateTypeId, array $data): void
    {
        $mailTemplateId = Uuid::randomHex();

        $connection->insert('mail_template', [
            'id' => Uuid::fromHexToBytes($mailTemplateId),
            'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
            'system_default' => true,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
        ]);

        $this->createMailTemplateTranslations($connection, $mailTemplateId, $data);

        $this->addMailTemplateToSalesChannels($connection, $mailTemplateTypeId, $mailTemplateId);
    }

    /**
     * @param Connection $connection
     * @param string $mailTemplateId
     * @param array $data
     *
     * @throws DBALException
     */
    private function createMailTemplateTranslations(Connection $connection, string $mailTemplateId, array $data): void
    {
        foreach ($this->getLocales() as $locale) {
            try {
                $connection->insert('mail_template_translation', [
                    'mail_template_id' => Uuid::fromHexToBytes($mailTemplateId),
                    'language_id' => Uuid::fromHexToBytes($this->getLanguageIdByLocale($locale)),
                    'sender_name' => $data[$locale]['senderName'],
                    'subject' => $data[$locale]['subject'],
                    'description' => $data[$locale]['description'],
                    'content_html' => $data[$locale]['contentHtml'],
                    'content_plain' => $data[$locale]['contentPlain'],
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
                ]);
            } catch (Exception $e) {
                $this->logger->notice('[#fgits] fgitsAutoinvoiceSW6: ' . __CLASS__ . '::' . __FUNCTION__ . '(): ' . $e->getMessage());
            }
        }
    }

    /**
     * @param Connection $connection
     * @param string $mailTemplateTypeId
     * @param string $mailTemplateId
     *
     * @throws DBALException
     * @throws InvalidUuidException
     */
    private function addMailTemplateToSalesChannels(Connection $connection, string $mailTemplateTypeId, string $mailTemplateId): void
    {
        $salesChannels = $connection->fetchAll('SELECT `id` FROM `sales_channel`');

        foreach ($salesChannels as $salesChannel) {
            $mailTemplateSalesChannel = [
                'id' => Uuid::randomBytes(),
                'mail_template_id' => Uuid::fromHexToBytes($mailTemplateId),
                'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
                'sales_channel_id' => $salesChannel['id'],
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ];

            $connection->insert('mail_template_sales_channel', $mailTemplateSalesChannel);
        }
    }

    /**
     * @param string $locale
     *
     * @return string
     *
     * @throws InconsistentCriteriaIdsException
     * @throws Exception
     */
    private function getLanguageIdByLocale(string $locale): string
    {
        $context = new Context(new SystemSource());

        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        $criteria = new Criteria();
        $criteria->addAssociation('locale');
        $criteria->addFilter(new EqualsFilter('locale.code', $locale));

        /** @var LanguageEntity $languageEntity */
        $languageEntity = $languageRepository->search($criteria, $context)->first();

        if ($languageEntity === null) {
            throw new Exception($locale);
        }

        return $languageEntity->getId();
    }

    /**
     * @param string $pluginPath
     * @param string $locale
     * @param string $prefix
     * @param string $type
     *
     * @return string
     */
    public function getMailContent(string $pluginPath, string $locale, string $prefix, string $type): string
    {
        $path = $pluginPath . '/Resources/email/' . $locale . '/';

        switch ($type) {
            case 'html':
                $ext = 'html';
                break;
            case 'plain':
                $ext = 'txt';
                break;
            default:
                $ext = 'txt';
        }

        $file = $path . $prefix . '-' . $type . '.' . $ext;

        if (!is_file($file)) {
            throw new FileNotFoundException($file);
        }

        return file_get_contents($file);
    }

    /**
     * @param Context $context
     * @param string $name
     */
    public function deleteCustomFieldSet(Context $context, string $name): void
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));

        /** @var CustomFieldSetEntity $customFieldSetEntity */
        $customFieldSetEntity = $customFieldSetRepository->search($criteria, $context)->first();

        if (!is_null($customFieldSetEntity)) {
            $customFieldSetRepository->delete([[
                'id' => $customFieldSetEntity->getId()
            ]], $context);
        }
    }

    /**
     * @param Context $context
     * @param string $technicalName
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function deleteMailTemplate(Context $context, string $technicalName): void
    {
        try {
            $mailTemplateTypeId = $this->getMailTemplateTypeId($context, $technicalName);
        } catch (Exception $e) {
            return;
        }

        try {
            $mailTemplateId = $this->getMailTemplateId($context, $mailTemplateTypeId);
        } catch (Exception $e) {
            return;
        }

        /** @var EntityRepository $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        $mailTemplateRepository->delete([[
            'id' => $mailTemplateId
        ]], $context);
    }

    /**
     * @param Context $context
     * @param string $mailTemplateTypeId
     *
     * @return string
     *
     * @throws InconsistentCriteriaIdsException
     * @throws Exception
     */
    private function getMailTemplateId(Context $context, string $mailTemplateTypeId): string
    {
        /** @var EntityRepository $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('mailTemplateTypeId', $mailTemplateTypeId));

        /** @var MailTemplateEntity $mailTemplateEntity */
        $mailTemplateEntity = $mailTemplateRepository->search($criteria, $context)->first();

        if (is_null($mailTemplateEntity)) {
            throw new Exception('[#fgits] fgitsAutoinvoiceSW6: ' . __CLASS__ . '::' . __FUNCTION__ . '()');
        }

        return $mailTemplateEntity->getId();
    }

    /**
     * @param Context $context
     * @param string $technicalName
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function deleteMailTemplateType(Context $context, string $technicalName): void
    {
        try {
            $mailTemplateTypeId = $this->getMailTemplateTypeId($context, $technicalName);
        } catch (Exception $e) {
            return;
        }

        /** @var EntityRepository $mailTemplateTypeRepository */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        $mailTemplateTypeRepository->delete([[
            'id' => $mailTemplateTypeId
        ]], $context);
    }

    /**
     * @param Context $context
     * @param string $technicalName
     *
     * @return string
     *
     * @throws InconsistentCriteriaIdsException
     * @throws Exception
     */
    private function getMailTemplateTypeId(Context $context, string $technicalName): string
    {
        /** @var EntityRepository $mailTemplateTypeRepository */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));

        /** @var MailTemplateTypeEntity $mailTemplateTypeEntity */
        $mailTemplateTypeEntity = $mailTemplateTypeRepository->search($criteria, $context)->first();

        if (is_null($mailTemplateTypeEntity)) {
            throw new Exception('[#fgits] fgitsAutoinvoiceSW6: ' . __CLASS__ . '::' . __FUNCTION__ . '()');
        }

        return $mailTemplateTypeEntity->getId();
    }

    /**
     * @return array
     */
    private function getLocales(): array
    {
        return ['en-GB', 'de-DE'];
    }
}
