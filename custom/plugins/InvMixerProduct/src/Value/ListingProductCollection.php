<?php declare(strict_types=1);

namespace InvMixerProduct\Value;


/**
 * Class ListingProductCollection
 * @package InvMixerProduct\Value
 */
final class ListingProductCollection
{

    /**
     * @var ListingProductGroup[]
     */
    private $groups = [];

    /**
     * @param ListingProductGroup $listingProductGroup
     * @return $this
     */
    public function addGroup(ListingProductGroup $listingProductGroup): self
    {
        $this->groups[$listingProductGroup->getGroupIdentifier()] = $listingProductGroup;

        return $this;
    }

    /**
     * @return ListingProductGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }



}


