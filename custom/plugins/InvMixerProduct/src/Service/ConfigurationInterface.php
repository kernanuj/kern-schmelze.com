<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Struct\ContainerDefinition;

/**
 * Interface ConfigurationInterface
 * @package InvMixerProduct\Service
 */
interface ConfigurationInterface
{

    /**
     * @return ContainerDefinition
     */
    public function getDefaultContainerDefinition(): ContainerDefinition;

}
