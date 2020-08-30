<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use InvExportLabel\Constants;
use InvExportLabel\Helper\Dom;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;
use Shopware\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopware\Core\Checkout\Document\DocumentService;
use Shopware\Core\Checkout\Document\GeneratedDocument;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use SplFileObject;

class InvoiceDocumentCreator implements DocumentCreatorInterface
{


    /**
     * @var DocumentService
     */
    private $documentService;

    /**
     * InvoiceDocumentCreator constructor.
     * @param DocumentService $documentService
     */
    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }


    /**
     * @inheritDoc
     */
    public function run(
        ExportRequestConfiguration $configuration,
        SourceCollection $sourceCollection,
        ExportResult $exportResult
    ): void {

        if(true !== $configuration->isIncludeInvoice()){
            $exportResult->addLog('Invoice creation skipped');
            return;
        }
        $context = new Context(new SystemSource());
        $generatedDocuments = $this->generateDocuments($sourceCollection, $context);
        $generatedPdf = $this->generatePdf($generatedDocuments, $configuration);

        $exportResult->addCreatedFile(
            $generatedPdf
        );
        $exportResult->addCreatedFileForSendout(
            $generatedPdf
        );

    }

    /**
     * @param SourceCollection $sourceCollection
     * @param Context $context
     * @return GeneratedDocument[]
     */
    private function generateDocuments(SourceCollection $sourceCollection, Context $context): array
    {
        $generatedDocuments = [];
        foreach ($sourceCollection->getOrderCollection() as $index => $order) {
            $generatedDocument = $this->documentService->preview(
                $order->getId(),
                '',
                'invoice',
                'pdf',
                DocumentConfigurationFactory::createConfiguration([]),
                $context
            );

            $generatedDocuments[] = $generatedDocument;

            if(count($generatedDocuments) > 2) {
                break;
            }
        }

        return $generatedDocuments;
    }

    /**
     * @param array $generatedDocuments
     * @param ExportRequestConfiguration $configuration
     * @return SplFileObject
     */
    private function generatePdf(
        array $generatedDocuments,
        ExportRequestConfiguration $configuration): \SplFileObject
    {
        $documentStrings = [];
        foreach ($generatedDocuments as $generatedDocument) {
            $documentStrings[] = $generatedDocument->getHtml();
        }

        $mergedDocumentAsString = Dom::mergeHtmlStrings($documentStrings);
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->setIsHtml5ParserEnabled(true);
        $dompdf = new Dompdf($options);
        $contxt = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        $dompdf->setHttpContext($contxt);
        $dompdf->setPaper(Constants::LABEL_PDF_PAPER_SIZE, 'portrait');
        $dompdf->loadHtml($mergedDocumentAsString);
        $dompdf->render();

        file_put_contents($configuration->getInvoiceStoragePathName(), $dompdf->output());

        return new SplFileObject($configuration->getInvoiceStoragePathName());
    }


}
