<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\DataObject\MixView;
use InvMixerProduct\Entity\MixEntity;
use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Value\Identifier;
use InvMixerProduct\Value\Label;
use InvMixerProduct\Struct\ContainerDefinition;
use InvMixerProduct\Value\Weight;
use \InvMixerProduct\Value\Price;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class MixViewTransformer
 * @package InvMixerProduct\Service
 */
class MixViewTransformer
{

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mix
     * @return MixView
     */
    public function transform(
        SalesChannelContext $salesChannelContext,
        MixEntity $mix
    ): MixView {

        return new MixView(
            Identifier::fromString($mix->getId()),
            Label::aEmpty(),
            Price::aZero(),
            Weight::aZeroGrams(),
            $mix->getContainerDefinition(),
            $mix->getCustomer()
        );
    }


}
