<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff;

use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTableHelper
{
    /** @var array<scalar>  */
    private array $header = [];

    /** @var list<array<scalar>>  */
    private array $body = [];

    public function __construct(private OutputInterface $output)
    {
    }

    /**
     * @param array<scalar> $header
     * @return void
     */
    public function setHeader(array $header): void
    {
        $this->header = $header;
    }

    /**
     * @param array<?scalar> $row
     * @return void
     */
    public function addRow(array $row): void
    {
        $this->body[] = $row;
    }

    public function render(): void
    {

        $this->renderRow($this->header);
        $this->renderRow(array_fill_keys(range(0, count($this->header) - 1), ' ---- '));
        foreach ($this->body as $row) {
            $this->renderRow($row);
        }
    }

    /**
     * @param array<scalar> $row
     * @return void
     */
    public function renderRow(array $row): void
    {
        $separator = ' | ';
        $row = array_map(fn ($val) => trim((string)$val), $row);
        $this->output->writeln(trim($separator . implode($separator, $row) . $separator));
    }
}
