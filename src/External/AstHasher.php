<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\External;

use O0h\PhpAstCheckDiff\Differ\Port\AstHasherInterface;
use PhpParser\ErrorHandler\Collecting;
use PhpParser\NodeDumper;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class AstHasher implements AstHasherInterface
{
    private NodeDumper $dumper;
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->dumper = new NodeDumper();
    }

    #[\Override]
    public function get(string $source): string
    {
        $errorHandler = new Collecting();
        $ast = $this->parser->parse($source, $errorHandler);
        if (!$ast || $errorHandler->hasErrors()) {
            throw new \RuntimeException('Failed to parse code');
        }
        $astSerialized = $this->dumper->dump($ast);

        return md5($astSerialized);
    }
}
