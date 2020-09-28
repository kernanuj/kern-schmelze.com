<?php

namespace InvExportLabel\Service;


use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;

/**
 * Class LabelCreator
 * @package InvExportLabel\Service
 */
interface DocumentCreatorInterface
{
    /**
     * @param ExportRequestConfiguration $configuration
     * @param SourceCollection $sourceCollection
     * @param ExportResult $exportResult
     */
    public function run(
        ExportRequestConfiguration $configuration,
        SourceCollection $sourceCollection,
        ExportResult $exportResult
    ):void;
}
