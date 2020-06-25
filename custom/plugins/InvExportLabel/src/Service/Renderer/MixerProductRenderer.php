<?php declare(strict_types=1);

namespace InvExportLabel\Service\Renderer;

use InvExportLabel\Value\RenderedDocument;
use InvExportLabel\Value\SourceCollection;
use Shopware\Core\Checkout\Document\Twig\DocumentTemplateRenderer;

/**
 * Class MixerProductRenderer
 * @package InvExportLabel\Service\Renderer
 */
class MixerProductRenderer implements RendererInterface
{

    /**
     * @var DocumentTemplateRenderer
     */
    private $documentTemplateRenderer;

    /**
     * MixerProductRenderer constructor.
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
        $view = '@InvExportLabel/documents/mixer_product_label.html.twig';

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
