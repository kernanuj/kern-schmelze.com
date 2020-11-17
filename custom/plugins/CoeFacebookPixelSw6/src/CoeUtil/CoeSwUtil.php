<?php

namespace CoeFacebookPixelSw6\CoeUtil;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
 * Class CoeSwUtil
 * @package CoeFacebookPixelSw6\CoeSwUtil
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class CoeSwUtil
{

    /**
     * @param string $controllerString
     * @return ControllerDataStruct
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public static function extractControllerNameAndAction(string $controllerString) : ControllerDataStruct
    {
        list($controller, $action) = explode("::", $controllerString);

        $controllerParts = explode("\\", $controller);

        $controllerDataStruct = new ControllerDataStruct();
        $controllerDataStruct->controllerName = array_pop($controllerParts);
        $controllerDataStruct->action = $action;
        $controllerDataStruct->nsPath = implode("\\", $controllerParts);

        return $controllerDataStruct;
    }

    /**
     * @param Entity $entity
     * @param array $associations
     * @param DefinitionInstanceRegistry $registry
     * @param Context $context
     * @return Entity
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public static function reloadEntityWithAssociations(
        Entity $entity,
        array $associations,
        DefinitionInstanceRegistry $registry,
        Context $context
    ) : Entity{
        /** @var string $entityName */
        $entityName = ($registry->getByEntityClass($entity))->getEntityName();

        /** @var EntityRepositoryInterface $repo */
        $repo = $registry->getRepository($entityName);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter("id", $entity->getId()));
        $criteria->addAssociations($associations);
        $criteria->setLimit(1);

        return ($repo->search($criteria, $context))->first();
    }
}

/**
 * Class ControllerDataStruct
 * @package CoeFacebookPixelSw6\CoeSwUtil
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class ControllerDataStruct {
    /** @var string $controlllerName */
    public $controllerName;
    /** @var string $action */
    public $action;
    /** @var string $nsPath */
    public $nsPath;
}