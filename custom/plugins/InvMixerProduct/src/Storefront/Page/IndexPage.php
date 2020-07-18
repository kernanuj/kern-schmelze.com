<?php declare(strict_types=1);

namespace InvMixerProduct\Storefront\Page;

use InvMixerProduct\Value\ListingProductCollection;
use Shopware\Storefront\Page\Page;

class IndexPage extends Page
{
    /**
     * @var ListingProductCollection
     */
    protected $productCollection;

    /**
     * @return ListingProductCollection
     */
    public function getProductCollection(): ListingProductCollection
    {
        return $this->productCollection;
    }

    /**
     * @param ListingProductCollection $productCollection
     * @return IndexPage
     */
    public function setProductCollection(ListingProductCollection $productCollection): IndexPage
    {
        $this->productCollection = $productCollection;
        return $this;
    }


}
