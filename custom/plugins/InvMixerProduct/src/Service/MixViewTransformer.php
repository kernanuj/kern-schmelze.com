<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\DataObject\MixView;
use InvMixerProduct\Entity\Mix;
use InvMixerProduct\Entity\Mix as Subject;
use InvMixerProduct\Value\Identifier;
use InvMixerProduct\Value\Label;
use InvMixerProduct\Value\MixContainer;
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
        Mix $mix
    ): MixView {

        return new MixView(
            Identifier::fromString('333'),
            Label::aEmpty(),
            Price::aZero(),
            Weight::aZeroGrams(),
            MixContainer::aDefault()
        );
    }


}
