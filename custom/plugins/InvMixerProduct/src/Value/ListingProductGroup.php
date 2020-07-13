<?php declare(strict_types=1);

namespace InvMixerProduct\Value;


use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

/**
 * Class ListingProductGroup
 * @package InvMixerProduct\Value
 */
final class ListingProductGroup
{

    /**
     * @var string
     */
    private $groupIdentifier;

    /**
     * @var EntitySearchResult
     */
    private $entitySearchResult;

    /**
     * @param string $groupIdentifier
     * @param EntitySearchResult $entitySearchResult
     * @return static
     */
    public static function fromEntitySearchResult(string $groupIdentifier, EntitySearchResult $entitySearchResult): self
    {
        $self = new self();
        $self->groupIdentifier = $groupIdentifier;
        $self->entitySearchResult = $entitySearchResult;

        return $self;
    }

    /**
     * @return string
     */
    public function getGroupIdentifier(): string
    {
        return $this->groupIdentifier;
    }

    /**
     * @return EntitySearchResult
     */
    public function getEntitySearchResult(): EntitySearchResult
    {
        return $this->entitySearchResult;
    }


}


