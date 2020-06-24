<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceCollection;

/**
 * Interface SourceProviderInterface
 * @package InvExportLabel\Service
 */
interface SourceProviderInterface
{
    /**
     * @param ExportRequestConfiguration $configuration
     * @return SourceCollection
     */
    public function fetchSourceCollection(ExportRequestConfiguration $configuration): SourceCollection;
}
