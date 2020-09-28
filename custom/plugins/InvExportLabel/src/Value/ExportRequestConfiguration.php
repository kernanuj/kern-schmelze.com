<?php declare(strict_types=1);


namespace InvExportLabel\Value;

use InvExportLabel\Constants;
use Shopware\Core\Checkout\Order\OrderEntity;

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
    private $storageFileNameLabel;

    /**
     * @var string
     */
    private $storageFileNameInvoice;

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
     * @var OrderEntity
     */
    private $order;

    /**
     * @var string[]
     */
    private $selectedTypes;

    /**
     * @var bool
     */
    private $isIncludeInvoice = false;

    /**
     * @var bool
     */
    private $isUpdateStatusAfter = false;

    /**
     * @var string
     */
    private $transitionAfterSendout;

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
    public function getStorageFileNameLabel(): string
    {
        return $this->storageFileNameLabel;
    }

    /**
     * @param string $storageFileNameLabel
     * @return ExportRequestConfiguration
     */
    public function setStorageFileNameLabel(string $storageFileNameLabel): ExportRequestConfiguration
    {
        $this->storageFileNameLabel = $storageFileNameLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabelStoragePathName(): string
    {
        return $this->storagePath . DIRECTORY_SEPARATOR . $this->storageFileNameLabel;
    }

    /**
     * @return string
     */
    public function getInvoiceStoragePathName(): string
    {
        return $this->storagePath . DIRECTORY_SEPARATOR . $this->storageFileNameInvoice;
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

    /**
     * @return OrderEntity
     */
    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    /**
     * @param OrderEntity $order
     * @return ExportRequestConfiguration
     */
    public function setOrder(OrderEntity $order): ExportRequestConfiguration
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getSelectedTypes(): array
    {
        return $this->selectedTypes;
    }

    /**
     * @param string[] $selectedTypes
     * @return ExportRequestConfiguration
     */
    public function setSelectedTypes(array $selectedTypes): ExportRequestConfiguration
    {
        foreach ($selectedTypes as $selectedType) {
            \assert(in_array($selectedType, Constants::allAvailableLabelTypes()));
        }
        $this->selectedTypes = $selectedTypes;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludeInvoice(): bool
    {
        return $this->isIncludeInvoice;
    }

    /**
     * @param bool $isIncludeInvoice
     * @return ExportRequestConfiguration
     */
    public function setIsIncludeInvoice(bool $isIncludeInvoice): ExportRequestConfiguration
    {
        $this->isIncludeInvoice = $isIncludeInvoice;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdateStatusAfter(): bool
    {
        return $this->isUpdateStatusAfter;
    }

    /**
     * @param bool $isUpdateStatusAfter
     * @return ExportRequestConfiguration
     */
    public function setIsUpdateStatusAfter(bool $isUpdateStatusAfter): ExportRequestConfiguration
    {
        $this->isUpdateStatusAfter = $isUpdateStatusAfter;
        return $this;
    }

    /**
     * @return string
     */
    public function getStorageFileNameInvoice(): string
    {
        return $this->storageFileNameInvoice;
    }

    /**
     * @param string $storageFileNameInvoice
     * @return ExportRequestConfiguration
     */
    public function setStorageFileNameInvoice(string $storageFileNameInvoice): ExportRequestConfiguration
    {
        $this->storageFileNameInvoice = $storageFileNameInvoice;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransitionAfterSendout(): string
    {
        return $this->transitionAfterSendout;
    }

    /**
     * @param string $transitionAfterSendout
     * @return ExportRequestConfiguration
     */
    public function setTransitionAfterSendout(string $transitionAfterSendout): ExportRequestConfiguration
    {
        $this->transitionAfterSendout = $transitionAfterSendout;
        return $this;
    }




}
