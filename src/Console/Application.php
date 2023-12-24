<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Console;

use O0h\PhpAstCheckDiff\Console\Command\AstDiffCheckCommand;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('PHP AST Check Diff');
        $this->add(new AstDiffCheckCommand());
    }
}
