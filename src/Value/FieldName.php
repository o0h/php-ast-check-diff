<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Value;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class FieldName
{
    public function __construct(public readonly string $name, public readonly int $order) {}
}
