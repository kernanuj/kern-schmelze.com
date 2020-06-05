<?php declare(strict_types=1);

namespace InvMixerProduct\Service;


use InvMixerProduct\Struct\ContainerDefinitionCollection;

/**
 * Interface MixContainerProviderInterface
 * @package InvMixerProduct\Service
 */
interface MixContainerDefinitionProviderInterface
{

    /**
     * @return ContainerDefinitionCollection
     */
    public function getAvailableContainerCollection(): ContainerDefinitionCollection;


}
