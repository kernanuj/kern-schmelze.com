<?php

namespace Sendcloud\Shipping\Service\Utility;

use Doctrine\DBAL\DBALException;
use Sendcloud\Shipping\Entity\Config\ConfigEntityRepository;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;
use Shopware\Core\System\SystemConfig\SystemConfigEntity;

/**
 * Class InstallHandler
 *
 * @package Sendcloud\Shipping\Service\Utility
 */
class ShippingMethodHandler
{
    public const SERVICE_POINT_SYSTEM_CONFIG_KEY = 'SendcloudSendCloudShipping.servicePointId';

    /**
     * @var EntityRepositoryInterface
     */
    private $shippingMethodRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $rulesRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $deliveryTimeRepository;
    /**
     * @var ConfigEntityRepository
     */
    private $configRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $systemConfigRepository;

    /**
     * ShippingMethodHandler constructor.
     *
     * @param EntityRepositoryInterface $shippingMethodRepository
     * @param EntityRepositoryInterface $rulesRepository
     * @param EntityRepositoryInterface $deliveryTimeRepository
     * @param ConfigEntityRepository $configRepository
     * @param EntityRepositoryInterface $systemConfigRepository
     */
    public function __construct(
        EntityRepositoryInterface $shippingMethodRepository,
        EntityRepositoryInterface $rulesRepository,
        EntityRepositoryInterface $deliveryTimeRepository,
        ConfigEntityRepository $configRepository,
        EntityRepositoryInterface $systemConfigRepository
    ) {
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->rulesRepository = $rulesRepository;
        $this->deliveryTimeRepository = $deliveryTimeRepository;
        $this->configRepository = $configRepository;
        $this->systemConfigRepository = $systemConfigRepository;
    }

    /**
     * Creates Service Point Shipping Method
     *
     * @throws InconsistentCriteriaIdsException
     * @throws DBALException
     */
    public function addServicePointShippingMethod(): void
    {
        $createNewMethod = true;
        $existingServicePointSystemConfig = $this->getExistingServicePointSystemConfig();
        if ($existingServicePointSystemConfig) {
            $shippingMethodId = $existingServicePointSystemConfig->getConfigurationValue();
            $servicePointDeliveryShippingMethod = $this->shippingMethodRepository
                ->search(new Criteria([$shippingMethodId]), Context::createDefaultContext())
                ->first();
            if ($servicePointDeliveryShippingMethod) {
                $this->saveServicePointMethodId($shippingMethodId);
                $createNewMethod = false;
            } else {
                $this->deleteServicePointSystemConfig($existingServicePointSystemConfig->getId());
            }
        }

        if ($createNewMethod) {
            $this->createServicePointDeliveryShippingMethod();
        }
    }

    /**
     * Saves service point shipping method id to configs table
     *
     * @param string $id
     *
     * @throws DBALException
     */
    private function saveServicePointMethodId(string $id): void
    {
        $this->configRepository->updateValue('SENDCLOUD_SERVICE_POINT_DELIVERY_METHOD_ID', $id);
    }

    /**
     * Creates payload for shipping method
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     */
    private function createDataPayload(): array
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        /** @var RuleEntity $ruleEntity */
        $ruleEntity = $this->rulesRepository->search($criteria, $context)->first();
        $ruleId = $ruleEntity->getId();

        $criteria->addFilter();
        /** @var DeliveryTimeEntity $deliveryTimeEntity */
        $deliveryTimeEntity = $this->deliveryTimeRepository->search($criteria, $context)->first();
        $deliveryTimeEntityId = $deliveryTimeEntity->getId();

        return [
            'translations' => $this->getServicePointTranslations(),
            'active' => false,
            'availabilityRuleId' => $ruleId,
            'deliveryTimeId' => $deliveryTimeEntityId,
        ];
    }

    /**
     * Return service point configuration from system config table
     *
     * @return SystemConfigEntity|null
     * @throws InconsistentCriteriaIdsException
     */
    private function getExistingServicePointSystemConfig(): ?SystemConfigEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('configurationKey', self::SERVICE_POINT_SYSTEM_CONFIG_KEY));

        return $this->systemConfigRepository->search($criteria, Context::createDefaultContext())->first();
    }

    /**
     * Creates Service Point Delivery shipping method
     *
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     */
    public function createServicePointDeliveryShippingMethod(): void
    {
        $data = $this->createDataPayload();
        /** @var EntityWrittenEvent $writtenEvent */
        $writtenEvent = $this->shippingMethodRepository
            ->create([$data], Context::createDefaultContext())->getEventByEntityName('shipping_method');
        $writtenIds = $writtenEvent->getIds();
        $id = reset($writtenIds);
        $this->saveServicePointToSystemConfig($id);
        $this->saveServicePointMethodId($id);
    }

    /**
     * Returns service point translations.
     *
     * @return string[]
     */
    private function getServicePointTranslations(): array
    {
        return [
            'en-GB' => [
                'name' => 'Service Point Delivery',
                'description' => 'Please select a service point.'
            ],
            'de-DE' => [
                'name' => 'Service Point Zustellung',
                'description' => 'Bitte Service Point auswählen.'
            ],
            'nl-NL' => [
                'name' => 'Afhaalpuntlevering',
                'description' => 'Kies een afhaalpunt.'
            ],
            'es-ES' => [
                'name' => 'Punto de servicio de entrega',
                'description' => 'Por favor, selecciona un punto de servicio.'
            ],
            'fr-FR' => [
                'name' => 'Livraison en Point Relais',
                'description' => 'Selectionnez un point relais.'
            ]
        ];
    }

    /**
     * Saves service point id to system_config table
     *
     * @param string $shippingMethodId
     */
    private function saveServicePointToSystemConfig(string $shippingMethodId): void
    {
        $data = [
            'configurationKey' => self::SERVICE_POINT_SYSTEM_CONFIG_KEY,
            'configurationValue' => $shippingMethodId,
        ];

        $this->systemConfigRepository->create([$data], Context::createDefaultContext());

    }

    /**
     * Removes System configuration with given id
     *
     * @param string $systemConfig
     */
    public function deleteServicePointSystemConfig(string $systemConfig): void
    {
        $this->systemConfigRepository->delete([['id' => $systemConfig]], Context::createDefaultContext());
    }
}
