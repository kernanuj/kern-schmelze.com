<?php

declare(strict_types=1);

namespace KlarnaPayment\Installer;

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
use Symfony\Component\DependencyInjection\ContainerInterface;

class RuleInstaller implements InstallerInterface
{
    public const VALID_COUNTRIES = [
        'AT',
        'CH',
        'DE',
        'DK',
        'FI',
        'NL',
        'NO',
        'SE',
        'GB',
    ];

    private const RULE_ID      = 'f3f95e9b4f7b446799aa22feae0c61aa';
    private const CONDITION_ID = '5bc599736527422894964260585b3c21';

    /** @var EntityRepositoryInterface */
    private $ruleRepository;

    /** @var EntityRepositoryInterface */
    private $countryRepository;

    public function __construct(ContainerInterface $container)
    {
        $this->ruleRepository    = $container->get('rule.repository');
        $this->countryRepository = $container->get('country.repository');
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
            'description' => 'Determines whether or not Klarna Payments is available.',
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
            new EqualsAnyFilter('iso', self::VALID_COUNTRIES)
        );

        return $this->countryRepository->search($criteria, $context)->getIds();
    }
}
