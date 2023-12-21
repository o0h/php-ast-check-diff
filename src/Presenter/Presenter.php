<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Presenter;

use O0h\PhpAstCheckDiff\Differ\DiffCollector;
use O0h\PhpAstCheckDiff\Value\AstDiff;
use O0h\PhpAstCheckDiff\Value\Diff;

/**
 * @phpstan-import-type commit from DiffCollector
 *
 * @codeCoverageIgnore
 */
final class Presenter
{
    private string $contents = '';

    /**
     * Consume the contents stored in the object and return them as a string.
     *
     * @return string the consumed contents
     */
    public function consumeContents(): string
    {
        $contents = $this->contents;
        $this->contents = '';

        return $contents;
    }

    /**
     * Process the meta contents and display them in a markdown format.
     *
     * @param array{base: commit, head: commit} $commits
     */
    public function processMetaContents(array $commits): void
    {
        $this->putln(sprintf('# Check diff between %s...%s', $commits['base']['hash'], $commits['head']['hash']));
        $this->put(\PHP_EOL);

        $helper = new MarkdownTableHelper();
        $helper->setHeader([$commits['base']['hash'], $commits['head']['hash']]);

        $helper->addRow([$commits['base']['message'], $commits['head']['message']]);

        $this->putln($helper->render());
    }

    /**
     * Process the main contents.
     *
     * @param list<Diff> $nonPhpDiffs the non-PHP diffs
     * @param list<AstDiff> $phpDiffs the PHP diffs
     */
    public function processMainContents(array $nonPhpDiffs, array $phpDiffs): void
    {
        $this->putln('## Diff');
        if (!($nonPhpDiffs + $phpDiffs)) {
            $this->putln('NO CHANGES.');
            $this->putln('');
        } else {
            $this->processDiffContents('non-PHP Files', Diff::getDisplayFields(), $nonPhpDiffs);
            $this->processDiffContents('PHP Files', AstDiff::getDisplayFields(), $phpDiffs);
        }
    }

    /**
     * Process the diff contents and display them in a markdown format.
     *
     * @param string $sectionTitle the title for the section
     * @param non-empty-list<string> $fields the fields for the table header
     * @param list<Diff> $diffs the differences to be displayed
     */
    private function processDiffContents(string $sectionTitle, array $fields, array $diffs): void
    {
        $this->putln("### {$sectionTitle}");
        $this->putln('');
        if (!$diffs) {
            $this->putln('NO CHANGES.');
            $this->putln('');

            return;
        }

        $helper = new MarkdownTableHelper();
        $helper->setHeader($fields);

        array_walk($diffs, static fn(Diff $diff) => $helper->addRow($diff->toArray()));

        $this->putln($helper->render());
    }

    /**
     * Append a line of content by appending a new line character at the end.
     *
     * @param string $content the content to be printed
     */
    private function putln(string $content): void
    {
        $this->put($content . \PHP_EOL);
    }

    /**
     * Append the given string to the contents.
     *
     * @param string $contents the string to be appended
     */
    private function put(string $contents): void
    {
        $this->contents .= $contents;
    }
}
