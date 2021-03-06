<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use InvExportLabel\Constants;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;
use SplFileObject;

class LabelDocumentCreator implements DocumentCreatorInterface
{

    /**
     * @var TypeInstanceRegistry
     */
    private $typeInstanceRegistry;

    /**
     * LabelCreator constructor.
     * @param TypeInstanceRegistry $typeInstanceRegistry
     */
    public function __construct(TypeInstanceRegistry $typeInstanceRegistry)
    {
        $this->typeInstanceRegistry = $typeInstanceRegistry;
    }

    /**
     * @inheritDoc
     */
    public function run(
        ExportRequestConfiguration $configuration,
        SourceCollection $sourceCollection,
        ExportResult $exportResult
    ): void {

        if (true !== $sourceCollection->hasItems()) {
            $exportResult->addLog('There are no items to create labels for.');
            return;
        }

        $this->generateFileForAllMatchingOrders($configuration, $sourceCollection, $exportResult);
        $this->generateSeparateFilesForEachOrder($configuration, $sourceCollection, $exportResult);

    }

    /**
     * @param ExportRequestConfiguration $configuration
     * @param SourceCollection $collection
     * @param ExportResult $exportResult
     *
     * @return $this
     */
    private function generateFileForAllMatchingOrders(
        ExportRequestConfiguration $configuration,
        SourceCollection $collection,
        ExportResult $exportResult
    ): self {
        $renderer = $this->typeInstanceRegistry->forType($configuration->getType())->getRenderer();

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
        $dompdf->loadHtml($renderer->render($collection)->getHtml());
        $dompdf->render();

        file_put_contents($configuration->getLabelStoragePathName(), $dompdf->output());
        $exportResult->addCreatedFile(
            new SplFileObject($configuration->getLabelStoragePathName())
        );
        $exportResult->addCreatedFileForSendout(
            new SplFileObject($configuration->getLabelStoragePathName())
        );
        return $this;
    }

    /**
     * @param ExportRequestConfiguration $configuration
     * @param SourceCollection $collection
     * @param ExportResult $exportResult
     *
     * @return $this
     */
    private function generateSeparateFilesForEachOrder(
        ExportRequestConfiguration $configuration,
        SourceCollection $collection,
        ExportResult $exportResult
    ): self {

        foreach ($collection->getItems() as $index => $item) {

            $singleItemCollection = new SourceCollection();
            $singleItemCollection->addItem($item);

            $storagePathName = $configuration->getStoragePerOrderPathNameBuilder()(
                $item->getOrderNumber() . '_' . $item->getDisplayId() . '_' . $index
            );
            $renderer = $this->typeInstanceRegistry->forType($configuration->getType())->getRenderer();

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
            $dompdf->loadHtml($renderer->render($singleItemCollection)->getHtml());
            $dompdf->render();

            file_put_contents(
                $storagePathName,
                $dompdf->output());
            $exportResult->addCreatedFile(
                new SplFileObject($storagePathName)
            );
        }
        return $this;
    }
}
