<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use InvExportLabel\Constants;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;
use Shopware\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopware\Core\Checkout\Document\DocumentService;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use\SplFileObject;

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

        $context = new Context(new SystemSource());
        $documentStrings = [];
        $generatedDocuments = [];
        foreach ($sourceCollection->getOrderCollection() as $order) {
            $generatedDocument = $this->documentService->preview(
                $order->getId(),
                '',
                'invoice',
                'pdf',
                DocumentConfigurationFactory::createConfiguration([]),
                $context
            );

            $documentStrings[] = $generatedDocument->getHtml();
            $generatedDocuments[] = $generatedDocument;
        }


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
        $dompdf->loadHtml(join('', $documentStrings));
        $dompdf->render();

        file_put_contents($configuration->getInvoiceStoragePathName(), $dompdf->output());
        $exportResult->addCreatedFile(
            new SplFileObject($configuration->getInvoiceStoragePathName())
        );
        $exportResult->addCreatedFileForSendout(
            new SplFileObject($configuration->getInvoiceStoragePathName())
        );

    }



}
