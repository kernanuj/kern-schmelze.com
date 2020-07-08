<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Api;

use OpenApi\Annotations as OA;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class TemplateOptionController extends AbstractController
{
    /**
     * @var TemplateOptionServiceInterface
     */
    private $optionService;

    public function __construct(TemplateOptionServiceInterface $optionService)
    {
        $this->optionService = $optionService;
    }

    /**
     * @OA\Get(
     *     path="/_action/swag-customized-products-template-option/types",
     *     description="Get all supported option types",
     *     operationId="getSupportedOptionTypes",
     *     tags={"Admin Api", "SwagCustomizedProductsActions"},
     *     @OA\Response(
     *         response="200",
     *         description="All supported option types",
     *     )
     * )
     *
     * @Route("/api/v{version}/_action/swag-customized-products-template-option/types", name="api.action.swag-customized-products-template-option.types", methods={"GET"})
     */
    public function getTypes(): JsonResponse
    {
        return new JsonResponse($this->optionService->getSupportedTypes());
    }
}
