<?php declare(strict_types=1);

namespace InvMixerProduct\Service;


use InvMixerProduct\Struct\ContainerDefinition;
use InvMixerProduct\Struct\ContainerDefinitionCollection;
use InvMixerProduct\Value\Design;
use InvMixerProduct\Value\Weight;

/**
 *
 * This is a class that will just return a static list of available mix containers;
 * if in the future the list will have to be configurable, the interface should be implemented by a class with
 * access to the admin configuration.
 *
 * Interface MixContainerProviderInterface
 * @package InvMixerProduct\Service
 */
class StaticMixContainerDefinitionProvider implements MixContainerDefinitionProviderInterface
{

    /**
     * @return ContainerDefinitionCollection
     */
    public function getAvailableContainerCollection(): ContainerDefinitionCollection
    {

        $collection = new ContainerDefinitionCollection();

        $collection
            ->addItem(ContainerDefinition::build(
                    Design::fromString('black'),
                    Weight::xGrams(200),
                    10
            ))
            ->addItem(ContainerDefinition::build(
                    Design::fromString('white'),
                    Weight::xGrams(200),
                    10
            ))
            ->addItem(ContainerDefinition::build(
                    Design::fromString('black'),
                    Weight::xGrams(500),
                    10
            ))
            ->addItem(ContainerDefinition::build(
                    Design::fromString('white'),
                    Weight::xGrams(500),
                    10
            ));

        return $collection;
    }


}
