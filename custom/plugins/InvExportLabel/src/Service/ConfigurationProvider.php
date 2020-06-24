<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Constants;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceFilterDefinition;
use Webmozart\Assert\Assert;

/**
 * Class ConfigurationProvider
 * @package InvExportLabel\Service
 */
class ConfigurationProvider
{

    /**
     * @var string
     */
    private $baseStorageDirectory;

    /**
     * ConfigurationProvider constructor.
     * @param string $baseStorageDirectory
     */
    public function __construct(string $baseStorageDirectory)
    {
        $this->baseStorageDirectory = $baseStorageDirectory;
    }

    /**
     * @return ExportRequestConfiguration
     */
    public function provideDefaultSet(): ExportRequestConfiguration
    {
        return (new ExportRequestConfiguration())
            ->setSourceFilterDefinition(
                new SourceFilterDefinition(
                    (new \DateTime())->sub(new \DateInterval('P1D'))->setTime(0, 0, 0),
                    (new \DateTime())->setTime(0, 0, 0),
                    []
                ))
            ->setStoragePath($this->getStorageDirectory())
            ->setStorageFileName(date('Y-m-d').'.Etiketten.pdf')
            ->setRecipientEmailAddresses(
                [
                    'hallo@inventivo.de'
                ]
            )
            ->setRecipientEmailBody(
                'test email body'
            )
            ->setType(
                Constants::LABEL_TYPE_MIXER_PRODUCT
            );

    }


    /**
     * @return string
     */
    public function getStorageDirectory(): string
    {
        if (!is_dir($this->baseStorageDirectory)) {
            @mkdir($this->baseStorageDirectory, 0777, true);
        }
        Assert::writable($this->baseStorageDirectory);

        return $this->baseStorageDirectory;
    }
}
