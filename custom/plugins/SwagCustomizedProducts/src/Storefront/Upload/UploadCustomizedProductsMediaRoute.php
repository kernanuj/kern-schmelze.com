<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Upload;

use OpenApi\Annotations as OA;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Media\Exception\FileTypeNotAllowedException;
use Shopware\Storefront\Framework\Media\StorefrontMediaUploader;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsInvalidExtensionTypeError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsMaxFileSizeExceededError;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\FileMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\ImageMediaUploadValidator;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class UploadCustomizedProductsMediaRoute extends AbstractUploadCustomizedProductsMediaRoute
{
    /**
     * @var StorefrontMediaUploader
     */
    private $storefrontMediaUploader;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateOptionRepository;

    public function __construct(
        EntityRepositoryInterface $templateOptionRepository,
        StorefrontMediaUploader $storefrontMediaUploader
    ) {
        $this->templateOptionRepository = $templateOptionRepository;
        $this->storefrontMediaUploader = $storefrontMediaUploader;
    }

    public function getDecorated(): AbstractUploadCustomizedProductsMediaRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *     path="/customized-products/upload",
     *     description="Uploads a file for a custom product",
     *     operationId="uploadCustomizedProductCustomerFile",
     *     tags={"Store API", "Customized Products"},
     *     @OA\Parameter(
     *         parameter="optionId",
     *         name="optionId",
     *         in="body",
     *         description="Id of the template option",
     *         @OA\Schema(type="string", format="uuid"),
     *     ),
     *     @OA\Parameter(
     *         parameter="file",
     *         name="file",
     *         in="body",
     *         description="The file to upload",
     *         @OA\Schema(type="file"),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Property(
     *                 property="mediaId",
     *                 type="string",
     *                 format="uuid"
     *             ),
     *             @OA\Property(
     *                 property="filename",
     *                 type="string"
     *             ),
     *             example={"mediaId": "19489f5e16e14ac8b7c1dad26a258923", "filename": "example.png"}
     *         )
     *     )
     * )
     *
     * @Route("/store-api/v{version}/customized-products/upload", name="store-api.customized-products.upload", methods={"POST"})
     */
    public function upload(Request $request, SalesChannelContext $salesChannelContext): UploadCustomizedProductsMediaRouteResponse
    {
        $optionId = $request->request->get('optionId', null);
        if ($optionId === null) {
            throw new MissingRequestParameterException('optionId');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if ($file === null) {
            throw new MissingRequestParameterException('file');
        }

        $context = $salesChannelContext->getContext();
        $option = $this->templateOptionRepository->search(new Criteria([$optionId]), $context)->first();
        if ($option === null) {
            throw new MissingRequestParameterException('optionId');
        }

        $maxFileSize = $option->getTypeProperties()['maxFileSize'];
        if ($file->getSize() > $maxFileSize * 1024 * 1024) {
            throw new SwagCustomizedProductsMaxFileSizeExceededError();
        }

        $validatorType = (new ImageMediaUploadValidator())->getType();
        if ($option->getType() === FileUpload::NAME) {
            $validatorType = (new FileMediaUploadValidator())->getType();
        }

        try {
            $mediaId = $this->storefrontMediaUploader->upload(
                $file,
                'swag_customized_products_template_storefront_upload',
                $validatorType,
                $salesChannelContext->getContext()
            );
        } catch (FileTypeNotAllowedException $exception) {
            throw new SwagCustomizedProductsInvalidExtensionTypeError();
        }

        return new UploadCustomizedProductsMediaRouteResponse($mediaId, (string) $file->getClientOriginalName());
    }
}
