<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Service\Content\Cms\SalesChannel;

use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CmsExtensions\Extension\CmsSectionEntityExtension;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelCmsPageLoaderScrollNavigationDecorator implements SalesChannelCmsPageLoaderInterface
{
    public const SCROLL_NAVIGATION_ASSOCIATION_PATH = 'sections.' . CmsSectionEntityExtension::SCROLL_NAVIGATION_ASSOCIATION_PROPERTY_NAME;

    /**
     * @var SalesChannelCmsPageLoaderInterface
     */
    private $inner;

    public function __construct(SalesChannelCmsPageLoaderInterface $inner)
    {
        $this->inner = $inner;
    }

    public function load(
        Request $request,
        Criteria $criteria,
        SalesChannelContext $context,
        ?array $config = null,
        ?ResolverContext $resolverContext = null
    ): EntitySearchResult {
        return $this->inner->load(
            $request,
            $this->addScrollNavigationAssociation($criteria),
            $context,
            $config,
            $resolverContext
        );
    }

    protected function addScrollNavigationAssociation(Criteria $criteria): Criteria
    {
        return $criteria->addAssociation(self::SCROLL_NAVIGATION_ASSOCIATION_PATH);
    }
}
