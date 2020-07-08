<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Commands;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Message\GenerateDecisionTreeMessage;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use UnexpectedValueException;
use function is_string;
use function mb_strtolower;
use function sprintf;

class GenerateDecisionTreeCommand extends Command
{
    protected static $defaultName = 'cupro-template:generate-tree';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var TemplateDecisionTreeGeneratorInterface
     */
    private $treeGenerator;

    public function __construct(
        EntityRepositoryInterface $templateRepository,
        MessageBusInterface $messageBus,
        TemplateDecisionTreeGeneratorInterface $treeGenerator
    ) {
        parent::__construct();

        $this->templateRepository = $templateRepository;
        $this->messageBus = $messageBus;
        $this->treeGenerator = $treeGenerator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Generates the decision tree for template entities')
            ->addArgument('templateId', InputArgument::REQUIRED)
            ->addOption(
                'async',
                'a',
                InputOption::VALUE_NONE,
                'Queue up a job instead of generating the decision tree directly'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        $templateId = $input->getArgument('templateId');
        $this->io = new ShopwareStyle($input, $output);

        if (! is_string($templateId)) {
            throw new UnexpectedValueException('The given template id is not a string.');
        }

        $templateId = mb_strtolower($templateId);
        if (!Uuid::isValid($templateId)) {
            throw new UnexpectedValueException('The given template id is not a valid uuid.');
        }

        if (!$this->templateExists($templateId, $context)) {
            throw new UnexpectedValueException( sprintf('No Template could be found for the given id "%s".', $templateId));
        }

        if (!$input->getOption('async')) {
            $this->generateSynchronously($templateId, $context);
        } else {
            $this->generateAsynchronously($templateId, $context);
        }

        return 0;
    }

    private function generateSynchronously(string $templateId, Context $context): void
    {
        $this->treeGenerator->generate($templateId, $context);
        $this->io->success('Tree generated successfully');
    }

    private function generateAsynchronously(string $templateId, Context $context): void
    {
        $msg = new GenerateDecisionTreeMessage($templateId);
        $msg = $msg->withContext($context);
        $this->messageBus->dispatch($msg);
        $this->io->success('Message dispatched to queue');
    }

    private function templateExists(string $templateId, Context $context): bool
    {
        return $this->templateRepository->searchIds(new Criteria([$templateId]), $context)->firstId() !== null;
    }
}
