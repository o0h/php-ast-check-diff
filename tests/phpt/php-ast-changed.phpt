--TEST--
With PHP AST-Changed file is commited.

--ARGS--
ast-diff-check --base b2e73649 --head e7844dc6

--FILE--
<?php

include dirname(__FILE__, 3) . '/bin/command';

--EXPECT_EXTERNAL--
../fixture/php-ast-changed.md

