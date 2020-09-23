<?php declare(strict_types=1);

namespace TrustedShops\ScheduledTask;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\Context;

class ProductReviewsTaskHandler extends ScheduledTaskHandler
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $productReviewsApiUrl = 'https://cdn1.api.trustedshops.com/shops/{{tsId}}/products/public/v1/feed.json';

    public function __construct(EntityRepositoryInterface $scheduledTaskRepository, EntityRepositoryInterface $productRepository, SystemConfigService $systemConfigService)
    {
        $this->scheduledTaskRepository = $scheduledTaskRepository;
        $this->productRepository = $productRepository;
        $this->systemConfigService = $systemConfigService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ ProductReviewsTask::class ];
    }

    public function run(): void
    {
        $domain = 'TrustedShops.config.';
        $tsId = $this->systemConfigService->get( $domain . 'tsId' );

        if( $tsId ) {

            $apiUrl = str_replace( '{{tsId}}', $tsId, $this->productReviewsApiUrl );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Accept: application/json' ] );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            $result = curl_exec($ch);
            curl_close($ch);

            if( $result ) {
                $shopReviews = json_decode( $result );

                if( $shopReviews && $shopReviews->response && $shopReviews->response->data->shop->products ) {

                    foreach ($shopReviews->response->data->shop->products as $product) {

                        $productId = $this->productRepository->searchIds(
                            (new Criteria())->addFilter(new EqualsFilter('productNumber', $product->sku)),
                            Context::createDefaultContext()
                        )->getIds()[0];

                        if ($productId) {

                            $this->productRepository->update(
                                [
                                    [
                                        'id' => $productId,
                                        'trustedshopsRatings' => [
                                            [
                                                'id' => $productId,
                                                'overallMark' => $product->qualityIndicators->reviewIndicator->overallMark
                                            ]
                                        ]
                                    ]
                                ],
                                Context::createDefaultContext()
                            );
                        }
                    }
                }
            }
        }
    }

}