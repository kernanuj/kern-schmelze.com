<?php

declare(strict_types=1);

namespace KlarnaPayment\Core\System\SystemConfig;

use KlarnaPayment\Installer\PaymentMethodInstaller;
use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService as BaseSystemConfigService;

class SystemConfigService extends BaseSystemConfigService
{
    private const SETTING_ALLOWED_KLARNA_PAYMENTS_CODES = 'KlarnaPayment.settings.allowedKlarnaPaymentsCodes';

    /** @var BaseSystemConfigService */
    private $baseService;

    /** @var EntityRepositoryInterface */
    private $paymentMethodRepository;

    /** @var EntityRepositoryInterface */
    private $salesChannelRepository;

    public function __construct(
        BaseSystemConfigService $baseService,
        EntityRepositoryInterface $paymentMethodRepository,
        EntityRepositoryInterface $salesChannelRepository
    ) {
        $this->baseService             = $baseService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->salesChannelRepository  = $salesChannelRepository;
    }

    public function get(string $key, ?string $salesChannelId = null)
    {
        return $this->baseService->get($key, $salesChannelId);
    }

    public function all(?string $salesChannelId = null): array
    {
        return $this->baseService->all($salesChannelId);
    }

    public function getDomain(string $domain, ?string $salesChannelId = null, bool $inherit = false): array
    {
        return $this->baseService->getDomain($domain, $salesChannelId, $inherit);
    }

    public function set(string $key, $value, ?string $salesChannelId = null): void
    {
        $this->baseService->set($key, $value, $salesChannelId);

        if ($key === self::SETTING_ALLOWED_KLARNA_PAYMENTS_CODES) {
            $activeMethodCodes = $this->getActiveMethodCodes();

            $salesChannelIds = $this->salesChannelRepository->searchIds(new Criteria(), Context::createDefaultContext());

            foreach ($salesChannelIds->getIds() as $checkSalesChannelId) {
                if (is_string($checkSalesChannelId)) {
                    $activeMethodCodes = array_merge($activeMethodCodes, $this->getActiveMethodCodes($checkSalesChannelId));
                }
            }
            $activeMethodCodes   = array_unique($activeMethodCodes);
            $inactiveMethodCodes = array_values(array_diff(PaymentMethodInstaller::KLARNA_PAYMENTS_CODES, $activeMethodCodes));

            foreach ($activeMethodCodes as $code) {
                $this->setPaymentMethodStatus($code, true);
            }
            foreach ($inactiveMethodCodes as $code) {
                $this->setPaymentMethodStatus($code, false);
            }
        }
    }

    public function delete(string $key, ?string $salesChannel = null): void
    {
        $this->baseService->delete($key, $salesChannel);
    }

    public function savePluginConfiguration(Bundle $bundle, bool $override = false): void
    {
        $this->baseService->savePluginConfiguration($bundle, $override);
    }

    private function setPaymentMethodStatus(string $code, bool $active): void
    {
        $methodId = array_search($code, PaymentMethodInstaller::KLARNA_PAYMENTS_CODES, true);

        if (!$methodId) {
            return;
        }

        $this->paymentMethodRepository->update(
            [
                [
                    'id'     => $methodId,
                    'active' => $active,
                ],
            ],
            Context::createDefaultContext()
        );
    }

    private function getActiveMethodCodes(?string $salesChannelId = null): array
    {
        $activeMethodCodes = [];
        $values            = $this->get(self::SETTING_ALLOWED_KLARNA_PAYMENTS_CODES, $salesChannelId);

        if (!is_array($values)) {
            return [];
        }

        foreach ($values as $code) {
            switch ($code) {
                case PaymentMethodInstaller::KLARNA_PAYMENTS_PAY_NOW_CODE:
                    $activeMethodCodes[] = PaymentMethodInstaller::KLARNA_PAYMENTS_PAY_NOW_CODE;
                    $activeMethodCodes   = array_merge(
                        $activeMethodCodes,
                        array_map(
                            static function (string $id) {
                                return PaymentMethodInstaller::KLARNA_PAYMENTS_CODES[$id];
                            },
                            PaymentMethodInstaller::KLARNA_PAYMENTS_CODES_PAY_NOW_STANDALONE
                        )
                    );

                    break;
                default:
                    $activeMethodCodes[] = $code;

                    break;
            }
        }

        return $activeMethodCodes;
    }
}
