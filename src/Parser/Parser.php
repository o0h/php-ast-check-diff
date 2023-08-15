<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Parser;

class Parser
{

    public function __construct(private string $file)
    {
    }

    /**
     * Calculates and returns the hash value for the abstract syntax tree (AST) of the file.
     *
     * @return string The MD5 hash value of the AST.
     */
    public function getHash(): string
    {
        $ast = $this->getAst($this->file);

        return md5($ast);
    }

    /**
     * Retrieve the abstract syntax tree (AST) for a given file.
     *
     * @param string $file The path to the file for which to generate the AST.
     *
     * @return string The string representation of the generated AST.
     */
    private function getAst(string $file): string
    {
        assert(is_readable($file));

        $command = 'php ' . dirname(__DIR__, 2) . '/vendor/bin/php-parse';
        $arg = escapeshellarg($file);

        return (string)shell_exec($command . ' ' . $arg);
    }
}
