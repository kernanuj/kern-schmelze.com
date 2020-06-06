<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\Component\DataFeed;

use Shopware\Core\Content\ProductExport\Exception\SalesChannelDomainNotFoundException;
use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use SwagSocialShopping\Component\Network\Facebook;
use SwagSocialShopping\Component\Network\GoogleShopping;
use SwagSocialShopping\Component\Network\Instagram;
use SwagSocialShopping\Component\Network\NetworkRegistryInterface;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use SwagSocialShopping\Exception\SocialShoppingSalesChannelNotFoundException;

class DataFeedHandler
{
    public const RELEVANT_NETWORKS = [
        Facebook::class,
        Instagram::class,
        GoogleShopping::class,
    ];

    /**
     * @var EntityRepositoryInterface
     */
    private $productExportRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelDomainRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $socialShoppingSalesChannelRepository;

    /**
     * @var NetworkRegistryInterface
     */
    private $networkRegistry;

    public function __construct(
        EntityRepositoryInterface $productExportRepository,
        EntityRepositoryInterface $salesChannelDomainRepository,
        EntityRepositoryInterface $socialShoppingSalesChannelRepository,
        NetworkRegistryInterface $networkRegistry
    ) {
        $this->productExportRepository = $productExportRepository;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->socialShoppingSalesChannelRepository = $socialShoppingSalesChannelRepository;
        $this->networkRegistry = $networkRegistry;
    }

    public function createDataFeedForWriteResult(EntityWriteResult $writeResult, EntityWrittenEvent $event): void
    {
        $payload = $writeResult->getPayload();
        $primaryKey = $writeResult->getPrimaryKey();
        $primaryKey = \is_array($primaryKey)
            ? $primaryKey['id']
            : $primaryKey;

        $socialShoppingSalesChannel = $this->getSocialShoppingSalesChannel(
            $primaryKey,
            $event->getContext()
        );

        if (!($socialShoppingSalesChannel instanceof SocialShoppingSalesChannelEntity)) {
            throw new SocialShoppingSalesChannelNotFoundException($primaryKey);
        }

        $network = $payload['network'] ?? $socialShoppingSalesChannel->getNetwork();

        switch ($network) {
            case Facebook::class:
            case Instagram::class:
                $this->upsertFacebookDataFeed($payload, $event->getContext(), $socialShoppingSalesChannel);
                break;

            case GoogleShopping::class:
                $this->upsertGoogleShoppingDataFeed($payload, $event->getContext(), $socialShoppingSalesChannel);
                break;
        }
    }

    private function upsertGoogleShoppingDataFeed(
        array $socialShoppingPayload,
        Context $context,
        SocialShoppingSalesChannelEntity $socialShoppingSalesChannelEntity
    ): void {
        $dataFeedPayload = [
            'encoding' => ProductExportEntity::ENCODING_UTF8,
            'fileFormat' => ProductExportEntity::FILE_FORMAT_XML,
            'headerTemplate' => file_get_contents(__DIR__ . '/../../Resources/templates/google-shopping/header.xml'),
            'bodyTemplate' => file_get_contents(__DIR__ . '/../../Resources/templates/google-shopping/body.xml'),
            'footerTemplate' => file_get_contents(__DIR__ . '/../../Resources/templates/google-shopping/footer.xml'),
        ];

        $dataFeedPayload = array_merge(
            $dataFeedPayload,
            $this->mapPayloadToProductExport($socialShoppingPayload, $context, $socialShoppingSalesChannelEntity)
        );

        $this->upsertDataFeed($dataFeedPayload, $context);
    }

    private function upsertFacebookDataFeed(
        array $socialShoppingPayload,
        Context $context,
        SocialShoppingSalesChannelEntity $socialShoppingSalesChannelEntity
    ): void {
        $dataFeedPayload = [
            'encoding' => ProductExportEntity::ENCODING_UTF8,
            'fileFormat' => ProductExportEntity::FILE_FORMAT_XML,
            'headerTemplate' => file_get_contents(__DIR__ . '/../../Resources/templates/facebook/header.xml'),
            'bodyTemplate' => file_get_contents(__DIR__ . '/../../Resources/templates/facebook/body.xml'),
            'footerTemplate' => file_get_contents(__DIR__ . '/../../Resources/templates/facebook/footer.xml'),
        ];

        $dataFeedPayload = array_merge(
            $dataFeedPayload,
            $this->mapPayloadToProductExport($socialShoppingPayload, $context, $socialShoppingSalesChannelEntity)
        );

        $this->upsertDataFeed($dataFeedPayload, $context);
    }

