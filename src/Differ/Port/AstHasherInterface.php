<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Differ\Port;

interface AstHasherInterface
{
    /**
     * Retrieves the value from the given source.
     *
     * @param string $source the source to retrieve the value from
     *
     * @return string the value retrieved from the source
     */
    public function get(string $source): string;
}
