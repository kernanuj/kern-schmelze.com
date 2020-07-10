<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\CurrencyHelper;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class CurrencyHelper implements CurrencyHelperInterface
{
    /** @var EntityRepositoryInterface */
    private $currencyRepository;

    public function __construct(EntityRepositoryInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function getCurrencyIdFromIso(string $currencyIso, Context $context): ?string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('isoCode', $currencyIso));

        return $this->currencyRepository->searchIds($criteria, $context)->firstId();
    }
}
