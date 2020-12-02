<?php

namespace _PhpScoperfd240ab1f7e6\Bamarni\Composer\Bin;

use _PhpScoperfd240ab1f7e6\Composer\Console\Application as ComposerApplication;
use _PhpScoperfd240ab1f7e6\Composer\Factory;
use _PhpScoperfd240ab1f7e6\Composer\IO\IOInterface;
use _PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputInterface;
use _PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputArgument;
use _PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\StringInput;
use _PhpScoperfd240ab1f7e6\Symfony\Component\Console\Output\OutputInterface;
use _PhpScoperfd240ab1f7e6\Composer\Command\BaseCommand;
use _PhpScoperfd240ab1f7e6\Composer\Json\JsonFile;
class BinCommand extends \_PhpScoperfd240ab1f7e6\Composer\Command\BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('bin')->setDescription('Run a command inside a bin namespace')->setDefinition([new \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputArgument('namespace', \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputArgument::REQUIRED), new \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputArgument('args', \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputArgument::REQUIRED | \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputArgument::IS_ARRAY)])->ignoreValidationErrors();
    }
    /**
     * {@inheritDoc}
     */
    public function execute(\_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputInterface $input, \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $config = new \_PhpScoperfd240ab1f7e6\Bamarni\Composer\Bin\Config($this->getComposer());
        $this->resetComposers($application = $this->getApplication());
        /** @var ComposerApplication $application */
        if ($config->binLinksAreEnabled()) {
            \putenv('COMPOSER_BIN_DIR=' . $this->createConfig()->get('bin-dir'));
        }
        $vendorRoot = $config->getTargetDirectory();
        $namespace = $input->getArgument('namespace');
        $input = new \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\StringInput(\preg_replace(\sprintf('/bin\\s+(--ansi\\s)?%s(\\s.+)/', \preg_quote($namespace, '/')), '$1$2', (string) $input, 1));
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
    private function executeAllNamespaces(\_PhpScoperfd240ab1f7e6\Composer\Console\Application $application, $binVendorRoot, \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputInterface $input, \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Output\OutputInterface $output)
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
    private function executeInNamespace(\_PhpScoperfd240ab1f7e6\Composer\Console\Application $application, $namespace, \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\InputInterface $input, \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Output\OutputInterface $output)
    {
        if (!\file_exists($namespace)) {
            \mkdir($namespace, 0777, \true);
        }
        $this->chdir($namespace);
        // some plugins require access to composer file e.g. Symfony Flex
        if (!\file_exists(\_PhpScoperfd240ab1f7e6\Composer\Factory::getComposerFile())) {
            \file_put_contents(\_PhpScoperfd240ab1f7e6\Composer\Factory::getComposerFile(), '{}');
        }
        $input = new \_PhpScoperfd240ab1f7e6\Symfony\Component\Console\Input\StringInput((string) $input . ' --working-dir=.');
        $this->getIO()->writeError('<info>Run with <comment>' . $input->__toString() . '</comment></info>', \true, \_PhpScoperfd240ab1f7e6\Composer\IO\IOInterface::VERBOSE);
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
    private function resetComposers(\_PhpScoperfd240ab1f7e6\Composer\Console\Application $application)
    {
        $application->resetComposer();
        foreach ($this->getApplication()->all() as $command) {
            if ($command instanceof \_PhpScoperfd240ab1f7e6\Composer\Command\BaseCommand) {
                $command->resetComposer();
            }
        }
    }
    private function chdir($dir)
    {
        \chdir($dir);
        $this->getIO()->writeError('<info>Changed current directory to ' . $dir . '</info>', \true, \_PhpScoperfd240ab1f7e6\Composer\IO\IOInterface::VERBOSE);
    }
    private function createConfig()
    {
        $config = \_PhpScoperfd240ab1f7e6\Composer\Factory::createConfig();
        $file = new \_PhpScoperfd240ab1f7e6\Composer\Json\JsonFile(\_PhpScoperfd240ab1f7e6\Composer\Factory::getComposerFile());
        if (!$file->exists()) {
            return $config;
        }
        $file->validateSchema(\_PhpScoperfd240ab1f7e6\Composer\Json\JsonFile::LAX_SCHEMA);
        $config->merge($file->read());
        return $config;
    }
}
