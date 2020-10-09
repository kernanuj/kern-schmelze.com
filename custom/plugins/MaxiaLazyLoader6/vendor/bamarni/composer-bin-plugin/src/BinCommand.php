<?php

namespace _PhpScoper833c86d6963f\Bamarni\Composer\Bin;

use _PhpScoper833c86d6963f\Composer\Console\Application as ComposerApplication;
use _PhpScoper833c86d6963f\Composer\Factory;
use _PhpScoper833c86d6963f\Composer\IO\IOInterface;
use _PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputInterface;
use _PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputArgument;
use _PhpScoper833c86d6963f\Symfony\Component\Console\Input\StringInput;
use _PhpScoper833c86d6963f\Symfony\Component\Console\Output\OutputInterface;
use _PhpScoper833c86d6963f\Composer\Command\BaseCommand;
use _PhpScoper833c86d6963f\Composer\Json\JsonFile;
class BinCommand extends \_PhpScoper833c86d6963f\Composer\Command\BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('bin')->setDescription('Run a command inside a bin namespace')->setDefinition([new \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputArgument('namespace', \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputArgument::REQUIRED), new \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputArgument('args', \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputArgument::REQUIRED | \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputArgument::IS_ARRAY)])->ignoreValidationErrors();
    }
    /**
     * {@inheritDoc}
     */
    public function execute(\_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputInterface $input, \_PhpScoper833c86d6963f\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $config = new \_PhpScoper833c86d6963f\Bamarni\Composer\Bin\Config($this->getComposer());
        $this->resetComposers($application = $this->getApplication());
        /** @var ComposerApplication $application */
        if ($config->binLinksAreEnabled()) {
            \putenv('COMPOSER_BIN_DIR=' . $this->createConfig()->get('bin-dir'));
        }
        $vendorRoot = $config->getTargetDirectory();
        $namespace = $input->getArgument('namespace');
        $input = new \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\StringInput(\preg_replace(\sprintf('/bin\\s+(--ansi\\s)?%s(\\s.+)/', \preg_quote($namespace, '/')), '$1$2', (string) $input, 1));
        return 'all' !== $namespace ? $this->executeInNamespace($application, $vendorRoot . '/' . $namespace, $input, $output) : $this->executeAllNamespaces($application, $vendorRoot, $input, $output);
    }
    /**
     * @param ComposerApplication $application
     * @param string              $binVendorRoot
     * @param InputInterface      $input
     * @param OutputInterface     $output
     *
     * @return int Exit code
     */
    private function executeAllNamespaces(\_PhpScoper833c86d6963f\Composer\Console\Application $application, $binVendorRoot, \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputInterface $input, \_PhpScoper833c86d6963f\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $binRoots = \glob($binVendorRoot . '/*', \GLOB_ONLYDIR);
        if (empty($binRoots)) {
            $this->getIO()->writeError('<warning>Couldn\'t find any bin namespace.</warning>');
            return 0;
            // Is a valid scenario: the user may not have setup any bin namespace yet
        }
        $originalWorkingDir = \getcwd();
        $exitCode = 0;
        foreach ($binRoots as $binRoot) {
            $exitCode += $this->executeInNamespace($application, $binRoot, $input, $output);
            \chdir($originalWorkingDir);
            $this->resetComposers($application);
        }
        return \min($exitCode, 255);
    }
    /**
     * @param ComposerApplication $application
     * @param string              $namespace
     * @param InputInterface      $input
     * @param OutputInterface     $output
     *
     * @return int Exit code
     */
    private function executeInNamespace(\_PhpScoper833c86d6963f\Composer\Console\Application $application, $namespace, \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\InputInterface $input, \_PhpScoper833c86d6963f\Symfony\Component\Console\Output\OutputInterface $output)
    {
        if (!\file_exists($namespace)) {
            \mkdir($namespace, 0777, \true);
        }
        $this->chdir($namespace);
        // some plugins require access to composer file e.g. Symfony Flex
        if (!\file_exists(\_PhpScoper833c86d6963f\Composer\Factory::getComposerFile())) {
            \file_put_contents(\_PhpScoper833c86d6963f\Composer\Factory::getComposerFile(), '{}');
        }
        $input = new \_PhpScoper833c86d6963f\Symfony\Component\Console\Input\StringInput((string) $input . ' --working-dir=.');
        $this->getIO()->writeError('<info>Run with <comment>' . $input->__toString() . '</comment></info>', \true, \_PhpScoper833c86d6963f\Composer\IO\IOInterface::VERBOSE);
        return $application->doRun($input, $output);
    }
    /**
     * {@inheritDoc}
     */
    public function isProxyCommand()
    {
        return \true;
    }
    /**
     * Resets all Composer references in the application.
     *
     * @param ComposerApplication $application
     */
    private function resetComposers(\_PhpScoper833c86d6963f\Composer\Console\Application $application)
    {
        $application->resetComposer();
        foreach ($this->getApplication()->all() as $command) {
            if ($command instanceof \_PhpScoper833c86d6963f\Composer\Command\BaseCommand) {
                $command->resetComposer();
            }
        }
    }
    private function chdir($dir)
    {
        \chdir($dir);
        $this->getIO()->writeError('<info>Changed current directory to ' . $dir . '</info>', \true, \_PhpScoper833c86d6963f\Composer\IO\IOInterface::VERBOSE);
    }
    private function createConfig()
    {
        $config = \_PhpScoper833c86d6963f\Composer\Factory::createConfig();
        $file = new \_PhpScoper833c86d6963f\Composer\Json\JsonFile(\_PhpScoper833c86d6963f\Composer\Factory::getComposerFile());
        if (!$file->exists()) {
            return $config;
        }
        $file->validateSchema(\_PhpScoper833c86d6963f\Composer\Json\JsonFile::LAX_SCHEMA);
        $config->merge($file->read());
        return $config;
    }
}
