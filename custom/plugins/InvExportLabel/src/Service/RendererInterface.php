<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Value\RenderedDocument;
use InvExportLabel\Value\SourceCollection;

/**
 * Interface RendererInterface
 * @package InvExportLabel\Service
 */
interface RendererInterface
{

    /**
     * @param SourceCollection $sourceCollection
     * @return RenderedDocument
     */
    public function render(SourceCollection $sourceCollection): RenderedDocument;
}
