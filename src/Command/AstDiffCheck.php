<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Command;

use O0h\PhpAstCheckDiff\AstHasher;
use O0h\PhpAstCheckDiff\Driver\Git;
use O0h\PhpAstCheckDiff\MarkdownTableHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'ast-diff-check')]
class AstDiffCheck extends Command
{
    private readonly AstHasher $astHasher;
    private readonly Git $git;

    /** @var array{base: string, head: string}  */
    private array $commits = ['base' => '', 'head' => ''];
    private bool $withNoChanged = true;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->git = new Git();
        $this->astHasher = new AstHasher();
    }

    protected function configure(): void
    {
        $this
            ->addOption('base', mode: InputOption::VALUE_OPTIONAL, default: 'main')
            ->addOption('head', mode: InputOption::VALUE_OPTIONAL, default: 'HEAD')
            ->addOption('with-no-changed', mode: InputOption::VALUE_NONE)
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->withNoChanged = (bool)$input->getOption('with-no-changed');

        $base = (string)$input->getOption('base'); /** @phpstan-ignore-line */
        $head = (string)$input->getOption('head'); /** @phpstan-ignore-line */
        foreach ([$base, $head] as $commitHash) {
            if (!$this->git->verifyCommit($commitHash)) {
                $output->writeln("<error>{$commitHash} is invalid?</error>");
                return Command::INVALID;
            }
        }
        $this->commits = compact('base', 'head');

        $output->writeln("# Check diff between {$this->commits['base']}...{$this->commits['head']}");
        $helper = new MarkdownTableHelper($output);
        $helper->setHeader($this->commits);
        $helper->addRow([
            $this->git->getCommitLog($this->commits['base']),
            $this->git->getCommitLog($this->commits['head']),
        ]);
        $helper->render();

        $output->writeln('## Diff');
        $diffFiles = $this->git->getDiffFiles($this->commits['base'], $this->commits['head']);
        if (!$diffFiles) {
            $output->writeln('NO CHANGES.');
            return Command::SUCCESS;
        }

        $phpFiles = $nonPhpFiles = [];
        foreach ($diffFiles as $diffFile) {
            if (pathinfo($diffFile['path'], \PATHINFO_EXTENSION) === 'php') {
                $phpFiles[] = $diffFile;
            } else {
                $nonPhpFiles[] = $diffFile;
            }

        }
        $output->writeln('### non-PHP Files');
        if (!$nonPhpFiles) {
            $output->writeln('NO CHANGES.');
        } else {
            $helper = new MarkdownTableHelper($output);
            $header = ['filename', 'status'];
            $helper->setHeader($header);
            foreach ($nonPhpFiles as $diffFile) {
                $helper->addRow([$diffFile['path'], $diffFile['status']]);
            }
            $helper->render();
        }


        $output->writeln('### PHP Files');
        $helper = new MarkdownTableHelper($output);

        $astDiff = $this->getAstDiff($phpFiles);
        foreach ($astDiff as $row) {
            $helper->addRow($row);
        }
        $helper->render();

        return Command::SUCCESS;
    }

    /**
     * @param list<array{status: string, path: string}> $diff
     */
    private function getAstDiff(array $diff): array
    {
        $result = [];
        foreach ($diff as $fileDiff) {
            $row = [
                'path' => $fileDiff['path'],
                'status' => $fileDiff['status'],
                'base' => '---',
                'head' => '---',
                'changed' => '',
            ];

            switch ($fileDiff['status']) {
                case 'M':
                    $row['base'] = $this->getAstHash($this->commits['base'], $fileDiff['path']);
                    $row['head'] = $this->getAstHash($this->commits['head'], $fileDiff['path']);
                    break;
                case 'A':
                    $row['head'] = $this->getAstHash($this->commits['head'], $fileDiff['path']);
                    break;
                case 'D':
                    $row['base'] = $this->getAstHash($this->commits['base'], $fileDiff['path']);
                    break;
            }
            $hasChanged = $row['base'] !== $row['head'];
            if (!$this->withNoChanged && !$hasChanged) {
                continue;
            } elseif (!$hasChanged) {
                $row['changed'] = 'NO CHANGE';
            } else {
                $row['changed'] = 'CHANGED';
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Calculate the AST hash for a given commit and file path.
     *
     * @param string $commitHash The hash of the commit to get the AST hash from.
     * @param string $path The path of the file to calculate the AST hash for.
     * @return string The calculated AST hash.
     */
    private function getAstHash(string $commitHash, string $path): string
    {
        $source = $this->git->getSource($commitHash, $path);
        return $this->astHasher->get($source);
    }
}
