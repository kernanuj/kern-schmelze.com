<?php declare(strict_types=1);


namespace InvExportLabel\Value;

/**
 * Class ExportRequestConfiguration
 * @package InvExportLabel\Value
 */
class ExportRequestConfiguration
{


    /**
     * @var string
     */
    private $type;
    /**
     * @var SourceFilterDefinition
     */
    private $sourceFilterDefinition;

    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var string
     */
    private $storageFileName;

    /**
     * @var string[]
     */
    private $recipientEmailAddresses;

    /**
     * @var string
     */
    private $recipientEmailBody;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ExportRequestConfiguration
     */
    public function setType(string $type): ExportRequestConfiguration
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @return SourceFilterDefinition
     */
    public function getSourceFilterDefinition(): SourceFilterDefinition
    {
        return $this->sourceFilterDefinition;
    }

    /**
     * @param SourceFilterDefinition $sourceFilterDefinition
     * @return ExportRequestConfiguration
     */
    public function setSourceFilterDefinition(SourceFilterDefinition $sourceFilterDefinition
    ): ExportRequestConfiguration {
        $this->sourceFilterDefinition = $sourceFilterDefinition;
        return $this;
    }

    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    /**
     * @param string $storagePath
     * @return ExportRequestConfiguration
     */
    public function setStoragePath(string $storagePath): ExportRequestConfiguration
    {
        $this->storagePath = $storagePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getStorageFileName(): string
    {
        return $this->storageFileName;
    }

    /**
     * @param string $storageFileName
     * @return ExportRequestConfiguration
     */
    public function setStorageFileName(string $storageFileName): ExportRequestConfiguration
    {
        $this->storageFileName = $storageFileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getStoragePathName(): string
    {
        return $this->storagePath . DIRECTORY_SEPARATOR . $this->storageFileName;
    }

    /**
     * @return string[]
     */
    public function getRecipientEmailAddresses(): array
    {
        return $this->recipientEmailAddresses;
    }

    /**
     * @param string[] $recipientEmailAddresses
     * @return ExportRequestConfiguration
     */
    public function setRecipientEmailAddresses(array $recipientEmailAddresses): ExportRequestConfiguration
    {
        $this->recipientEmailAddresses = $recipientEmailAddresses;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientEmailBody(): string
    {
        return $this->recipientEmailBody;
    }

    /**
     * @param string $recipientEmailBody
     * @return ExportRequestConfiguration
     */
    public function setRecipientEmailBody(string $recipientEmailBody): ExportRequestConfiguration
    {
        $this->recipientEmailBody = $recipientEmailBody;
        return $this;
    }

}
