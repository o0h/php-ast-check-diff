<?php

use PhpCsFixer\Config;

$finder = PhpCsFixer\Finder::create()
    ->in([
        './bin',
        './src',
        './tests/unit',
    ]);


return (new Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
        '@PHP80Migration:risky' => true,
        '@PHP83Migration' => true,
        '@PHPUnit100Migration:risky' => true,
        'declare_strict_types' => true,
        'phpdoc_align' => ['align' => 'left'],
        'cast_spaces' => false,
        'php_unit_test_class_requires_covers' => false,
        'native_constant_invocation' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'function_declaration' => ['closure_fn_spacing' => 'none']
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ;
