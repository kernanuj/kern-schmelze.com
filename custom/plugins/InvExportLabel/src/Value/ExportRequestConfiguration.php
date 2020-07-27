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
     * @var \DateTime
     */
    private $bestBeforeDate;

    /**
     * @var string
     */
    private $storagePerOrderPath;

    /**
     * @var callable
     */
    private $storagePerOrderPathNameBuilder;

    /**
     * @var string
     */
    private $senderEmailAddress;

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

    /**
     * @return \DateTime
     */
    public function getBestBeforeDate(): \DateTime
    {
        return $this->bestBeforeDate;
    }

    /**
     * @param \DateTime $bestBeforeDate
     * @return ExportRequestConfiguration
     */
    public function setBestBeforeDate(\DateTime $bestBeforeDate): ExportRequestConfiguration
    {
        $this->bestBeforeDate = $bestBeforeDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getStoragePerOrderPath(): string
    {
        return $this->storagePerOrderPath;
    }

    /**
     * @param string $storagePerOrderPath
     * @return ExportRequestConfiguration
     */
    public function setStoragePerOrderPath(string $storagePerOrderPath): ExportRequestConfiguration
    {
        $this->storagePerOrderPath = $storagePerOrderPath;
        return $this;
    }

    /**
     * @return callable
     */
    public function getStoragePerOrderPathNameBuilder(): callable
    {
        return $this->storagePerOrderPathNameBuilder;
    }

    /**
     * @param callable $storagePerOrderPathNameBuilder
     * @return ExportRequestConfiguration
     */
    public function setStoragePerOrderPathNameBuilder(callable $storagePerOrderPathNameBuilder
    ): ExportRequestConfiguration {
        $this->storagePerOrderPathNameBuilder = $storagePerOrderPathNameBuilder;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderEmailAddress(): string
    {
        return $this->senderEmailAddress;
    }

    /**
     * @param string $senderEmailAddress
     * @return ExportRequestConfiguration
     */
    public function setSenderEmailAddress(string $senderEmailAddress): ExportRequestConfiguration
    {
        $this->senderEmailAddress = $senderEmailAddress;
        return $this;
    }
}
