<?php

declare(strict_types=1);

namespace KlarnaPayment\Core\System\SystemConfig;

use KlarnaPayment\Installer\PaymentMethodInstaller;
use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService as BaseSystemConfigService;

class SystemConfigService extends BaseSystemConfigService
{
    private const SETTING_ALLOWED_KLARNA_PAYMENTS_CODES = 'KlarnaPayment.settings.allowedKlarnaPaymentsCodes';

    private const SETTING_INSTANT_SHOPPING_ENABLED = 'KlarnaPayment.settings.instantShoppingEnabled';

    /** @var BaseSystemConfigService */
    private $baseService;

    /** @var EntityRepositoryInterface */
    private $paymentMethodRepository;

    /** @var EntityRepositoryInterface */
    private $salesChannelRepository;

    /** @var EntityRepositoryInterface */
    private $salesChannelPaymentRepository;

    public function __construct(
        BaseSystemConfigService $baseService,
        EntityRepositoryInterface $paymentMethodRepository,
        EntityRepositoryInterface $salesChannelRepository,
        EntityRepositoryInterface $salesChannelPaymentRepository
    ) {
        $this->baseService                   = $baseService;
        $this->paymentMethodRepository       = $paymentMethodRepository;
        $this->salesChannelRepository        = $salesChannelRepository;
        $this->salesChannelPaymentRepository = $salesChannelPaymentRepository;
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

        if ($key === self::SETTING_INSTANT_SHOPPING_ENABLED) {
            $this->updateInstantShoppingSalesChannelAssignment(Context::createDefaultContext());
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

    private function updateInstantShoppingSalesChannelAssignment(Context $context): void
    {
        $instantShoppingPaymentMethodEnabled = false;
        $salesChannels                       = $this->salesChannelRepository->search(new Criteria(), $context);

        /** @var SalesChannelEntity $channel */
        foreach ($salesChannels as $channel) {
            $isEnabled = $this->get('KlarnaPayment.settings.instantShoppingEnabled', $channel->getId());

            if ($isEnabled) {
                $instantShoppingPaymentMethodEnabled = true;
                $this->assignPaymentMethod($channel, $context);
            } else {
                $this->unassignPaymentMethod($channel, $context);
            }
        }

        $this->setInstantShoppingPaymentMethodActiveState($instantShoppingPaymentMethodEnabled, $context);
    }

    private function assignPaymentMethod(SalesChannelEntity $salesChannel, Context $context): void
    {
        if (!is_array($salesChannel->getPaymentMethodIds()) || !in_array(PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING, $salesChannel->getPaymentMethodIds())) {
            $this->salesChannelRepository->update(
                [
                    [
                        'id'             => $salesChannel->getId(),
                        'paymentMethods' => [
                            ['id' => PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING],
                        ],
                    ],
                ],
                $context
            );
        }
    }

    private function unassignPaymentMethod(SalesChannelEntity $salesChannel, Context $context): void
    {
        $this->salesChannelPaymentRepository->delete(
            [
                [
                    'salesChannelId'  => $salesChannel->getId(),
                    'paymentMethodId' => PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING,
                ],
            ],
            $context
        );
    }

    private function setInstantShoppingPaymentMethodActiveState(bool $isActive, Context $context): void
    {
        $this->paymentMethodRepository->update([
            [
                'id'     => PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING,
                'active' => $isActive,
            ],
        ], $context);
    }
}
