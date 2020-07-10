<?php

declare(strict_types=1);

namespace KlarnaPayment\Installer;

use KlarnaPayment\Components\PaymentHandler\KlarnaInstantShoppingPaymentHandler;
use KlarnaPayment\Components\PaymentHandler\KlarnaPaymentsPaymentHandler;
use KlarnaPayment\KlarnaPayment;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentMethodInstaller implements InstallerInterface
{
    public const KLARNA_CHECKOUT             = 'e38e7c972cf4433dbbf7794fdea32cf9';
    public const KLARNA_PAY_LATER            = 'ede05b719b214143a4cb1c0216b852de';
    public const KLARNA_FINANCING            = 'ad4ca642046b40248444eba38bb8f5e8';
    public const KLARNA_DIRECT_DEBIT         = '9f4ac7bef3394487b0ab9298d12eb1bd';
    public const KLARNA_DIRECT_BANK_TRANSFER = 'a03b53a6e3d34836b150cc6eeaf6d97d';
    public const KLARNA_CREDIT_CARD          = 'd245c39e8707e85f053e806abffcbb36';
    public const KLARNA_PAY_NOW              = 'f1ef36538c594dc580b59e28206a1297';

    public const KLARNA_INSTANT_SHOPPING = '0e9d7933f84244879a78acfc5b8a8d99';

    public const KLARNA_PAYMENTS_CODES = [
        self::KLARNA_PAY_LATER            => 'pay_later',
        self::KLARNA_FINANCING            => 'pay_over_time',
        self::KLARNA_DIRECT_DEBIT         => 'direct_debit',
        self::KLARNA_DIRECT_BANK_TRANSFER => 'direct_bank_transfer',
        self::KLARNA_CREDIT_CARD          => 'card',
        self::KLARNA_PAY_NOW              => self::KLARNA_PAYMENTS_PAY_NOW_CODE,
    ];

    public const KLARNA_PAYMENTS_CODES_PAY_NOW_STANDALONE = [
        self::KLARNA_CREDIT_CARD,
        self::KLARNA_DIRECT_BANK_TRANSFER,
        self::KLARNA_DIRECT_DEBIT,
    ];

    public const KLARNA_PAYMENTS_CODES_WITH_PAY_NOW_COMBINED = [
        self::KLARNA_PAY_NOW,
        self::KLARNA_PAY_LATER,
        self::KLARNA_FINANCING,
    ];

    public const KLARNA_PAYMENTS_PAY_NOW_CODE = 'pay_now';

    public const KLARNA_CHECKOUT_CODES = [
        self::KLARNA_CHECKOUT => 'checkout',
    ];

    /**
     * Example:
     *
     * [
     *     'id'                => 'UUID',
     *     'handlerIdentifier' => Handler::class,
     *     'translations'      => [
     *         'de-DE' => [
     *             'name'        => 'Name',
     *             'description' => 'Description',
     *         ],
     *         'en-GB' => [
     *             'name'        => 'Name',
     *             'description' => 'Description',
     *         ],
     *     ],
     * ]
     *
     * Klarna codes: 'pay_later','pay_over_time','direct_debit','direct_bank_transfer','card','pay_now'
     */
    private const PAYMENT_METHODS = [
        self::KLARNA_PAY_LATER => [
            'id'                => self::KLARNA_PAY_LATER,
            'handlerIdentifier' => KlarnaPaymentsPaymentHandler::class,
            'afterOrderEnabled' => true,
            'translations'      => [
                'de-DE' => [
                    'name' => 'Klarna Rechnung',
                ],
                'en-GB' => [
                    'name' => 'Klarna Pay Later',
                ],
            ],
        ],
        self::KLARNA_FINANCING => [
            'id'                => self::KLARNA_FINANCING,
            'handlerIdentifier' => KlarnaPaymentsPaymentHandler::class,
            'afterOrderEnabled' => true,
            'translations'      => [
                'de-DE' => [
                    'name' => 'Klarna Ratenkauf',
                ],
                'en-GB' => [
                    'name' => 'Klarna Financing',
                ],
            ],
        ],
        self::KLARNA_DIRECT_DEBIT => [
            'id'                => self::KLARNA_DIRECT_DEBIT,
            'handlerIdentifier' => KlarnaPaymentsPaymentHandler::class,
            'afterOrderEnabled' => true,
            'translations'      => [
                'de-DE' => [
                    'name' => 'Klarna Lastschrift',
                ],
                'en-GB' => [
                    'name' => 'Klarna Direct Debit',
                ],
            ],
        ],
        self::KLARNA_DIRECT_BANK_TRANSFER => [
            'id'                => self::KLARNA_DIRECT_BANK_TRANSFER,
            'handlerIdentifier' => KlarnaPaymentsPaymentHandler::class,
            'afterOrderEnabled' => true,
            'translations'      => [
                'de-DE' => [
                    'name' => 'Klarna Sofortüberweisung',
                ],
                'en-GB' => [
                    'name' => 'Klarna Online Bank Transfer',
                ],
            ],
        ],
        self::KLARNA_INSTANT_SHOPPING => [
            'id'                => self::KLARNA_INSTANT_SHOPPING,
            'handlerIdentifier' => KlarnaInstantShoppingPaymentHandler::class,
            'translations'      => [
                'de-DE' => [
                    'name' => 'Klarna Instant Shopping',
                ],
                'en-GB' => [
                    'name' => 'Klarna Instant Shopping',
                ],
            ],
            'active' => false,
        ],
        self::KLARNA_CREDIT_CARD => [
            'id'                => self::KLARNA_CREDIT_CARD,
            'handlerIdentifier' => KlarnaPaymentsPaymentHandler::class,
            'afterOrderEnabled' => true,
            'translations'      => [
                'de-DE' => [
                    'name' => 'Klarna Kreditkarte',
                ],
                'en-GB' => [
                    'name' => 'Klarna Credit Card',
                ],
            ],
        ],
        self::KLARNA_PAY_NOW => [
            'id'                => self::KLARNA_PAY_NOW,
            'handlerIdentifier' => KlarnaPaymentsPaymentHandler::class,
            'afterOrderEnabled' => true,
            'translations'      => [
                'de-DE' => [
                    'name' => 'Klarna Sofort bezahlen',
                ],
                'en-GB' => [
                    'name' => 'Klarna Pay Now',
                ],
            ],
        ],
    ];

    /** @var EntityRepositoryInterface */
    private $paymentMethodRepository;

    /** @var PluginIdProvider */
    private $pluginIdProvider;

    public function __construct(ContainerInterface $container)
    {
        $this->paymentMethodRepository = $container->get('payment_method.repository');
        $this->pluginIdProvider        = $container->get(PluginIdProvider::class);
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
        if (empty(self::PAYMENT_METHODS)) {
            return;
        }

        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->upsertPaymentMethod($paymentMethod, $context->getContext());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
        if (empty(self::PAYMENT_METHODS)) {
            return;
        }

        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->upsertPaymentMethod($paymentMethod, $context->getContext());
        }

        if ($context->getPlugin()->isActive()) {
            $this->setPaymentMethodStatus(self::PAYMENT_METHODS[self::KLARNA_INSTANT_SHOPPING], true, $context->getContext());
        }

        $this->removeKlarnaCheckout($context->getContext());
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        if (empty(self::PAYMENT_METHODS)) {
            return;
        }

        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->setPaymentMethodStatus($paymentMethod, false, $context->getContext());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        $this->setPaymentMethodStatus(self::PAYMENT_METHODS[self::KLARNA_INSTANT_SHOPPING], true, $context->getContext());
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        if (empty(self::PAYMENT_METHODS)) {
            return;
        }

        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->setPaymentMethodStatus($paymentMethod, false, $context->getContext());
        }
    }

    private function upsertPaymentMethod(array $paymentMethod, Context $context): void
    {
        $pluginId                  = $this->pluginIdProvider->getPluginIdByBaseClass(KlarnaPayment::class, $context);
        $paymentMethod['pluginId'] = $pluginId;

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($paymentMethod): void {
            $this->paymentMethodRepository->upsert([$paymentMethod], $context);
        });
    }

    private function setPaymentMethodStatus(array $paymentMethod, bool $active, Context $context): void
    {
        $paymentMethodCriteria = new Criteria([$paymentMethod['id']]);
        $hasPaymentMethod      = $this->paymentMethodRepository->searchIds($paymentMethodCriteria, $context)->getTotal() > 0;

        if (!$hasPaymentMethod) {
            return;
        }

        $data = [
            'id'     => $paymentMethod['id'],
            'active' => $active,
        ];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($data): void {
            $this->paymentMethodRepository->upsert([$data], $context);
        });
    }

    private function removeKlarnaCheckout(Context $context): void
    {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $this->paymentMethodRepository->delete([['id' => self::KLARNA_CHECKOUT]], $context);
        });
    }
}
