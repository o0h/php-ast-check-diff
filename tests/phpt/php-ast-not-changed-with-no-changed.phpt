--TEST--
With PHP AST-Non-Changed file is commited and with-no-changed mode.

--ARGS--
check --base e7844dc6 --head cd2f816 --with-no-changed

--FILE--
<?php

include dirname(__FILE__, 3) . '/bin/command';

--EXPECT_EXTERNAL--
../fixture/php-ast-not-changed-with-no-changed.md

