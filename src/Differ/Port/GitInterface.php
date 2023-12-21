<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Differ\Port;

use O0h\PhpAstCheckDiff\Value\GitStatus;

interface GitInterface
{
    /**
     * Verify if a commit exists in the git repository.
     *
     * @param string $commitHash the hash of the commit to verify
     *
     * @return bool returns true if the commit exists, otherwise false
     */
    public function verifyCommit(string $commitHash): bool;

    /**
     * Retrieves the commit log message for a given commit hash.
     *
     * @param string $commitHash the commit hash to retrieve the log message for
     *
     * @return string the commit log message as a string
     */
    public function getCommitLog(string $commitHash): string;

    /**
     * Retrieves the list of files that have been modified between the base commit and the head commit.
     *
     * @param string $baseCommit the base commit to compare against
     * @param string $headCommit the head commit to compare with the base commit
     *
     * @return list<array{status: GitStatus, path: string}>
     */
    public function getDiffFiles(string $baseCommit, string $headCommit): array;

    /**
     * Retrieves the source code of a file at a specific commit.
     *
     * @param string $commitHash the commit hash of the desired commit
     * @param string $path the path to the file
     *
     * @return string the source code of the file at the specified commit and path
     */
    public function getSource(string $commitHash, string $path): string;
}
