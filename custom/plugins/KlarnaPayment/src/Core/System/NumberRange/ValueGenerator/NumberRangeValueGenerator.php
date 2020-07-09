<?php

declare(strict_types=1);

namespace KlarnaPayment\Core\System\NumberRange\ValueGenerator;

use KlarnaPayment\Components\Extension\GuestCustomerRegistrationExtension;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;

class NumberRangeValueGenerator implements NumberRangeValueGeneratorInterface
{
    private const TEMPORARY_KLARNA_CUSTOMER_NUMBER = 'klarna-instant-shopping';

    /** @var NumberRangeValueGeneratorInterface */
    private $baseService;

    public function __construct(NumberRangeValueGeneratorInterface $baseService)
    {
        $this->baseService = $baseService;
    }

    public function getValue(string $type, Context $context, ?string $salesChannelId, bool $preview = false): string
    {
        if ($context->hasExtension(GuestCustomerRegistrationExtension::EXTENSION_NAME)) {
            return self::TEMPORARY_KLARNA_CUSTOMER_NUMBER;
        }

        return $this->baseService->getValue($type, $context, $salesChannelId, $preview);
    }

    public function previewPattern(string $definition, string $pattern, int $start): string
    {
        return $this->baseService->previewPattern($definition, $pattern, $start);
    }
}
