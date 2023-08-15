<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Command;

use O0h\PhpAstCheckDiff\Parser\Parser;
use O0h\PhpAstCheckDiff\Parser\Validation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:echo-hash')]
class EchoHashCommand extends Command
{
    protected function configure()
    {
        $this->addArgument(
            'files',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string, string> $result */
        $result = [];

        /** @var array<string> */
        $files = $input->getArgument('files');
        $files = array_unique($files);
        sort($files);

        foreach ($files as $file) {
            (new Validation($file))->isValid();
            $parser = new Parser($file);
            $result[$file] = $parser->getHash();
        }

        foreach ($result as $file => $hash) {
            $output->writeln("{$file}:\t{$hash}");
        }

        return Command::SUCCESS;
    }
}