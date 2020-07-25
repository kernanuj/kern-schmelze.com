<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Constants;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\OrderStateCombination;
use InvExportLabel\Value\OrderStateCombinationCollection;
use InvExportLabel\Value\SourceFilterDefinition;
use Shopware\Core\System\SystemConfig\SystemConfigService;
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
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * ConfigurationProvider constructor.
     * @param string $baseStorageDirectory
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(string $baseStorageDirectory, SystemConfigService $systemConfigService)
    {
        $this->baseStorageDirectory = $baseStorageDirectory;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @return ExportRequestConfiguration
     * @throws \Exception
     */
    public function provideDefaultSet(): ExportRequestConfiguration
    {


        $configuration = (new ExportRequestConfiguration())
            ->setSourceFilterDefinition(
                new SourceFilterDefinition(
                    (new \DateTime())->sub(new \DateInterval('P1D'))->setTime(0, 0, 0),
                    (new \DateTime())->setTime(0, 0, 0),
                    $this->fromConfigurationReadValidOrderStateCombinations()
                ))
            ->setBestBeforeDate(
                (new \DateTime())
                    ->add(
                        new \DateInterval(
                            sprintf(
                                'P%dM',
                                $this->systemConfigService->get(Constants::SYSTEM_CONFIG_MIXER_PRODUCT_BEST_BEFORE_MONTHS)
                            )
                        )
                    )
            )
            ->setStoragePath($this->getBaseStorageDirectory())
            ->setStorageFileName(date('Y-m-d') . '.Etiketten.pdf')
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
            )
            ->setStoragePerOrderPath(
                $this->getPerOrderStorageDirectory(Constants::LABEL_TYPE_MIXER_PRODUCT)
            )
            ->setSenderEmailAddress(
                $this->systemConfigService->get('core.mailerSettings.senderAddress')
            );

        $configuration->setStoragePerOrderPathNameBuilder(
            function (string $identifier) use ($configuration) {
                return
                    $configuration->getStoragePerOrderPath() . DIRECTORY_SEPARATOR .
                    sprintf(
                        'Bestellung.%s.Etiketten.pdf',
                        $identifier
                    );
            }
        );

        return $configuration;
    }

    /**
     * @return OrderStateCombinationCollection
     */
    private function fromConfigurationReadValidOrderStateCombinations(): OrderStateCombinationCollection
    {
        $orderStates = $this->systemConfigService->get(Constants::SYSTEM_CONFIG_MIXER_PRODUCT_FILTER_ORDER_STATE);

        $collection = new OrderStateCombinationCollection();
        foreach ($orderStates as $orderState) {

            $collection->addCombination(
                OrderStateCombination::fromConfigValue($orderState)
            );
        }
        return $collection;
    }

    /**
     * @return string
     */
    public function getBaseStorageDirectory(): string
    {
        if (!is_dir($this->baseStorageDirectory)) {
            @mkdir($this->baseStorageDirectory, 0777, true);
        }
        Assert::writable($this->baseStorageDirectory);

        return $this->baseStorageDirectory;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getPerOrderStorageDirectory(string $type): string
    {

        $directory = $this->getBaseStorageDirectory();
        $directory = $directory . DIRECTORY_SEPARATOR . 'perOrder' . DIRECTORY_SEPARATOR . $type;
        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }
        Assert::writable($directory);

        return $directory;
    }
}
