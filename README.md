# PHP AST Check Diff Tool

## Overview

The PHP AST Check Diff Tool is a powerful  tool tailored for enhancing and refactoring PHP applications.
This tool differentiates itself by analyzing PHP code through its Abstract Syntax Tree (AST), enabling more in-depth and meaningful code comparisons.

### Key Features
- **AST-based Diff**: Interprets PHP code as an Abstract Syntax Tree (AST) to detect nuanced differences beyond standard code-level diffs.
- **Git Integration**: Seamlessly works with Git to detect changes in PHP files, focusing on AST differences for a more comprehensive analysis.
- **Markdown Output**: Generates results in GitHub Flavored Markdown, providing clear and readable reports.

### User Benefits
- **Automated Reviews**: By focusing on AST differences, this tool automates the review process, making it less tedious and more efficient.
- **Safe Refactoring**: Facilitates safe code modifications. The tool ensures that code changes do not alter the intended behavior, thus supporting secure and reliable refactoring.

## Appendix(thanks!)
Inspiration:
The development of this tool was inspired by articles from :

- [MonotaRO Tech Blog](https://tech-blog.monotaro.com/entry/2018/09/26/142451)
- [Qiita(tetsunosukeito-san)](https://qiita.com/tetsunosukeito/items/c0e99a120414de226480)

## Usage
To use the PHP AST Check Diff Tool, follow these steps:

1. Installation:
- Run `composer require --dev o0h/ast-check-diff` to install the tool.

2. Execution:
- Execute `vendor/bin/ast-check-diff check` to perform a comparison and output a Markdown document to standard output.
- The `--head` and `--base` options can be passed via CLI to specify the source and destination of the comparison. Branch names, tags, or commit hashes can be used for this purpose.

 examples:
- `vendor/bin/ast-check-diff check ast-diff-check` : If the `--head` option is omitted, `HEAD` is implicitly specified, and if the `--base` option is omitted, the `main` branch is implicitly specified.
- `vendor/bin/ast-check-diff check ast-diff-check --base HEAD@{3} --head HEAD~` : These comparisons are made by specifying pointers.
- `vendor/bin/ast-check-diff check ast-diff-check --base cd2f816 --head 1a89b0c` : These comparisons are made by specifying specific commits.

3. Integration with GitHub Actions:
 - Integrating this tool with GitHub Actions for deep collaboration with pull requests is highly recommended.
 - Example code of workflow: [GitHub Workflow](https://github.com/o0h/php-ast-check-diff/blob/main/.github/workflows/php-ast-check-diff.yml)
 - Example of operation: [GitHub Pull Request Example](https://github.com/o0h/php-ast-check-diff/pull/5#issuecomment-1867274471)

## Notes
- This tool is intended for use with the latest PHP environments(8.3 or 8.2+).
- Includes composer.lock: The project includes a composer.lock file.

Due to the above points, it is recommended to use this tool in isolated environments such as containers or on Continuous Integration (CI) platforms, rather than directly requiring it in your project.
