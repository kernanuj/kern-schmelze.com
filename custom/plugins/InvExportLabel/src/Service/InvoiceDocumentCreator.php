<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use InvExportLabel\Constants;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;
use SplFileObject;

class InvoiceDocumentCreator implements DocumentCreatorInterface
{


    /**
     * @inheritDoc
     */
    public function run(
        ExportRequestConfiguration $configuration,
        SourceCollection $sourceCollection,
        ExportResult $exportResult
    ): void {
    }


}
