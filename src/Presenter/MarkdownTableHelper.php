<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Presenter;

class MarkdownTableHelper
{
    private const SEPARATOR = ' | ';
    private const PART_SEPARATOR = ' ---- ';

    /** @var array<scalar> */
    private array $header = [];

    /** @var list<array<null|scalar|\Stringable>> */
    private array $body = [];

    /**
     * @param array<scalar> $header
     */
    public function setHeader(array $header): void
    {
        $this->header = $header;
    }

    /**
     * @param array<null|scalar> $row
     */
    public function addRow(array $row): void
    {
        $this->body[] = $row;
    }

    /**
     * Renders the content of the table.
     *
     * @return string the rendered content of the table
     */
    public function render(): string
    {
        $content = '';
        if ($this->header) {
            $content .= $this->renderRow($this->header);
            $content .= $this->renderRow(array_fill_keys(range(0, \count($this->header) - 1), self::PART_SEPARATOR));
        }

        foreach ($this->body as $row) {
            $content .= $this->renderRow($row);
        }

        return $content;
    }

    /**
     * @param array<null|scalar|\Stringable> $row
     */
    private function renderRow(array $row): string
    {
        if ($this->header && \count($this->header) !== \count($row)) {
            throw new \UnexpectedValueException('Header and row must have the same number of elements');
        }
        $row = array_map(static fn($val) => trim((string)$val), $row);
        $content = trim(self::SEPARATOR . implode(self::SEPARATOR, $row) . self::SEPARATOR);

        return trim($content) . \PHP_EOL;
    }
}
