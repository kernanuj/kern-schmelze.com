<?php

namespace Sendcloud\Shipping\Controller\API\Frontend;

use Sendcloud\Shipping\Entity\ServicePoint\ServicePointEntityRepository;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ServicePointController
 *
 * @package Sendcloud\Shipping\Controller\API\Frontend
 */
class ServicePointController extends AbstractController
{
    /**
     * @var ServicePointEntityRepository
     */
    private $servicePointRepository;

    /**
     * ServicePointController constructor.
     *
     * @param ServicePointEntityRepository $servicePointRepository
     */
    public function __construct(ServicePointEntityRepository $servicePointRepository)
    {
        $this->servicePointRepository = $servicePointRepository;
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(path="/api/v{version}/sendcloud/servicepoint/save", name="api.sendcloud.servicepoint.save",
     *     defaults={"auth_required"=false}, methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonApiResponse
     * @throws InconsistentCriteriaIdsException
     */
    public function saveServicePointInfo(Request $request): JsonApiResponse
    {
        $success = false;
        $customerNumber = $request->get('customerNumber');
        $servicePointInfo = json_decode($request->getContent(), true);
        if ($customerNumber && $servicePointInfo) {
            $this->servicePointRepository->saveServicePoint($customerNumber, $servicePointInfo);
            $success = true;
        }

        return new JsonApiResponse(['success' => $success]);
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route(path="/api/v{version}/sendcloud/servicepoint", name="api.sendcloud.servicepoint",
     *     defaults={"auth_required"=false}, methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonApiResponse
     * @throws InconsistentCriteriaIdsException
     */
    public function getServicePointInfo(Request $request): JsonApiResponse
    {
        $servicePointInfo = [];
        $customerNumber = $request->get('customerNumber');
        if ($customerNumber) {
            $servicePointEntity = $this->servicePointRepository->getServicePointByCustomerNumber($customerNumber);
            $servicePointInfo = $servicePointEntity ? json_decode($servicePointEntity->get('servicePointInfo'), true) : [];
        }

        return new JsonApiResponse(['servicePointInfo' => $servicePointInfo]);
    }
}
