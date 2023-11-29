<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Driver;

final class Git
{
    /**
     * Verify if a commit exists in the git repository.
     *
     * @param string $commitHash The hash of the commit to verify.
     *
     * @return bool Returns true if the commit exists, otherwise false.
     */
    public function verifyCommit(string $commitHash): bool
    {
        $verifyBranch = \shell_exec(sprintf(
            'git rev-parse --verify -q %s',
            \escapeshellarg($commitHash)
        ));
        if ($verifyBranch === null) {
            return false;
        }
        return true;
    }

    /**
     * Retrieves the commit log for a given commit hash.
     *
     * @param string $commitHash The commit hash to retrieve the log for.
     * @return string|null|false The commit log as a string. If the commit hash is invalid or no log is found, returns null.
     */
    public function getCommitLog(string $commitHash)
    {
        return shell_exec(sprintf(
            'git log --pretty=oneline -n 1 %s',
            escapeshellarg($commitHash)
        ));
    }

    /**
     * @return list<array{status: 'M'|'A'|'D', path: string}>
     */
    public function getDiffFiles($baseCommit, $headCommit) : array
    {
        $diff = shell_exec(sprintf(
            'git --no-pager diff --name-status %s..%s',
            \escapeshellarg($baseCommit),
            \escapeshellarg($headCommit),
        ));
        if (!$diff) {
            return [];
        }

        $diff = array_map(
            function ($line) {
                /** @var 'M'|'A'|'D' $status */
                [$status, $path] = explode("\t", $line);
                return compact('status', 'path');
            },
            \explode(PHP_EOL, trim($diff)),
        );

        return $diff;
    }

    public function getSource(string $commitHash, string $path): string
    {
        $source = shell_exec(sprintf('git show %s:"%s"', escapeshellarg($commitHash), $path));
        if (!$source) {
            throw new \UnexpectedValueException("Failed to load {$commitHash}:{$path}");
        }

        return $source;
    }
}
