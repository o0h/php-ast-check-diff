<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Test\Case\Differ;

use O0h\PhpAstCheckDiff\Differ\DiffFactory;
use O0h\PhpAstCheckDiff\Differ\Port\GitInterface;
use O0h\PhpAstCheckDiff\Differ\DiffCollector;
use O0h\PhpAstCheckDiff\Value\AstDiff;
use O0h\PhpAstCheckDiff\Value\Diff;
use O0h\PhpAstCheckDiff\Value\GitStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DiffCollector::class)]
#[UsesClass(DiffFactory::class)]
final class DiffCollectorTest extends TestCase
{
    public function testInitialize(): void
    {
        $this->expectNotToPerformAssertions();

        $subject = $this->getSubject();

        $subject->initialize('commit1', 'commit2');
    }

    public function testInitializeInvalidCommitHash(): void
    {
        $git = new class extends DummyGit {
            public function verifyCommit(string $commitHash): bool
            {
                return false;
            }
        };
        $subject = $this->getSubject(git: $git);

        $this->expectException(\InvalidArgumentException::class);

        $subject->initialize('invalid', 'commit2');
    }

    /**
     * @covers \O0h\PhpAstCheckDiff\Differ\DiffCollector::getCommits
     */
    public function testGetCommits(): void
    {
        $git = new class extends DummyGit {
            public function getCommitLog(string $commitHash): string
            {
                $commits = [
                    'commit1' => 'initial commit',
                    'commit2' => 'latest commit',
                ];

                return $commits[$commitHash];
            }
        };

        $subject = $this->getSubject(git: $git);
        $subject->initialize('commit1', 'commit2');

        $actual = $subject->getCommits();

        $expect  = [
            'base' => [
                'hash' => 'commit1',
                'message' => 'initial commit',
            ],
            'head' => [
                'hash' => 'commit2',
                'message' => 'latest commit',
            ],
        ];

        $this->assertSame($expect, $actual);
    }

    public function testGetPhpDiffs(): void
    {
        $git = new class extends DummyGit {
            public function getDiffFiles(string $baseCommit, string $headCommit): array
            {
                return [
                    [
                        'path' => 'a.php',
                        'status' => GitStatus::from('M'),
                    ],
                    [
                        'path' => 'b.php',
                        'status' => GitStatus::from('M'),
                    ],
                    [
                        'path' => 'c.text',
                        'status' => GitStatus::from('M'),
                    ],
                ];
            }
        };
        $subject = $this->getSubject(git: $git);
        $subject->initialize('commit1', 'commit2');

        $actual = $subject->getPhpDiffs(true);

        $this->assertContainsOnlyInstancesOf(AstDiff::class, $actual);
        $this->assertEmpty(
            array_filter(
                $actual,
                static fn(Diff $diff) => 'php' !== pathinfo($diff->path, \PATHINFO_EXTENSION),
            )
        );
    }

    /**
     * Test case for the method `testGetPhpDiffs_astChanged`.
     *
     * @param int $expectCount the expected count of PhpDiffs
     * @param bool $includeNonAstChanged whether to include non AST-changed PhpDiffs
     * @param GitInterface $git the GitInterface instance
     */
    #[DataProvider('getPhpDiffsAstChangedProvider')]
    public function testGetPhpDiffsAstChanged(int $expectCount, bool $includeNonAstChanged, GitInterface $git, DiffFactory $diffFactory): void
    {
        $subject = $this->getSubject(git: $git, diffFactory: $diffFactory);
        $subject->initialize('commit1', 'commit2');

        $actual = $subject->getPhpDiffs($includeNonAstChanged);

        $this->assertCount($expectCount, $actual);
    }

    public static function getPhpDiffsAstChangedProvider(): \Generator
    {
        $git = new class extends DummyGit {
            public function getDiffFiles(string $baseCommit, string $headCommit): array
            {
                return
                    [
                        ['path' => 'a.php', 'status' => GitStatus::from('M')],
                        ['path' => 'b.php', 'status' => GitStatus::from('A')],
                        ['path' => 'c.php', 'status' => GitStatus::from('D')],
                    ];
            }
        };

        $diffFactory = new class ($git, new DummyAstHasher()) extends DiffFactory {
            public function createForPhp(string $path, GitStatus $status): AstDiff
            {
                return new AstDiff($path, $status, 'commit1', 'commit2');
            }
        };

        yield 'changed-files-only-exists, exclude-not-changed-files' => [3, false, $git, $diffFactory];

        $diffFactory = new class ($git, new DummyAstHasher()) extends DiffFactory {
            public function createForPhp(string $path, GitStatus $status): AstDiff
            {
                return new AstDiff($path, $status, 'same-commit', 'same-commit');
            }
        };

        yield 'not-changed-files-only-exists, exclude-not-changed-files' => [0, false, $git, $diffFactory];

        $diffFactory = new class ($git, new DummyAstHasher()) extends DiffFactory {
            public function createForPhp(string $path, GitStatus $status): AstDiff
            {
                return match ($path) {
                    'c.php' => new AstDiff($path, $status, 'commit1', 'commit2'),
                    default => new AstDiff($path, $status, 'same-commit', 'same-commit')
                };
            }
        };

        yield 'exclude-not-changed-files' => [1, false, $git, $diffFactory];

        yield 'include-not-changed-files' => [3, true, $git, $diffFactory];
    }

    public function testGetNonPhpDiffs(): void
    {
        $git = new class extends DummyGit {
            public function getDiffFiles(string $baseCommit, string $headCommit): array
            {
                return
                    [
                        ['path' => 'some.text', 'status' => GitStatus::from('M')],
                        ['path' => 'another.jpeg', 'status' => GitStatus::from('M')],
                    ];
            }
        };
        $subject = $this->getSubject(git: $git);
        $subject->initialize('commit1', 'commit2');

        $actual = $subject->getNonPhpDiffs();

        $this->assertContainsOnlyInstancesOf(Diff::class, $actual);
        $this->assertCount(2, $actual);
        $this->assertEmpty(
            array_filter(
                $actual,
                static fn(Diff $diff) => 'php' === pathinfo($diff->path, \PATHINFO_EXTENSION),
            )
        );
    }

    private function getSubject(?GitInterface $git = null, ?DiffFactory $diffFactory = null): DiffCollector
    {
        if (!$git) {
            $git = new DummyGit();
        }

        if (!$diffFactory) {
            $astHasher = new DummyAstHasher();
            $diffFactory = new DiffFactory($git, $astHasher);
        }

        return new DiffCollector($git, $diffFactory);
    }
}
