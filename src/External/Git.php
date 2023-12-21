<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\External;

use O0h\PhpAstCheckDiff\Differ\Port\GitInterface;
use O0h\PhpAstCheckDiff\Value\GitStatus;

/**
 * @codeCoverageIgnore
 */
final class Git implements GitInterface
{
    #[\Override]
    public function verifyCommit(string $commitHash): bool
    {
        $verifyBranch = shell_exec(sprintf(
            'git rev-parse --verify -q %s',
            escapeshellarg($commitHash)
        ));
        if (null === $verifyBranch) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getCommitLog(string $commitHash): string
    {
        return (string)shell_exec(sprintf(
            'git log --pretty=oneline -n 1 %s',
            escapeshellarg($commitHash)
        ));
    }

    #[\Override]
    public function getDiffFiles($baseCommit, $headCommit): array
    {
        $diff = shell_exec(sprintf(
            'git --no-pager diff --name-status %s..%s',
            escapeshellarg($baseCommit),
            escapeshellarg($headCommit),
        ));
        if (!$diff) {
            return [];
        }

        return array_map(
            static function ($line) {
                [$statusFlag, $path] = explode("\t", $line);
                $status = GitStatus::from($statusFlag);

                return compact('status', 'path');
            },
            explode(\PHP_EOL, trim($diff)),
        );
    }

    #[\Override]
    public function getSource(string $commitHash, string $path): string
    {
        $source = shell_exec(sprintf('git show %s:"%s"', escapeshellarg($commitHash), $path));
        if (!$source) {
            throw new \UnexpectedValueException("Failed to load {$commitHash}:{$path}");
        }

        return $source;
    }
}
