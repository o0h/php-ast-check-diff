--TEST--
With PHP AST-Non-Changed file is commited.

--ARGS--
ast-diff-check --base e7844dc6 --head cd2f816

--FILE--
<?php

include dirname(__FILE__, 3) . '/bin/command';

--EXPECT_EXTERNAL--
../fixture/php-ast-not-changed.md

