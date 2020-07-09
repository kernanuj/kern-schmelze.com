<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route\AbstractPriceDetailRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class CustomizedProductPriceDetailController extends StorefrontController
{
    /**
     * @var AbstractPriceDetailRoute
     */
    private $priceDetailRoute;

    public function __construct(AbstractPriceDetailRoute $priceDetailRoute)
    {
        $this->priceDetailRoute = $priceDetailRoute;
    }

    /**
     * @Route("/customized-products/price-detail", name="storefront.customized-products.price-detail", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function priceDetail(Request $request, SalesChannelContext $context): Response
    {
        $priceDetails = $this->priceDetailRoute->priceDetail($request, $context);

        return $this->renderStorefront(
            '@SwagCustomizedProducts/storefront/component/customized-products/_include/price-detail-box.html.twig',
            [
                'priceDetails' => $priceDetails,
            ]
        );
    }
}
