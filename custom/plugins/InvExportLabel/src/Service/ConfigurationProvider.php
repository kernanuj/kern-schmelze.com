<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Constants;
use InvExportLabel\Value\ExportRequestConfiguration;
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
     */
    public function provideDefaultSet(): ExportRequestConfiguration
    {


        return (new ExportRequestConfiguration())
            ->setSourceFilterDefinition(
                new SourceFilterDefinition(
                    (new \DateTime())->sub(new \DateInterval('P1D'))->setTime(0, 0, 0),
                    (new \DateTime())->setTime(0, 0, 0),
                    $this->fromConfigurationReadValidOrderStates()
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
            ->setStoragePath($this->getStorageDirectory())
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

    /**
     * @return array
     */
    private function fromConfigurationReadValidOrderStates(): array
    {
        $orderStates = $this->systemConfigService->get(Constants::SYSTEM_CONFIG_MIXER_PRODUCT_FILTER_ORDER_STATE);
        if (!is_array($orderStates)) {
            $orderStates = [$orderStates];
        }

        if (empty($orderStates)) {
            throw new \RuntimeException('Needs at least one order state to be configured');
        }
        return $orderStates;
    }
}
