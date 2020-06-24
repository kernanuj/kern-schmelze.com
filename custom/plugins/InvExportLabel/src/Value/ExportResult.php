<?php declare(strict_types=1);


namespace InvExportLabel\Value;

/**
 * Class ExportResult
 * @package InvExportLabel\Value
 */
class ExportResult
{

    /**
     * @var \SplFileObject
     */
    private $createdFile;

    /**
     * @var array
     */
    private $log = [];

    /**
     * @return \SplFileObject
     */
    public function getCreatedFile(): \SplFileObject
    {
        return $this->createdFile;
    }

    /**
     * @param \SplFileObject $createdFile
     * @return ExportResult
     */
    public function setCreatedFile(\SplFileObject $createdFile): ExportResult
    {
        $this->createdFile = $createdFile;
        return $this;
    }

    /**
     * @return array
     */
    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * @param string $log
     * @return $this
     */
    public function addLog(string $log): ExportResult
    {
        $this->log[] = $log;
        return $this;
    }



}
