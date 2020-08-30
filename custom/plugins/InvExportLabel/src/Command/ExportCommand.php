<?php declare(strict_types=1);

namespace InvExportLabel\Command;

use InvExportLabel\Constants;
use InvExportLabel\Service\ConfigurationProvider;
use InvExportLabel\Service\LabelCreator;
use InvExportLabel\Service\LabelSender;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\MixerProductCreateConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportCommand
 * @package InvExportLabel\Command
 */
class ExportCommand extends Command
{


    const INPUT_NAME_DAYS_BACK = 'daysBack';
    const INPUT_NAME_DAYS_FORWARD = 'daysForward';
    const INPUT_NAME_TYPE = 'type';
    const INPUT_NAME_INCLUDE_INVOICE = 'includeInvoice';
    const INPUT_NAME_UPDATE_STATUS = 'updateStatus';
    /**
     * @var string
     */
    protected static $defaultName = 'inv:export-label:export';

    /**
     * @var LabelCreator
     */
    private $creator;

    /**
     * @var ConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var LabelSender
     */
    private $sender;

    /**
     * @param LabelSender $sender
     * @return ExportCommand
     */
    public function setSender(LabelSender $sender): ExportCommand
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @param LabelCreator $creator
     * @return ExportCommand
     */
    public function setCreator(LabelCreator $creator): ExportCommand
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @param ConfigurationProvider $configurationProvider
     * @return ExportCommand
     */
    public function setConfigurationProvider(ConfigurationProvider $configurationProvider): ExportCommand
    {
        $this->configurationProvider = $configurationProvider;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->addOption(
            'daysBack',
            'db',
            InputOption::VALUE_REQUIRED,
            'Number of days back an order is considered',
            1
        );

        $this->addOption(
            'daysForward',
            'df',
            InputOption::VALUE_REQUIRED,
            'Number of days in the future an order is considered',
            1
        );

        $this->addOption(
            'type',
            't',
            InputOption::VALUE_REQUIRED,
            'The type to be created ("inv_mixer_product" only for now )',
            Constants::LABEL_TYPE_MIXER_PRODUCT
        );
        $this->addOption(
            self::INPUT_NAME_INCLUDE_INVOICE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Include invoices in creation',
            false
        );
        $this->addOption(
            self::INPUT_NAME_UPDATE_STATUS,
            null,
            InputOption::VALUE_OPTIONAL,
            'Update status after sendout',
            false
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (false) {
            $this->printOrderStateCombinations($output);
            return;
        }

        $configuration = $this->buildConfigurationFromInput($input);

        $result = $this->creator->run(
            $configuration
        );

        $this->sender->run(
            $configuration,
            $result
        );



        foreach ($result->getLog() as $log) {
            $output->writeln($log);
        }

        foreach($result->getCreatedFiles() as $createdFile) {
            $output->writeln('Generated file:' . $createdFile->getPathname());
        }
    }

    /**
     * @param OutputInterface $output
     */
    private function printOrderStateCombinations(OutputInterface $output)
    {
        foreach (Constants::allOrderStates() as $orderState) {
            foreach (Constants::allOrderTransactionStates() as $orderTransactionState) {
                foreach (Constants::allOrderDeliveryStates() as $orderDeliveryState) {
                    $option = <<<XML
                <option>
                    <id>%s</id>
                    <name>Order:%s, Transaction:%s, Delivery:%s</name>
                </option>
XML;

                    $output->writeln(
                        sprintf(
                            $option,
                            \json_encode(
                                [
                                    'order' => $orderState,
                                    'orderTransaction' => $orderTransactionState,
                                    'orderDelivery' => $orderDeliveryState
                                ]
                            ),
                            $orderState,
                            $orderTransactionState,
                            $orderDeliveryState
                        )
                    );
                }
            }
        }
    }

    /**
     * @param InputInterface $input
     * @return ExportRequestConfiguration
     * @throws \Exception
     */
    private function buildConfigurationFromInput(InputInterface $input): ExportRequestConfiguration
    {
        $configuration = $this->configurationProvider->provideDefaultSet();

        $daysBack = $input->getOption(self::INPUT_NAME_DAYS_BACK);
        $daysForward = $input->getOption(self::INPUT_NAME_DAYS_FORWARD);
        $type = $input->getOption(self::INPUT_NAME_TYPE);
        $isIncludeInvoice = (bool)$input->getOption(self::INPUT_NAME_INCLUDE_INVOICE);
        $isUpdateStatus = (bool)$input->getOption(self::INPUT_NAME_UPDATE_STATUS);

        $configuration
            ->setType($type)
            ->setIsIncludeInvoice($isIncludeInvoice)
            ->setIsUpdateStatusAfter($isUpdateStatus);

        $configuration->getSourceFilterDefinition()
            ->setOrderedAtFrom(
                (new \DateTime())->sub(new \DateInterval('P' . $daysBack . 'D'))->setTime(0, 0, 0)

            )->setOrderedAtTo(
                (new \DateTime())->add(new \DateInterval('P' . $daysForward . 'D'))->setTime(0, 0, 0)
            );

        return $configuration;
    }


}
