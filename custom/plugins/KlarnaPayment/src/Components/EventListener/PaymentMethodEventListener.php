<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Installer\Modules\PaymentMethodInstaller;
use KlarnaPayment\Installer\Modules\RuleInstaller;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityIdSearchResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Event\GenericEvent;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityIdSearchResultLoadedEvent;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntitySearchResultLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentMethodEventListener implements EventSubscriberInterface
{
    /** @var ConfigReaderInterface */
    private $configReader;

    /** @var EntityRepositoryInterface */
    private $languageRepository;

    public function __construct(ConfigReaderInterface $configReader, EntityRepositoryInterface $languageRepository)
    {
        $this->configReader       = $configReader;
        $this->languageRepository = $languageRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.payment_method.search.id.result.loaded' => ['onSalesChannelIdSearchResultLoaded', -1],
            'payment_method.search.id.result.loaded'               => ['onIdSearchResultLoaded', -1],
            'sales_channel.payment_method.search.result.loaded'    => ['onSalesChannelSearchResultLoaded', -1],
            'payment_method.search.result.loaded'                  => ['onSearchResultLoaded', -1],
        ];
    }

    public function onSalesChannelIdSearchResultLoaded(SalesChannelEntityIdSearchResultLoadedEvent $event): void
    {
        $source = $event->getContext()->getSource();

        if (!($source instanceof SalesChannelApiSource)) {
            return;
        }

        if ($this->removeInvalidPaymentMethods($event)) {
            return;
        }

        $this->removeDeactivatedPaymentMethodsIds($event->getResult(), $source->getSalesChannelId());
    }

    public function onIdSearchResultLoaded(EntityIdSearchResultLoadedEvent $event): void
    {
        $source = $event->getContext()->getSource();

        if (!($source instanceof SalesChannelApiSource)) {
            return;
        }

        $this->removeDeactivatedPaymentMethodsIds($event->getResult(), $source->getSalesChannelId());
    }

    public function onSalesChannelSearchResultLoaded(SalesChannelEntitySearchResultLoadedEvent $event): void
    {
        $source = $event->getContext()->getSource();

        if (!($source instanceof SalesChannelApiSource)) {
            return;
        }

        if ($this->removeInvalidPaymentMethods($event)) {
            return;
        }

        $this->removeDeactivatedPaymentMethods($event->getResult(), $source->getSalesChannelId());
    }

    public function onSearchResultLoaded(EntitySearchResultLoadedEvent $event): void
    {
        $source = $event->getContext()->getSource();

        if (!($source instanceof SalesChannelApiSource)) {
            return;
        }

        $this->removeDeactivatedPaymentMethods($event->getResult(), $source->getSalesChannelId());
    }

    private function removeDeactivatedPaymentMethods(EntitySearchResult $result, string $salesChannelId = null): void
    {
        $validPaymentMethods     = $this->getValidPaymentMethods($salesChannelId);
        $allKlarnaPaymentMethods = $this->getAllKlarnaPaymentMethods();

        $filter = static function (PaymentMethodEntity $entity) use ($validPaymentMethods, $allKlarnaPaymentMethods) {
            if (!in_array($entity->getId(), $allKlarnaPaymentMethods, true)) {
                return true;
            }

            return in_array($entity->getId(), $validPaymentMethods, true);
        };

        $filteredPaymentMethods = $result->getEntities()->filter($filter);

        $result->assign([
            'total'    => count($filteredPaymentMethods),
            'entities' => $filteredPaymentMethods,
            'elements' => $filteredPaymentMethods->getElements(),
        ]);
    }

    /**
     * @param SalesChannelEntityIdSearchResultLoadedEvent|SalesChannelEntitySearchResultLoadedEvent $event
     */
    private function removeInvalidPaymentMethods(GenericEvent $event): bool
    {
        $salesChannelContext = $event->getSalesChannelContext();
        $customer            = $salesChannelContext->getCustomer();
        $currencyIsoCode     = $salesChannelContext->getCurrency()->getIsoCode();
        $languageId          = $salesChannelContext->getSalesChannel()->getLanguageId();
        $billingAddress      = $customer ? $customer->getDefaultBillingAddress() : null;
        $language            = $this->getLanguageById($languageId, $salesChannelContext->getContext());

        if (!$language || !$billingAddress || !($country = $billingAddress->getCountry()) || !($countryIso = $country->getIso())) {
            return false;
        }

        /** @var LocaleEntity $locale */
        $locale       = $language->getLocale();
        $klarnaLocale = strtolower(substr($locale->getCode(), 0, 2)) . '-' . strtoupper($countryIso);
        $rule         = RuleInstaller::AVAIBILITY_CONDITIONS[$countryIso] ?? null;

        if ($rule === null || $rule['currency'] !== $currencyIsoCode || !in_array($klarnaLocale, $rule['locales'], true)) {
            if ($event instanceof SalesChannelEntityIdSearchResultLoadedEvent) {
                $this->removeAllKlarnaPaymentMethodsIds($event->getResult());
            } else {
                $this->removeAllKlarnaPaymentMethods($event->getResult());
            }

            return true;
        }

        return false;
    }

    private function removeAllKlarnaPaymentMethods(EntitySearchResult $result): void
    {
        $allKlarnaPaymentMethods = $this->getAllKlarnaPaymentMethods();

        $filter = static function (PaymentMethodEntity $entity) use ($allKlarnaPaymentMethods) {
            if (in_array($entity->getId(), $allKlarnaPaymentMethods, true)) {
                return false;
            }

            return true;
        };

        $filteredPaymentMethods = $result->getEntities()->filter($filter);

        $result->assign([
            'total'    => count($filteredPaymentMethods),
            'entities' => $filteredPaymentMethods,
            'elements' => $filteredPaymentMethods->getElements(),
        ]);
    }

    private function removeAllKlarnaPaymentMethodsIds(IdSearchResult $result): void
    {
        $allKlarnaPaymentMethods = $this->getAllKlarnaPaymentMethods();

        $filter = static function (string $paymentMethod) use ($allKlarnaPaymentMethods) {
            if (in_array($paymentMethod, $allKlarnaPaymentMethods, true)) {
                return false;
            }

            return true;
        };

        $filteredPaymentMethods = array_filter($result->getIds(), $filter);

        $result->assign([
            'total'    => count($filteredPaymentMethods),
            'ids'      => $filteredPaymentMethods,
            'entities' => $filteredPaymentMethods,
            'elements' => $filteredPaymentMethods,
        ]);
    }

    private function removeDeactivatedPaymentMethodsIds(IdSearchResult $result, string $salesChannelId = null): void
    {
        $validPaymentMethods     = $this->getValidPaymentMethods($salesChannelId);
        $allKlarnaPaymentMethods = $this->getAllKlarnaPaymentMethods();

        $filter = static function (string $paymentMethod) use ($validPaymentMethods, $allKlarnaPaymentMethods) {
            if (!in_array($paymentMethod, $allKlarnaPaymentMethods, true)) {
                return true;
            }

            return in_array($paymentMethod, $validPaymentMethods, true);
        };

        $filteredPaymentMethods = array_filter($result->getIds(), $filter);

        $result->assign([
            'total'    => count($filteredPaymentMethods),
            'ids'      => $filteredPaymentMethods,
            'entities' => $filteredPaymentMethods,
            'elements' => $filteredPaymentMethods,
        ]);
    }

    private function getValidPaymentMethods(string $salesChannelId = null): array
    {
        $config = $this->configReader->read($salesChannelId);

        if ($config->get('klarnaType') === 'checkout') {
            $validPaymentMethods = array_keys(PaymentMethodInstaller::KLARNA_CHECKOUT_CODES);
        } elseif ($config->get('klarnaType') === 'payments') {
            $validPaymentMethods = array_keys(PaymentMethodInstaller::KLARNA_PAYMENTS_CODES);

            $merchantValidKlarnaPaymentsMethods = $config->get('allowedKlarnaPaymentsCodes', []);

            if (in_array(PaymentMethodInstaller::KLARNA_PAYMENTS_PAY_NOW_CODE, $merchantValidKlarnaPaymentsMethods, true)) {
                $additionalValidCodes = array_map(
                    static function (string $paymentMethodId) {
                        return PaymentMethodInstaller::KLARNA_PAYMENTS_CODES[$paymentMethodId];
                    },
                    PaymentMethodInstaller::KLARNA_PAYMENTS_CODES_PAY_NOW_STANDALONE
                );
                $merchantValidKlarnaPaymentsMethods = array_unique(array_merge($merchantValidKlarnaPaymentsMethods, $additionalValidCodes));
            }

            $validPaymentMethods = array_filter(
                $validPaymentMethods,
                static function (string $paymentMethodId) use ($merchantValidKlarnaPaymentsMethods) {
                    return in_array(PaymentMethodInstaller::KLARNA_PAYMENTS_CODES[$paymentMethodId], $merchantValidKlarnaPaymentsMethods, true);
                }
            );
        } else {
            $validPaymentMethods = [];
        }

        return $validPaymentMethods;
    }

    private function getAllKlarnaPaymentMethods(): array
    {
        $allKlarnaPaymentMethods = array_merge(
            array_keys(PaymentMethodInstaller::KLARNA_CHECKOUT_CODES),
            array_keys(PaymentMethodInstaller::KLARNA_PAYMENTS_CODES)
        );

        $allKlarnaPaymentMethods[] = PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING;

        return $allKlarnaPaymentMethods;
    }

    private function getLanguageById(string $id, Context $context): ?LanguageEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('locale');

        return $this->languageRepository->search($criteria, $context)->first();
    }
}
