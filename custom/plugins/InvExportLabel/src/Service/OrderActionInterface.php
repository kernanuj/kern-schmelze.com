<?php

namespace InvExportLabel\Service;


use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;

/**
 * Class OrderSourceMarker
 * @package InvExportLabel\Service
 */
interface OrderActionInterface
{
    /**
     * @param ExportRequestConfiguration $exportRequestConfiguration
     * @param SourceCollection $sourceCollection
     * @param ExportResult $exportResult
     */
    public function run(
        ExportRequestConfiguration $exportRequestConfiguration,
        SourceCollection $sourceCollection,
        ExportResult $exportResult
    );
}
