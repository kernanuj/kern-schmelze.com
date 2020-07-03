<?php declare(strict_types=1);

namespace InvMixerProduct\Service;


use InvMixerProduct\Constants;
use InvMixerProduct\Struct\ContainerDefinition;
use InvMixerProduct\Struct\ContainerDefinitionCollection;

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


        foreach (Constants::allKSPackageBaseProducts() as $baseProduct) {
            foreach (Constants::allKSPackageDesigns() as $design) {
                foreach (Constants::allFillDelimiter() as $fillDelimiter) {
                    $collection->addItem(
                        ContainerDefinition::build(
                            $design,
                            $baseProduct,
                            $fillDelimiter
                        )
                    );
                }
            }
        }

        return $collection;
    }


}
