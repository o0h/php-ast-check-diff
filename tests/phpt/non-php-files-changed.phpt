--TEST--
With non-PHP Files changed.

--ARGS--
ast-diff-check --base bb6f65e9 --head 4abb01cf

--FILE--
<?php

include dirname(__FILE__, 3) . '/bin/command';

--EXPECT_EXTERNAL--
../fixture/non-php-files-changed.md


