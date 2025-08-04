<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Test\Case\Differ;

use O0h\PhpAstCheckDiff\Differ\DiffFactory;
use O0h\PhpAstCheckDiff\Differ\Port\AstHasherInterface;
use O0h\PhpAstCheckDiff\Differ\Port\GitInterface;
use O0h\PhpAstCheckDiff\Value\GitStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DiffFactory::class)]
final class DiffFactoryTest extends TestCase
{
    public function testCreateForNonPhp(): void
    {
        $path = '/path/to/file';
        $status = GitStatus::MODIFIED;

        $subject = $this->getSubject();

        $diff = $subject->createForNonPhp($path, $status);

        $this->assertSame($path, $diff->path);
        $this->assertSame($status, $diff->status);
    }

    #[DataProvider('provideCreateForPhpCases')]
    public function testCreateForPhp(GitStatus $gitStatus, ?string $expectedBase, ?string $expectedHead): void
    {
        $git = new class extends DummyGit {
            public function getSource(string $commitHash, string $path): string
            {
                return "<?php \$hash = '{$commitHash}'; \$path = '{$path}';";
            }
        };
        $astHasher = new class extends DummyAstHasher {
            public function get(string $source): string
            {
                return $source;
            }
        };
        $subject = $this->getSubject(git: $git, astHasher: $astHasher);
        $subject->setCommitHashes('commit1', 'commit2');

        $actual = $subject->createForPhp('a.php', $gitStatus);
        $this->assertSame('a.php', $actual->path);
        $this->assertSame($gitStatus, $actual->status);
        $this->assertSame($expectedBase, $actual->base);
        $this->assertSame($expectedHead, $actual->head);
    }

    public static function provideCreateForPhpCases(): iterable
    {
        yield 'ADDED' => [GitStatus::ADDED, null, '<?php $hash = \'commit2\'; $path = \'a.php\';'];

        yield 'MODIFIED' => [GitStatus::MODIFIED, '<?php $hash = \'commit1\'; $path = \'a.php\';', '<?php $hash = \'commit2\'; $path = \'a.php\';'];

        yield 'DELETED' => [GitStatus::DELETED, '<?php $hash = \'commit1\'; $path = \'a.php\';', null];
    }

    private function getSubject(?GitInterface $git = null, ?AstHasherInterface $astHasher = null): DiffFactory
    {
        if (!$git) {
            $git = new DummyGit();
        }

        if (!$astHasher) {
            $astHasher = new DummyAstHasher();
        }

        return new DiffFactory($git, $astHasher);
    }
}
