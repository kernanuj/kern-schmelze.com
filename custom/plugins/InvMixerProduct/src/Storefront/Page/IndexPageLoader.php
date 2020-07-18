<?php declare(strict_types=1);

namespace InvMixerProduct\Storefront\Page;

use InvMixerProduct\Service\ProductListingProviderInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class IndexPageLoader
{
    /**
     * @var GenericPageLoaderInterface
     */
    private $genericLoader;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProductListingProviderInterface
     */
    private $productListingProvider;

    /**
     * ProductPageLoader constructor.
     * @param GenericPageLoaderInterface $genericLoader
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProductListingProviderInterface $productListingProvider
     */
    public function __construct(
        GenericPageLoaderInterface $genericLoader,
        EventDispatcherInterface $eventDispatcher,
        ProductListingProviderInterface $productListingProvider
    ) {
        $this->genericLoader = $genericLoader;
        $this->eventDispatcher = $eventDispatcher;
        $this->productListingProvider = $productListingProvider;
    }


    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return IndexPage
     */
    public function load(Request $request, SalesChannelContext $salesChannelContext): IndexPage
    {
        $page = $this->genericLoader->load($request, $salesChannelContext);
        $page = IndexPage::createFrom($page);

        $listingProductCollection = $this->productListingProvider->getDefaultListingProductCollection($salesChannelContext);
        $page->setProductCollection($listingProductCollection);

        $this->eventDispatcher->dispatch(
            new IndexPageLoadedEvent($page, $salesChannelContext, $request)
        );

        return $page;
    }


}
