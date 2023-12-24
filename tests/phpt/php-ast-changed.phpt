--TEST--
With PHP AST-Changed file is commited.

--ARGS--
check --base b2e73649 --head e7844dc6

--FILE--
<?php

include dirname(__FILE__, 3) . '/bin/ast-check-diff';

--EXPECT_EXTERNAL--
../fixture/php-ast-changed.md

