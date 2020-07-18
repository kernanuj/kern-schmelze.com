<?php declare(strict_types=1);

namespace InvExportLabel\Service;

/**
 * Class TypeInstanceRegistry
 * @package InvExportLabel\Service
 */
final class TypeInstanceRegistry
{


    /**
     * @var TypeInstanceInterface
     */
    private $typeInstances = [];

    /**
     * @param string $type
     * @return TypeInstanceInterface
     */
    public function forType(string $type): TypeInstanceInterface
    {
        return $this->typeInstances[$type];
    }

    /**
     * @param string $type
     * @param TypeInstanceInterface $renderer
     * @return $this
     */
    public function addTypeInstance(string $type, TypeInstanceInterface $renderer): self
    {
        $this->typeInstances[$type] = $renderer;
        return $this;
    }
}
