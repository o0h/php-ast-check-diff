<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Differ;

use O0h\PhpAstCheckDiff\Differ\Port\GitInterface;
use O0h\PhpAstCheckDiff\Value\AstDiff;
use O0h\PhpAstCheckDiff\Value\Diff;

/**
 * @phpstan-type commit array{hash: string, message: string}
 */
class DiffCollector
{
    /** @var array{base: commit, head:commit} */
    private array $commits;

    /** @var array{php: list<AstDiff>, nonPhp:list<Diff>} */
    private array $diffs;

    public function __construct(private readonly GitInterface $git, private DiffFactory $diffFactory) {}

    /**
     * Initializes the object with the provided base and head commit hashes.
     *
     * @param string $base the hash of the base commit
     * @param string $head the hash of the head commit
     *
     * @throws \InvalidArgumentException if either the base or head commit hash is invalid
     */
    public function initialize(string $base, string $head): void
    {
        foreach (compact('base', 'head') as $place => $commitHash) {
            if (!$this->git->verifyCommit($commitHash)) {
                throw new \InvalidArgumentException("{$commitHash}({$place}) is invalid");
            }
        }

        $this->commits = [
            'base' => [
                'hash' => $base,
                'message' => $this->git->getCommitLog($base),
            ],
            'head' => [
                'hash' => $head,
                'message' => $this->git->getCommitLog($head),
            ],
        ];
        $this->diffFactory->setCommitHashes($base, $head);
        $this->setCollections();
    }

    /**
     * Get the commits.
     *
     * @return array{base: commit, head: commit} the commits dict
     */
    public function getCommits(): array
    {
        return $this->commits;
    }

    /**
     * Get the PHP diffs for the changed files.
     *
     * @param bool $includeNonAstChanged Flag to include non-AST changed files. Default is true.
     *
     * @return list<AstDiff> an array of Diff objects representing the PHP diffs
     */
    public function getPhpDiffs(bool $includeNonAstChanged = true): array
    {
        if ($includeNonAstChanged) {
            return $this->diffs['php'];
        }

        return array_values(array_filter($this->diffs['php'], static fn($diff) => $diff->hasChanged()));
    }

    /**
     * Get the non-PHP diffs.
     *
     * @return array<Diff> the array of non-PHP diffs
     */
    public function getNonPhpDiffs(): array
    {
        return $this->diffs['nonPhp'];
    }

    /**
     * Set the collections of PHP and non-PHP diffs.
     */
    private function setCollections(): void
    {
        $phpDiffs = $nonPhpDiffs = [];

        $diffFiles = $this->git->getDiffFiles($this->commits['base']['hash'], $this->commits['head']['hash']);
        foreach ($diffFiles as $diffFile) {
            $isPhpFile = 'php' === pathinfo($diffFile['path'], \PATHINFO_EXTENSION);
            if ($isPhpFile) {
                $phpDiffs[] = $this->diffFactory->createForPhp($diffFile['path'], $diffFile['status']);
            } else {
                $nonPhpDiffs[] = $this->diffFactory->createForNonPhp($diffFile['path'], $diffFile['status']);
            }
        }

        $this->diffs = [
            'php' => $phpDiffs,
            'nonPhp' => $nonPhpDiffs,
        ];
    }
}
