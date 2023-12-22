--TEST--
With PHP AST-Changed file is commited.

--ARGS--
check --base bb6f6 --head cd2f8

--FILE--
<?php

include dirname(__FILE__, 3) . '/bin/command';

--EXPECT_EXTERNAL--
../fixture/mixed-changes.md

