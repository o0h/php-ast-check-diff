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
    private const UNESCAPE_SHELL_ARG_PREFIX = '@@@@';

    public function __construct()
    {
        $gitInstalled = $this->shellExec('which git');
        if (!$gitInstalled) {
            throw new \RuntimeException('the git command is not installed');
        }
    }

    #[\Override]
    public function verifyCommit(string $commitHash): bool
    {
        $verifyBranch = shell_exec(\sprintf(
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
        return (string)shell_exec(\sprintf(
            'git log --pretty=oneline -n 1 %s',
            escapeshellarg($commitHash)
        ));
    }

    #[\Override]
    public function getDiffFiles($baseCommit, $headCommit): array
    {
        $diff = shell_exec(\sprintf(
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
        $source = $this->shellExec(
            'git show %s:"%s"',
            $commitHash,
            self::UNESCAPE_SHELL_ARG_PREFIX . $path
        );
        if (!$source) {
            throw new \UnexpectedValueException("Failed to load {$commitHash}:{$path}");
        }

        return $source;
    }

    /**
     * Executes a shell command with given arguments.
     *
     * @param string $externalCommand the placeholder string with format specifiers
     * @param string ...$args The variable number of arguments to substitute in the placeholder.
     *
     * @return null|false|string returns the output of the shell command as a string, or null if it fails
     */
    private function shellExec(string $externalCommand, string ...$args): null|false|string
    {
        if ($args) {
            $px = self::UNESCAPE_SHELL_ARG_PREFIX;
            $args = array_map(
                static fn($arg): string => str_starts_with($arg, $px) ? substr($arg, \strlen($px)) : escapeshellarg($arg),
                $args
            );
            $externalCommand = \sprintf($externalCommand, ...$args);
        }

        return shell_exec($externalCommand);
    }
}
