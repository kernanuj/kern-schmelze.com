<?php declare(strict_types=1);

namespace InvExportLabel\Service\Renderer;

/**
 * Class RendererRegistry
 * @package InvExportLabel\Service\Renderer
 */
final class RendererRegistry
{

    /**
     * @var RendererInterface
     */
    private $renderer = [];

    /**
     * @param string $type
     * @return RendererInterface
     */
    public function forType(string $type): RendererInterface
    {
        return $this->renderer[$type];
    }

    /**
     * @param string $type
     * @param RendererInterface $renderer
     * @return $this
     */
    public function addRenderer(string $type, RendererInterface $renderer): self
    {
        $this->renderer[$type] = $renderer;
        return $this;
    }
}
