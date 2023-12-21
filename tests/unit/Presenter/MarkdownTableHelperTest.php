<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Test\Case\Presenter;

use O0h\PhpAstCheckDiff\Presenter\MarkdownTableHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MarkdownTableHelper::class)]
final class MarkdownTableHelperTest extends TestCase
{
    private MarkdownTableHelper $subject;

    protected function setUp(): void
    {
        $this->subject = new MarkdownTableHelper();
    }

    public function testRender(): void
    {
        $this->subject->setHeader(['Column 1', 'Column 2']);
        $this->subject->addRow(['Row 1 Column 1', 'Row 1 Column 2']);
        $this->subject->addRow(['Row 2 Column 1', 'Row 2 Column 2']);

        $actual = $this->subject->render();

        $expected = implode(\PHP_EOL, [
            '| Column 1 | Column 2 |',
            '| ---- | ---- |',
            '| Row 1 Column 1 | Row 1 Column 2 |',
            '| Row 2 Column 1 | Row 2 Column 2 |',
            '',
        ]);

        $this->assertSame($expected, $actual);
    }

    public function testRenderWithoutHeader(): void
    {
        $this->subject->addRow(['Row 1 Column 1', 'Row 1 Column 2']);

        $actual = $this->subject->render();

        $expected = implode(\PHP_EOL, [
            '| Row 1 Column 1 | Row 1 Column 2 |',
            '',
        ]);

        $this->assertSame($expected, $actual);
    }

    public function testRenderCountMismatch(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->subject->setHeader(['Column 1', 'Column 2']);
        $this->subject->addRow(['Row 1 Column 1']);

        $this->subject->render();
    }
}
