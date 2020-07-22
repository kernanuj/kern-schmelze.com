<?php declare(strict_types=1);


namespace InvExportLabel\Value;

/**
 * Class ExportResult
 * @package InvExportLabel\Value
 */
class ExportResult
{

    /**
     * @var \SplFileObject[]
     */
    private $createdFiles =[];

    /**
     * @var array
     */
    private $log = [];

    /**
     * @return \SplFileObject[]
     */
    public function getCreatedFiles(): array
    {
        return $this->createdFiles;
    }

    /**
     * @param \SplFileObject $createdFile
     * @return ExportResult
     */
    public function addCreatedFile(\SplFileObject $createdFile): ExportResult
    {
        $this->createdFiles[] = $createdFile;
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
