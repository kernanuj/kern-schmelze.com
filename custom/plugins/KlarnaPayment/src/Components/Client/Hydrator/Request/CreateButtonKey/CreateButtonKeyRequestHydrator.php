<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\CreateButtonKey;

use KlarnaPayment\Components\Client\Request\CreateButtonKeyRequest;
use KlarnaPayment\Components\Client\Struct\Options;
use KlarnaPayment\Components\Helper\MerchantUrlHelper\MerchantUrlHelperInterface;
use LogicException;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CreateButtonKeyRequestHydrator implements CreateButtonKeyRequestHydratorInterface
{
    /** @var SalesChannelDomainEntity */
    protected $salesChannelDomain;

    /** @var MerchantUrlHelperInterface */
    private $merchantUrlHelper;

    /** @var SystemConfigService */
    private $systemConfigService;

    public function __construct(
        MerchantUrlHelperInterface $merchantUrlHelper,
        SystemConfigService $systemConfigService
    ) {
        $this->merchantUrlHelper   = $merchantUrlHelper;
        $this->systemConfigService = $systemConfigService;
    }

    public function hydrate(SalesChannelDomainEntity $salesChannelDomain, Context $context): CreateButtonKeyRequest
    {
        $salesChannel = $salesChannelDomain->getSalesChannel();

        if (null === $salesChannel) {
            throw new LogicException('sales channel entity missing from sales channel domain entity');
        }

        $request = new CreateButtonKeyRequest();
        $request->assign([
            'salesChannel' => $salesChannelDomain->getSalesChannelId(),
            'name'         => $salesChannelDomain->getUrl(),
            'merchantUrls' => $this->merchantUrlHelper->getMerchantUrls($salesChannelDomain),
            'options'      => $this->getOptions($salesChannel->getId()),
        ]);

        return $request;
    }

    private function getOptions(string $salesChannelId): Options
    {
        $options = new Options();
        $options->assign([
            'allow_separate_shipping_address'          => true,
            'date_of_birth_mandatory'                  => false,
            'phone_mandatory'                          => false,
            'national_identification_number_mandatory' => false,
            'title_mandatory'                          => false,
        ]);

        if ($this->isPhoneNumberMandatory($salesChannelId)) {
            $options->assign(['phone_mandatory' => true]);
        }

        if ($this->isBirthdayMandatory($salesChannelId)) {
            $options->assign(['date_of_birth_mandatory' => true]);
        }

        return $options;
    }

    private function isPhoneNumberMandatory(string $salesChannelId): bool
    {
        if (!$this->systemConfigService->get('core.loginRegistration.showPhoneNumberField', $salesChannelId)) {
            return false;
        }

        if (!$this->systemConfigService->get('core.loginRegistration.phoneNumberFieldRequired', $salesChannelId)) {
            return false;
        }

        return true;
    }

    private function isBirthdayMandatory(string $salesChannelId): bool
    {
        if (!$this->systemConfigService->get('core.loginRegistration.showBirthdayField', $salesChannelId)) {
            return false;
        }

        if (!$this->systemConfigService->get('core.loginRegistration.birthdayFieldRequired', $salesChannelId)) {
            return false;
        }

        return true;
    }
}
