<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff;

use PhpParser\NodeDumper;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class AstHasher
{
    private NodeDumper $dumper;
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->dumper = new NodeDumper();
    }

    public function get(string $source): string
    {
        $tmp = tmpfile();
        if (!$tmp) {
            throw new \RuntimeException('Failed to create tmp file to write source');
        }
        fwrite($tmp, $source);

        $code = php_strip_whitespace(stream_get_meta_data($tmp)['uri']);
        unset($tmp);

        $ast = $this->parser->parse($code);
        if (!$ast) {
            throw new \RuntimeException('Failed to parse code');
        }
        $astSerialized = $this->dumper->dump($ast);

        return md5($astSerialized);
    }
}
