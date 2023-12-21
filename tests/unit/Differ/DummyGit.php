<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Test\Case\Differ;

use O0h\PhpAstCheckDiff\Differ\Port\GitInterface;

class DummyGit implements GitInterface
{
    public function verifyCommit(string $commitHash): bool
    {
        return true;
    }

    public function getCommitLog(string $commitHash): string
    {
        return '';
    }

    public function getDiffFiles(string $baseCommit, string $headCommit): array
    {
        return [];
    }

    public function getSource(string $commitHash, string $path): string
    {
        return '';
    }
}
