<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Value;

enum GitStatus: string
{
    case MODIFIED = 'M';

    case ADDED = 'A';

    case DELETED = 'D';
}
