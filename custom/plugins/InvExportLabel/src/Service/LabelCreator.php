<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use InvExportLabel\Service\Renderer\RendererRegistry;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;

/**
 * Class LabelCreator
 * @package InvExportLabel\Service
 */
class LabelCreator
{

    /**
     * @var SourceProviderInterface
     */
    private $sourceProvider;

    /**
     * @var RendererRegistry
     */
    private $rendererRegistry;

    /**
     * LabelCreator constructor.
     * @param SourceProviderInterface $sourceProvider
     * @param RendererRegistry $rendererRegistry
     */
    public function __construct(SourceProviderInterface $sourceProvider, RendererRegistry $rendererRegistry)
    {
        $this->sourceProvider = $sourceProvider;
        $this->rendererRegistry = $rendererRegistry;
    }

    /**
     * @param ExportRequestConfiguration $configuration
     * @return $this|ExportResult
     */
    public function run(
        ExportRequestConfiguration $configuration
    ): ExportResult {

        $result = new ExportResult();

        $sourceCollection = $this->sourceProvider->fetchSourceCollection($configuration);

        if (true !== $sourceCollection->hasItems()) {
            return $result->addLog('There are no items to create labels for.');
        }

        $this->generateFile($configuration, $sourceCollection, $result);

        return $result;
    }

    /**
     * @param ExportRequestConfiguration $configuration
     * @param SourceCollection $collection
     * @param ExportResult $exportResult
     *
     * @return $this
     */
    private function generateFile(
        ExportRequestConfiguration $configuration,
        SourceCollection $collection,
        ExportResult $exportResult
    ): self {
        $renderer = $this->rendererRegistry->forType($configuration->getType());

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->setIsHtml5ParserEnabled(true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->loadHtml($renderer->render($collection)->getHtml());
        $dompdf->render();

        file_put_contents($configuration->getStoragePathName(), $dompdf->output());
        $exportResult->setCreatedFile(
            new \SplFileObject($configuration->getStoragePathName())
        );
        return $this;
    }
}
