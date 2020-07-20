<?php declare(strict_types=1);

namespace InvMixerProduct\Storefront\Page;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

class IndexPageLoadedEvent extends PageLoadedEvent
{
    /**
     * @var IndexPage
     */
    protected $page;

    public function __construct(IndexPage $page, SalesChannelContext $salesChannelContext, Request $request)
    {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): IndexPage
    {
        return $this->page;
    }
}
