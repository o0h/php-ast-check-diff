<?php

declare(strict_types=1);

const CHECK_PHP_VERSION = 80;

if (!extension_loaded('ast')) {
    abort('php-ast is required, but not installed.Abort.');
}
if ($argc < 2) {
    abort('Please input the base branch or commit hash.');
}
$baseBranch = $argv[1];
$headBranch = $argv[2] ?? 'HEAD';

foreach ([$baseBranch, $headBranch] as $branch) {
    $verifyBranch = shell_exec(sprintf(
        'git rev-parse --verify -q %s',
        escapeshellarg($branch)
    ));
    if ($verifyBranch === null) {
        abort("Branch '{$branch}' is ambigous.");
    }
}
println("# Check diff between {$baseBranch}...{$headBranch}");

printMarkdownRowHeader([$baseBranch, $headBranch]);

$baseCommit = shell_exec(sprintf(
    'git log --pretty=oneline -n 1 %s',
    escapeshellarg($baseBranch)
));
$headCommit = shell_exec(sprintf(
    'git log --pretty=oneline -n 1 %s',
    escapeshellarg($headBranch)
));
printMarkdownRow([$baseCommit, $headCommit]);

println('## Diff');
$hasUncommitted = filter_var(shell_exec('git status --porcelain|wc -l'), FILTER_VALIDATE_INT) > 0;

$phpDiffFiles = shell_exec(sprintf(
    'git --no-pager diff --name-status %s..%s -- "*.php"',
    escapeshellarg($baseBranch),
    escapeshellarg($headBranch)
));
if (!$phpDiffFiles) {
    println('No differences were found in the PHP files. Exiting.');
    exit(0);
}

$diffFiles = (string)shell_exec(sprintf(
    'git --no-pager diff --name-status %s..%s -- ":(exclude)*.php"',
    escapeshellarg($baseBranch),
    escapeshellarg($headBranch)
));
$diffFiles = array_filter(explode(PHP_EOL, $diffFiles));
if ($diffFiles) {
    println('### non-PHP Files');
    $header = ['filename', 'status'];
    printMarkdownRowHeader($header);
    foreach ($diffFiles as $diffFile) {
        [$status, $path] = explode("\t", $diffFile);
        printMarkdownRow([$path, $status]);
    }
}

println('### PHP Files');
$phpDiffFiles = array_filter(explode(PHP_EOL, trim($phpDiffFiles)));

$hashMap = [];
foreach ($phpDiffFiles as $diffFile) {
    [$status, $path] = explode("\t", $diffFile);
    $datum = [
        'status' => $status,
        'changed' => true,
        ...array_fill_keys([$baseBranch, $headBranch], '')
    ];

    switch ($status) {
        case 'M':
            $datum[$headBranch] = getAstHash($headBranch, $path);
            $datum[$baseBranch] = getAstHash($baseBranch, $path);
            break;
        case 'A':
            $datum[$headBranch] = getAstHash($headBranch, $path);
            break;
        case 'D':
            $datum[$baseBranch] = getAstHash($baseBranch, $path);
            break;
    }
    if ($datum[$baseBranch] === $datum[$headBranch]) {
        $datum['changed'] = false;
    }

    $hashMap[$path] = $datum;
}

$header = ['filename', 'status', 'BASE', 'HEAD', 'ast-changed'];
printMarkdownRowHeader($header);

foreach ($hashMap as $fileName => $row) {
    printMarkdownRow([
        $fileName,
        $row['status'],
        $row[$baseBranch],
        $row[$headBranch],
        $row['changed'] ? '' : 'NO CHANGE',
    ]);
}

// obtained from: https://github.com/nikic/php-ast/blob/v1.1.0/util.php
function astDump($ast): string
{
    if (is_string($ast)) {
        return '"' . $ast . '"';
    } elseif ($ast === null) {
        return 'null';
    } elseif (is_scalar($ast)) {
        return (string)$ast;
    }

    $dump = ast\get_kind_name($ast->kind);
    foreach ($ast->children as $name => $child) {
        if ($name === 'docComment') {
            continue;
        }
        $dump .= "\n\t\$i: " . str_replace("\n", "\n\t", astDump($child));
    }

    return $dump;
}

/**
 * Get the AST hash for the given branch and file path.
 *
 * @param string $branch The name of the branch.
 * @param string $path The path to the file.
 * @return string The MD5 hash of the AST dump.
 */
function getAstHash(string $branch, string $path)
{
    $source = shell_exec(sprintf('git show %s:"%s"', escapeshellarg($branch), $path));
    $ast = \ast\parse_code($source, CHECK_PHP_VERSION);
    return md5(astDump($ast));

}

/**
 * Abort the execution and display an error message.
 *
 * @param string $errorMessage The error message to display.
 * @return void
 */
function abort(string $errorMessage)
{
    fwrite(STDERR, $errorMessage);
    exit(1);
}

/**
 * Print a message to the standard output.
 *
 * @param string $message The message to be printed.
 * @return void
 */
function println(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

/**
 * Print the markdown row header.
 *
 * @param array $header The array of header elements.
 * @return void
 */
function printMarkdownRowHeader(array $header): void
{
    printMarkdownRow($header);
    printMarkdownRow(array_fill_keys(range(0, count($header) - 1), ' ---- '));
}

/**
 * Print a row in Markdown table format.
 *
 * @param array $row An array containing the values of each cell in the row.
 * @return void
 */
function printMarkdownRow(array $row): void
{
    $separator = ' | ';
    $row = array_map('trim', $row);
    println(trim($separator . implode($separator, $row) . $separator));
}
