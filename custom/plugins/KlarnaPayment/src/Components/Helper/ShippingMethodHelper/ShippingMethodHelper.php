<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\ShippingMethodHelper;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ShippingMethodHelper implements ShippingMethodHelperInterface
{
    /** @var SalesChannelRepositoryInterface */
    private $shippingMethodRepository;

    public function __construct(SalesChannelRepositoryInterface $shippingMethodRepository)
    {
        $this->shippingMethodRepository = $shippingMethodRepository;
    }

    public function shippingMethodIdExists(?string $shippingMethodId, SalesChannelContext $context): bool
    {
        if (!is_string($shippingMethodId) || !Uuid::isValid($shippingMethodId)) {
            return false;
        }

        $criteria = (new Criteria([$shippingMethodId]))->addFilter(new EqualsFilter('active', true));

        $shippingMethodIds = $this->shippingMethodRepository->searchIds($criteria, $context);

        return $shippingMethodIds->getTotal() > 0;
    }
}
