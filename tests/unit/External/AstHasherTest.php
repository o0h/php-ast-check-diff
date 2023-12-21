<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Test\Case\External;

use O0h\PhpAstCheckDiff\External\AstHasher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AstHasher::class)]
final class AstHasherTest extends TestCase
{
    private AstHasher $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new AstHasher();
    }

    public function testGet(): void
    {
        $source = <<<'CODE'
             <?php
             class Nanika
             {
                public function getLongString($seed): string
                {
                    return str_pad($seed, 100, '*');
                }
             }
            CODE;
        $actual = $this->subject->get($source);
        $expected = 'abcd814e2805dc8935a669bccd6671a7';
        $this->assertSame($expected, $actual);
    }

    public function testGetWithBrokenCode(): void
    {
        $invalidSource = '<?php Invalid PHP code';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse code');
        $this->subject->get($invalidSource);
    }
}
