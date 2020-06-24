<?php declare(strict_types=1);

namespace InvExportLabel\Value;

/**
 * Class RenderedDocument
 * @package InvExportLabel\Value
 */
class RenderedDocument {

    /**
     * @var string
     */
    private $html;

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return RenderedDocument
     */
    public function setHtml(string $html): RenderedDocument
    {
        $this->html = $html;
        return $this;
    }




}
