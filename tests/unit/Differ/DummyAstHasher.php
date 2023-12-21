<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Test\Case\Differ;

use O0h\PhpAstCheckDiff\Differ\Port\AstHasherInterface;

#[\AllowDynamicProperties]
class DummyAstHasher implements AstHasherInterface
{
    public function get(string $source): string
    {
        return md5($source);
    }
}