    private function upsertDataFeed(array $payload, Context $context): void
    {
        $this->productExportRepository->upsert(
            [
                array_filter(
                    $payload,
                    static function ($item) {
                        return $item !== null;
                    }
                ),
            ],
            $context
        );
    }

    private function mapPayloadToProductExport(
        array $socialShoppingPayload,
        Context $context,
        SocialShoppingSalesChannelEntity $socialShoppingSalesChannelEntity
    ): array {
        $dataFeedPayload = [];

        if (isset($socialShoppingPayload['configuration'])) {
            $dataFeedPayload['includeVariants'] = $socialShoppingPayload['configuration']['includeVariants'];
            $dataFeedPayload['generateByCronjob'] = $socialShoppingPayload['configuration']['generateByCronjob'];
            $dataFeedPayload['interval'] = $socialShoppingPayload['configuration']['interval'];
        }

        if (!isset($socialShoppingPayload['salesChannelId'])) {
            $dataFeedPayload['id'] = $this->getProductExportId(
                $socialShoppingSalesChannelEntity->getSalesChannelId(),
                $context
            );
        }

        if (isset($socialShoppingPayload['salesChannelId'])) {
            $dataFeedPayload['id'] = $this->getProductExportId(
                $socialShoppingPayload['salesChannelId'],
                $context
            );
            $dataFeedPayload['salesChannelId'] = $socialShoppingPayload['salesChannelId'];
            $dataFeedPayload['fileName'] = sprintf(
                '%s_%s.xml',
                $this->networkRegistry->getNetworkByName($socialShoppingSalesChannelEntity->getNetwork())->getName(),
                $socialShoppingPayload['salesChannelId']
            );
        }

        if (isset($socialShoppingPayload['productStreamId'])) {
            $dataFeedPayload['productStreamId'] = $socialShoppingPayload['productStreamId'];
        }

        if (isset($socialShoppingPayload['currencyId'])) {
            $dataFeedPayload['currencyId'] = $socialShoppingPayload['currencyId'];
        }

        if (isset($socialShoppingPayload['salesChannelDomainId'])) {
            $dataFeedPayload['salesChannelDomainId'] = $socialShoppingPayload['salesChannelDomainId'];
            $dataFeedPayload['storefrontSalesChannelId'] = $this->getStoreFrontSalesChannelId(
                $socialShoppingPayload['salesChannelDomainId'],
                $context
            );
        }

        if (empty($dataFeedPayload['id'])) {
            $dataFeedPayload['accessKey'] = AccessKeyHelper::generateAccessKey('product-export');
        }

        return $dataFeedPayload;
    }

    private function getProductExportId(string $salesChannelId, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));

        return $this->productExportRepository->searchIds($criteria, $context)->firstId();
    }

    /**
     * @throws SalesChannelDomainNotFoundException
     */
    private function getStoreFrontSalesChannelId(string $salesChannelDomainId, Context $context): string
    {
        /** @var SalesChannelDomainEntity|null $salesChannelDomain */
        $salesChannelDomain = $this->salesChannelDomainRepository->search(
            new Criteria([$salesChannelDomainId]),
            $context
        )->get($salesChannelDomainId);

        if ($salesChannelDomain === null) {
            throw new SalesChannelDomainNotFoundException($salesChannelDomainId);
        }

        return $salesChannelDomain->getSalesChannelId();
    }

    private function getSocialShoppingSalesChannel(
        string $socialShoppingSalesChannelId,
        Context $context
    ): ?SocialShoppingSalesChannelEntity {
        return $this->socialShoppingSalesChannelRepository->search(
            new Criteria([$socialShoppingSalesChannelId]),
            $context
        )->get($socialShoppingSalesChannelId);
    }
}
