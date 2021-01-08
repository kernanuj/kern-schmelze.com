<?php

declare(strict_types=1);

namespace KlarnaPayment\Installer\Modules;

use KlarnaPayment\Installer\InstallerInterface;
use Shopware\Core\Checkout\Customer\Rule\BillingCountryRule;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class RuleInstaller implements InstallerInterface
{
    /**
     * further information regarding the avaibility of klarna payment methods:
     * https://developers.klarna.com/documentation/klarna-payments/in-depth-knowledge/puchase-countries-currencies-locales/
     */
    public const AVAIBILITY_CONDITIONS = [
        'AT' => [
            'currency' => 'EUR',
            'locales'  => ['de-AT', 'en-AT'],
        ],
        'CH' => [
            'currency' => 'CHF',
            'locales'  => ['de-CH', 'fr-CH', 'it-CH', 'en-CH'],
        ],
        'DE' => [
            'currency' => 'EUR',
            'locales'  => ['de-DE', 'en-DE'],
        ],
        'DK' => [
            'currency' => 'DKK',
            'locales'  => ['da-DK', 'en-DK'],
        ],
        'FI' => [
            'currency' => 'EUR',
            'locales'  => ['fi-FI', 'sv-FI', 'en-FI'],
        ],
        'NL' => [
            'currency' => 'EUR',
            'locales'  => ['nl-NL', 'en-NL'],
        ],
        'NO' => [
            'currency' => 'NOK',
            'locales'  => ['nb-NO', 'en-NO'],
        ],
        'SE' => [
            'currency' => 'SEK',
            'locales'  => ['sv-SE', 'en-SE'],
        ],
        'GB' => [
            'currency' => 'GBP',
            'locales'  => ['en-GB'],
        ],
        'US' => [
            'currency' => 'USD',
            'locales'  => ['en-US'],
        ],
        'AU' => [
            'currency' => 'AUD',
            'locales'  => ['en-AU'],
        ],
        'BE' => [
            'currency' => 'EUR',
            'locales'  => ['nl-BE', 'fr-BE'],
        ],
        'ES' => [
            'currency' => 'EUR',
            'locales'  => ['es-ES'],
        ],
    ];

    private const RULE_ID      = 'f3f95e9b4f7b446799aa22feae0c61aa';
    private const CONDITION_ID = '5bc599736527422894964260585b3c21';

    /** @var EntityRepositoryInterface */
    private $ruleRepository;

    /** @var EntityRepositoryInterface */
    private $countryRepository;

    public function __construct(EntityRepositoryInterface $ruleRepository, EntityRepositoryInterface $countryRepository)
    {
        $this->ruleRepository    = $ruleRepository;
        $this->countryRepository = $countryRepository;
    }

    public function install(InstallContext $context): void
    {
        $this->upsertAvailabilityRule($context->getContext());
    }

    public function update(UpdateContext $context): void
    {
        $this->upsertAvailabilityRule($context->getContext());
    }

    public function uninstall(UninstallContext $context): void
    {
        $this->removeAvailabilityRule($context->getContext());
    }

    public function activate(ActivateContext $context): void
    {
        // Nothing to do here
    }

    public function deactivate(DeactivateContext $context): void
    {
        // Nothing to do here
    }

    private function upsertAvailabilityRule(Context $context): void
    {
        $data = [
            'id'          => self::RULE_ID,
            'name'        => 'Klarna Payments',
            'priority'    => 1,
            'description' => 'Determines whether or not Klarna Payments is available. Further Information: https://developers.klarna.com/documentation/klarna-payments/in-depth-knowledge/puchase-countries-currencies-locales/',
            'moduleTypes' => ['types' => ['payment']],
            'conditions'  => [
                [
                    'id'    => self::CONDITION_ID,
                    'type'  => (new BillingCountryRule())->getName(),
                    'value' => [
                        'operator'   => BillingCountryRule::OPERATOR_EQ,
                        'countryIds' => array_values($this->getCountries($context)),
                    ],
                ],
            ],
            'paymentMethods' => [
                ['id' => PaymentMethodInstaller::KLARNA_PAY_LATER],
                ['id' => PaymentMethodInstaller::KLARNA_FINANCING],
                ['id' => PaymentMethodInstaller::KLARNA_DIRECT_DEBIT],
                ['id' => PaymentMethodInstaller::KLARNA_DIRECT_BANK_TRANSFER],
                ['id' => PaymentMethodInstaller::KLARNA_CREDIT_CARD],
                ['id' => PaymentMethodInstaller::KLARNA_PAY_NOW],
            ],
        ];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($data): void {
            $this->ruleRepository->upsert([$data], $context);
        });
    }

    private function removeAvailabilityRule(Context $context): void
    {
        $deletion = [
            'id' => self::RULE_ID,
        ];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($deletion): void {
            $this->ruleRepository->delete([$deletion], $context);
        });
    }

    private function getCountries(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter('iso', array_keys(self::AVAIBILITY_CONDITIONS))
        );

        return $this->countryRepository->search($criteria, $context)->getIds();
    }
}
