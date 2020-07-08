<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\CustomizedProducts\Storefront\Upload\AbstractUploadCustomizedProductsMediaRoute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated tag:v3.0.0, since 2.1.0 - Use UploadCustomizedProductsMediaRoute instead
 * @see \Swag\CustomizedProducts\Storefront\Upload\UploadCustomizedProductsMediaRoute
 *
 * @RouteScope(scopes={"storefront"})
 */
class CustomizedProductsUploadController extends StorefrontController
{
    /**
     * @var AbstractUploadCustomizedProductsMediaRoute
     */
    private $uploadRoute;

    public function __construct(AbstractUploadCustomizedProductsMediaRoute $uploadRoute)
    {
        $this->uploadRoute = $uploadRoute;
    }

    /**
     * @deprecated tag:v3.0.0, since 2.1.0 - Use UploadCustomizedProductsMediaRoute::upload instead
     * @see \Swag\CustomizedProducts\Storefront\Upload\UploadCustomizedProductsMediaRoute::upload
     *
     * @Route("/customized-products/upload", name="frontend.customized-products.upload", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function upload(
        Request $request,
        SalesChannelContext $salesChannelContext
    ): JsonResponse {
        $uploadRouteResponse = $this->uploadRoute->upload($request, $salesChannelContext);

        return new JsonResponse(
            [
                'mediaId' => $uploadRouteResponse->getMediaId(),
                'filename' => $uploadRouteResponse->getFileName(),
            ]
        );
    }
}
