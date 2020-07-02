<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Constants;
use InvMixerProduct\Struct\ContainerDefinition;

/**
 * Interface StaticConfiguration
 * @package InvMixerProduct\Service
 */
class StaticConfiguration implements ConfigurationInterface
{

    /**
     * @inheritDoc
     */
    public function getDefaultContainerDefinition(): ContainerDefinition
    {
        return new ContainerDefinition(
            Constants::KS_PACKAGE_DESIGN_1(),
            Constants::KS_BASEPRODUCT_MILCHSCHOKOLOADE(),
            Constants::KS_FILL_DELIMITER_100()
        );

    }

}
