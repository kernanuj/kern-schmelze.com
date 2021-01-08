<?php

declare(strict_types=1);

namespace KlarnaPayment\Installer\Modules;

use KlarnaPayment\Installer\InstallerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFieldInstaller implements InstallerInterface
{
    /**
     * Example:
     *
     * [
     *     'id'     => 'UUID',
     *     'name'   => 'field_set_technical_name',
     *     'active' => true,
     *     'config' => [
     *         'label' => [
     *             'en-GB' => 'Name',
     *             'de-DE' => 'Name',
     *         ],
     *     ],
     *     'customFields' => [
     *         [
     *             'id'     => 'UUID',
     *             'name'   => 'field_name',
     *             'active' => true,
     *             'type'   => CustomFieldTypes::TEXT,
     *             'config' => [
     *                 'label' => [
     *                     'en-GB' => 'Name',
     *                     'de-DE' => 'Name',
     *                 ],
     *             ],
     *         ],
     *     ],
     * ],
     */
    private const CUSTOM_FIELDSETS = [
        [
            'id'     => 'bdf291e1e7be415b98ffb0bbc8eb710b',
            'name'   => 'klarna',
            'active' => true,
            'config' => [
                'label' => [
                    'en-GB' => 'Klarna',
                    'de-DE' => 'Klarna',
                ],
            ],
            'customFields' => [
                [
                    'id'     => 'b1ae547185a24e0d973724409232f7a9',
                    'name'   => 'klarna_order_id',
                    'active' => true,
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Klarna Order ID',
                            'de-DE' => 'Klarna Order-ID',
                        ],
                    ],
                ],
                [
                    'id'     => 'f585e86f340c4e31bdfd5ca49cc93d5f',
                    'name'   => 'klarna_fraud_status',
                    'active' => true,
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Klarna Fraud Status',
                            'de-DE' => 'Klarna Fraud-Status',
                        ],
                    ],
                ],
                [
                    'id'     => '8477734532684639bd7b0fe8ea3da853',
                    'name'   => 'klarna_order_address_hash',
                    'active' => true,
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Klarna Order Address Hash',
                            'de-DE' => 'Klarna Adress-Hash',
                        ],
                    ],
                ],
                [
                    'id'     => 'c81828e18bbd44e7b0a59ce65e1f18a7',
                    'name'   => 'klarna_order_cart_hash',
                    'active' => true,
                    'type'   => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Klarna Order Cart Hash',
                            'de-DE' => 'Klarna Warenkorb-Hash',
                        ],
                    ],
                ],
            ],
        ],
    ];

    /** @var EntityRepositoryInterface */
    private $customFieldSetRepository;

    public function __construct(EntityRepositoryInterface $customFieldSetRepository)
    {
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
        if (empty(self::CUSTOM_FIELDSETS)) {
            return;
        }

        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $this->customFieldSetRepository->upsert(self::CUSTOM_FIELDSETS, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
        if (empty(self::CUSTOM_FIELDSETS)) {
            return;
        }

        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $this->customFieldSetRepository->upsert(self::CUSTOM_FIELDSETS, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        if (empty(self::CUSTOM_FIELDSETS)) {
            return;
        }

        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $data = $this->getDeactivateData();

            $this->customFieldSetRepository->upsert($data, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        if (empty(self::CUSTOM_FIELDSETS)) {
            return;
        }

        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $this->customFieldSetRepository->upsert(self::CUSTOM_FIELDSETS, $context);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        if (empty(self::CUSTOM_FIELDSETS)) {
            return;
        }

        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $data = $this->getDeactivateData();

            $this->customFieldSetRepository->upsert($data, $context);
        });
    }

    private function getDeactivateData(): array
    {
        $data = self::CUSTOM_FIELDSETS;

        foreach ($data as $setKey => $set) {
            $data[$setKey]['active'] = false;

            foreach ($set['customFields'] as $fieldKey => $customField) {
                $data[$setKey]['customFields'][$fieldKey]['active'] = false;
            }
        }

        return $data;
    }
}
