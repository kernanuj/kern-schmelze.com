<?php

namespace InvProductImageDocuments\Service;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

/**
 * Class DocumentService
 *
 * @author    Nils Harder <hallo@inventivo.de>
 * @copyright Copyright (c) 2020 Nils Harder
 * @package   InvProductImageDocuments\Service
 * @version   1
 */
class DocumentService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $mediaRepository;

    public function __construct(EntityRepositoryInterface $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    public function extendLineItems(Context $context, OrderLineItemCollection $lineItems)
    {
        foreach ($lineItems as $lineItem) {
            $coverId = $lineItem->getCoverId();
            if ($coverId !== null) {
                $cover = $this->mediaRepository->search(new Criteria([$coverId]), $context)->get($coverId);

                if ($cover) {
                    $lineItem->setCover($cover);
                }
            }
        }
    }
}
