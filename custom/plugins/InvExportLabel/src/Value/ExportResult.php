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
    private $createdFiles = [];

    /**
     * @var \SplFileObject[]
     */
    private $createdFilesForSendout;

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
     * @param \SplFileObject $createdFile
     * @return ExportResult
     */
    public function addCreatedFileForSendout(\SplFileObject $createdFile): ExportResult
    {
        $this->createdFilesForSendout[] = $createdFile;
        return $this;
    }

    /**
     * @return \SplFileObject[]
     */
    public function getCreatedFilesForSendout(): array
    {
        return $this->createdFilesForSendout;
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
