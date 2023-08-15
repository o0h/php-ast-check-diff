<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        './src',
        './tests/Case'
    ]);

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PER' => true,
    ))
    ->setFinder($finder)
    ;
