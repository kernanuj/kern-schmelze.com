<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\ButtonKeyHandler;

use KlarnaPayment\Components\Client\ClientInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\CreateButtonKey\CreateButtonKeyRequestHydratorInterface;
use KlarnaPayment\Components\Client\Response\GenericResponse;
use KlarnaPayment\Components\DataAbstractionLayer\Entity\ButtonKey\ButtonKeyEntity;
use KlarnaPayment\Components\Exception\ButtonKeyCreationFailed;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ButtonKeyHandler implements ButtonKeyHandlerInterface
{
    /** @var CreateButtonKeyRequestHydratorInterface */
    private $requestHydrator;

    /** @var EntityRepositoryInterface */
    private $buttonKeyRepository;

    /** @var EntityRepositoryInterface */
    private $salesChannelDomainRepository;

    /** @var ClientInterface */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    /** @var SystemConfigService */
    private $systemConfigService;

    public function __construct(
        CreateButtonKeyRequestHydratorInterface $requestHydrator,
        EntityRepositoryInterface $buttonKeyRepository,
        EntityRepositoryInterface $salesChannelDomainRepository,
        ClientInterface $client,
        LoggerInterface $logger,
        SystemConfigService $systemConfigService
    ) {
        $this->requestHydrator              = $requestHydrator;
        $this->buttonKeyRepository          = $buttonKeyRepository;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->client                       = $client;
        $this->logger                       = $logger;
        $this->systemConfigService          = $systemConfigService;
    }

    public function getButtonKey(string $salesChannelDomainId, Context $context): ?ButtonKeyEntity
    {
        $buttonKeyCriteria = new Criteria();

        $buttonKeyCriteria->addFilter(new EqualsFilter('salesChannelDomainId', $salesChannelDomainId));

        $buttonKeySearchResult = $this->buttonKeyRepository->search($buttonKeyCriteria, $context);

        if ($buttonKeySearchResult->count() >= 1) {
            return $buttonKeySearchResult->first();
        }

        return null;
    }

    public function getOrCreateButtonKey(string $salesChannelDomainId, Context $context): ?ButtonKeyEntity
    {
        $buttonKey = $this->getButtonKey($salesChannelDomainId, $context);

        if ($buttonKey) {
            return $buttonKey;
        }

        $this->createButtonKey($salesChannelDomainId, $context);

        return $this->getButtonKey($salesChannelDomainId, $context);
    }

    public function createButtonKey(string $salesChannelDomainId, Context $context): void
    {
        $this->deleteButtonKey($salesChannelDomainId, $context);

        $response = $this->getButtonKeyFromKlarna($salesChannelDomainId, $context);

        $responseData = $response->getResponse();

        if ($response->getHttpStatus() !== 201 || empty($responseData['button_key'])) {
            $this->logger->warning('could not create instant shopping button key', $responseData);

            throw new ButtonKeyCreationFailed($responseData['error_message'] ?? '', $responseData['error_code'] ?? 'plugin_unknown_error');
        }

        $data = [
            'id'                   => Uuid::randomHex(),
            'buttonKey'            => $responseData['button_key'],
            'salesChannelDomainId' => $salesChannelDomainId,
        ];

        $this->buttonKeyRepository->create([$data], $context);
    }

    public function deleteButtonKey(string $salesChannelDomainId, Context $context): void
    {
        $buttonKey = $this->getButtonKey($salesChannelDomainId, $context);

        if ($buttonKey === null) {
            return;
        }

        $this->buttonKeyRepository->delete([['id' => $buttonKey->getId()]], $context);
    }

    public function deleteButtonKeysBySalesChannelId(string $salesChannelId, Context $context): void
    {
        $salesChannelDomainCriteria = new Criteria();
        $salesChannelDomainCriteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));

        $salesChannelDomains = $this->salesChannelDomainRepository->search($salesChannelDomainCriteria, $context);

        /** @var SalesChannelDomainEntity $salesChannelDomain */
        foreach ($salesChannelDomains as $salesChannelDomain) {
            $this->deleteButtonKey($salesChannelDomain->getId(), $context);
        }
    }

    public function createButtonKeysBySalesChannelId(string $salesChannelId, Context $context): void
    {
        $salesChannelDomainCriteria = new Criteria();
        $salesChannelDomainCriteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));

        $salesChannelDomains = $this->salesChannelDomainRepository->search($salesChannelDomainCriteria, $context);

        /** @var SalesChannelDomainEntity $salesChannelDomain */
        foreach ($salesChannelDomains as $salesChannelDomain) {
            $this->createButtonKey($salesChannelDomain->getId(), $context);
        }
    }

    public function deleteButtonKeysBySalesChannelDomainId(string $salesChannelDomainId, Context $context): void
    {
        $salesChannelDomainCriteria = new Criteria([$salesChannelDomainId]);

        $salesChannelDomains = $this->salesChannelDomainRepository->search($salesChannelDomainCriteria, $context);

        foreach ($salesChannelDomains as $salesChannelDomain) {
            $this->deleteButtonKey($salesChannelDomain->getId(), $context);
        }
    }

    public function createButtonKeysBySalesChannelDomainId(string $salesChannelDomainId, Context $context): void
    {
        $salesChannelDomainCriteria = new Criteria([$salesChannelDomainId]);

        /** @var SalesChannelDomainCollection $salesChannelDomains */
        $salesChannelDomains = $this->salesChannelDomainRepository->search($salesChannelDomainCriteria, $context);

        foreach ($salesChannelDomains->getElements() as $salesChannelDomain) {
            if (!$this->systemConfigService->get('KlarnaPayment.settings.instantShoppingEnabled', $salesChannelDomain->getSalesChannelId())) {
                $this->deleteButtonKeysBySalesChannelDomainId($salesChannelDomain->getId(), $context);

                continue;
            }

            $this->createButtonKey($salesChannelDomain->getId(), $context);
        }
    }

    public function createButtonKeysForAllDomains(Context $context): void
    {
        /** @var SalesChannelDomainEntity $salesChannelDomain */
        foreach ($this->salesChannelDomainRepository->search(new Criteria(), $context)->getElements() as $salesChannelDomain) {
            $this->createButtonKeysBySalesChannelDomainId($salesChannelDomain->getId(), $context);
        }
    }

    private function getButtonKeyFromKlarna(string $salesChannelDomainId, Context $context): GenericResponse
    {
        $criteria = new Criteria([$salesChannelDomainId]);
        $criteria->addAssociation('salesChannel');

        /** @var null|SalesChannelDomainEntity $entity */
        $entity = $this->salesChannelDomainRepository->search($criteria, $context)->first();

        if (!$entity) {
            return (new GenericResponse())->assign([
                'status' => 400,
            ]);
        }

        $request = $this->requestHydrator->hydrate($entity, $context);

        return $this->client->request($request, $context);
    }
}
