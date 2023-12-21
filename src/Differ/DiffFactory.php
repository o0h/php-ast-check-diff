<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Differ;

use O0h\PhpAstCheckDiff\Differ\Port\AstHasherInterface;
use O0h\PhpAstCheckDiff\Differ\Port\GitInterface;
use O0h\PhpAstCheckDiff\Value\AstDiff;
use O0h\PhpAstCheckDiff\Value\Diff;
use O0h\PhpAstCheckDiff\Value\GitStatus;

class DiffFactory
{
    /** @var array{base: string, head: string} */
    private array $commitHashes;

    public function __construct(private readonly GitInterface $git, private readonly AstHasherInterface $astHasher) {}

    /**
     * Create a Diff object for a non-PHP file.
     *
     * @param string $path the path of the non-PHP file
     * @param GitStatus $status the Git status of the file
     *
     * @return Diff the created Diff object
     */
    public function createForNonPhp(string $path, GitStatus $status): Diff
    {
        return new Diff($path, $status);
    }

    /**
     * Creates an AstDiff object for PHP source code.
     *
     * @param string $path the path of the PHP file
     * @param GitStatus $status the Git status of the file
     *
     * @return AstDiff the AstDiff object representing the differences in the AST of the PHP file
     */
    public function createForPhp(string $path, GitStatus $status): AstDiff
    {
        $base = $head = null;

        switch ($status) {
            case GitStatus::MODIFIED:
                $base = $this->getAstHash($this->commitHashes['base'], $path);
                $head = $this->getAstHash($this->commitHashes['head'], $path);

                break;

            case GitStatus::ADDED:
                $head = $this->getAstHash($this->commitHashes['head'], $path);

                break;

            case GitStatus::DELETED:
                $base = $this->getAstHash($this->commitHashes['base'], $path);

                break;
        }

        return new AstDiff($path, $status, $base, $head);
    }

    /**
     * Set the commit hashes for the base and head.
     *
     * @param string $base the commit hash for the base
     * @param string $head the commit hash for the head
     */
    public function setCommitHashes(string $base, string $head): void
    {
        $this->commitHashes['base'] = $base;
        $this->commitHashes['head'] = $head;
    }

    /**
     * Calculate the AST hash for a given commit and file path.
     *
     * @param string $commitHash the hash of the commit to get the AST hash from
     * @param string $path the path of the file to calculate the AST hash for
     *
     * @return string the calculated AST hash
     */
    private function getAstHash(string $commitHash, string $path): string
    {
        $source = $this->git->getSource($commitHash, $path);

        return $this->astHasher->get($source);
    }
}
