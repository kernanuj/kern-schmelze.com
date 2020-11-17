<?php

declare(strict_types=1);

namespace Sisi\SisiDetailVideo6;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

/**
 * Class SisiDetailVideo6
 *
 * @package Sisi\SisiDetailVideo6
 */
class SisiDetailVideo6 extends Plugin
{
    /**
     * {@inheritDoc}
     */
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        /** @var EntityRepositoryInterface $repository */
        $repository = $this->container->get('custom_field_set.repository');

        $repository->create(
            [
                [
                    'name' => 'YoutubeLink',
                    'config' => ['label' => 'Youtube Link'],
                    'customFields' => [
                        [
                            'name' => 'sisi_video_id',
                            'label' => 'Youtube Video ID',
                            'config' => ['label' => 'Youtube Video ID'],
                            'type' => CustomFieldTypes::TEXT
                        ],

                        [
                            'name' => 'sisi_show_listing',
                            'label' => 'Show video in listing',
                            'config' => ['label' => 'Show video in listing'],
                            'type' => CustomFieldTypes::BOOL
                        ]
                    ],
                    'relations' => [
                        [
                            'entityName' => 'product',
                        ],
                    ],

                ]
            ],
            $installContext->getContext()
        );
    }

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if (!$uninstallContext->keepUserData()) {
            /** @var EntityRepositoryInterface $repository */
            $repository = $this->container->get('custom_field_set.repository');

            $serchYTFieldCriteria = new Criteria();
            $serchYTFieldCriteria->addFilter(new EqualsFilter('name', 'YoutubeLink'));

            $serchYTFieldResponse = $repository->search(
                $serchYTFieldCriteria,
                $uninstallContext->getContext()
            );

            $youtubeId = $serchYTFieldResponse->getEntities()->getIds();
            $youtubeId = reset($youtubeId);

            $repository->delete(
                [
                    [
                        'id' => $youtubeId
                    ]
                ],
                $uninstallContext->getContext()
            );
        }
        parent::uninstall($uninstallContext);
    }
}
