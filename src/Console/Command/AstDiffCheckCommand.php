<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Console\Command;

use O0h\PhpAstCheckDiff\Differ\DiffCollector;
use O0h\PhpAstCheckDiff\Differ\DiffFactory;
use O0h\PhpAstCheckDiff\External\AstHasher;
use O0h\PhpAstCheckDiff\External\Git;
use O0h\PhpAstCheckDiff\Presenter\Presenter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'check')]
final class AstDiffCheckCommand extends Command
{
    private DiffCollector $diffCollector;
    private Presenter $presenter;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $git = new Git();
        $astHasher = new AstHasher();
        $diffFactory = new DiffFactory($git, $astHasher);
        $this->diffCollector = new DiffCollector($git, $diffFactory);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('base', mode: InputOption::VALUE_OPTIONAL, default: 'main')
            ->addOption('head', mode: InputOption::VALUE_OPTIONAL, default: 'HEAD')
            ->addOption('with-no-changed', mode: InputOption::VALUE_NONE)
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->presenter = new Presenter();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commitHashes = [
            'base' => (string)$input->getOption('base'), // @phpstan-ignore-line
            'head' => (string)$input->getOption('head'), // @phpstan-ignore-line
        ];

        try {
            $this->diffCollector->initialize($commitHashes['base'], $commitHashes['head']);
        } catch (\InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::INVALID;
        }

        $this->presenter->processMetaContents($this->diffCollector->getCommits());

        $nonPhpDiffs = $this->diffCollector->getNonPhpDiffs();
        $withNoChanged = (bool)$input->getOption('with-no-changed');
        $phpDiffs = $this->diffCollector->getPhpDiffs($withNoChanged);

        $this->presenter->processMainContents($nonPhpDiffs, $phpDiffs);

        $output->writeln($this->presenter->consumeContents());

        return Command::SUCCESS;
    }
}
