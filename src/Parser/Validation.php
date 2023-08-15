<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Parser;

class Validation
{
    /** @var array<string> */
    private array $errors = [];

    public function __construct(private string $file)
    {
    }

    public function isValid(): bool
    {
        try {
            $this->validate($this->file);
        } catch (\RuntimeException $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function validate(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new \RuntimeException("{$file} is invalid file");
        }
        $isPhPScript = (function ($file): bool {
            $fp = fopen($file, 'r');
            if (!$fp) {
                throw new \RuntimeException('Can\'t open file:' . $file);
            }
            $line = (string)fgets($fp);
            return str_starts_with($line, '<?php');
        })($file);
        if (!$isPhPScript) {
            throw new \RuntimeException("{$file} is not php script");
        }
        $syntaxCheck = (string)shell_exec('php -l ' . $file);
        $isValid = trim($syntaxCheck) === "No syntax errors detected in {$file}";
        if (!$isValid) {
            throw new \RuntimeException("{$file} is an invalid php script");
        }
    }

}
