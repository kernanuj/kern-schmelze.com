<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Api;

use OpenApi\Annotations as OA;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Swag\CustomizedProducts\Template\Message\GenerateDecisionTreeMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class TemplateController extends AbstractController
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @OA\Post(
     *      path="/_action/swag-customized-products-template/{templateId}/tree",
     *      description="Dispatch a decision tree generation message for {templateId}",
     *      operationId="dispatchDecisionTreeMessage",
     *      tags={"Admin Api", "SwagCustomizedProductsActions"},
     *     @OA\Parameter(
     *         name="{templateId}",
     *         description="The template id for which the message should be queued",
     *         in="path",
     *         required=true,
     *         allowEmptyValue=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Empty response",
     *     )
     * )
     *
     * @Route("/api/v{version}/_action/swag-customized-products-template/{templateId}/tree", name="api.action.swag-customized-products-template.tree", methods={"POST"})
     */
    public function addTreeGenerationMessageToQueue(string $templateId, Context $context): Response
    {
        $messageBus = $this->messageBus;
        $msg = new GenerateDecisionTreeMessage($templateId);

        $context->scope(Context::SYSTEM_SCOPE, static function (Context $inlineContext) use ($msg, $messageBus): void {
            $messageBus->dispatch($msg->withContext($inlineContext));
        });

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
