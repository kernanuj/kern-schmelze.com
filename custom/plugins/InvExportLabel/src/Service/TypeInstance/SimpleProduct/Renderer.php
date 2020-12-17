<?php declare(strict_types=1);

namespace InvExportLabel\Service\TypeInstance\SimpleProduct;

use InvExportLabel\Service\RendererInterface;
use InvExportLabel\Value\RenderedDocument;
use InvExportLabel\Value\SourceCollection;
use Shopware\Core\Checkout\Document\Twig\DocumentTemplateRenderer;

/**
 * Class Renderer
 * @package InvExportLabel\Service\TypeInstance\SimpleProduct
 */
class Renderer implements RendererInterface
{

    /**
     * @var DocumentTemplateRenderer
     */
    private $documentTemplateRenderer;

    /**
     * SimpleProductRenderer constructor.
     * @param DocumentTemplateRenderer $documentTemplateRenderer
     */
    public function __construct(DocumentTemplateRenderer $documentTemplateRenderer)
    {
        $this->documentTemplateRenderer = $documentTemplateRenderer;
    }


    /**
     * @inheritDoc
     */
    public function render(SourceCollection $sourceCollection): RenderedDocument
    {
        $view = '@InvExportLabel/documents/product_label.html.twig';

        $html = $this->documentTemplateRenderer->render(
            $view,
            [
                'sourceCollection' => $sourceCollection
            ],
            null,
            null,
            null,
            null
        );

        return (new RenderedDocument())
            ->setHtml($html);
    }


}
