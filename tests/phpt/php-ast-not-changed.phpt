--TEST--
With PHP AST-Non-Changed file is commited.

--ARGS--
check --base e7844dc6 --head cd2f816

--FILE--
<?php

include dirname(__FILE__, 3) . '/bin/ast-check-diff';

--EXPECT_EXTERNAL--
../fixture/php-ast-not-changed.md

