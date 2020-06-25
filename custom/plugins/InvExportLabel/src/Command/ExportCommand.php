<?php declare(strict_types=1);

namespace InvExportLabel\Command;

use InvExportLabel\Service\ConfigurationProvider;
use InvExportLabel\Service\LabelCreator;
use InvExportLabel\Value\MixerProductCreateConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportCommand
 * @package InvExportLabel\Command
 */
class ExportCommand extends Command
{

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->creator->run(
            $this->configurationProvider->provideDefaultSet()
        );

        foreach ($result->getLog() as $log) {
            $output->writeln($log);
        }

        $output->writeln('Generated file:'.$result->getCreatedFile()->getPathname());
    }


}
