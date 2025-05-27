<?php
namespace App;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name:        'app:ollama:batch-run',
    description: 'Run a prompt against every Ollama model whose size is within the given range.',
)]
class OllamaBatchCommand extends Command
{
    public function __construct(private readonly OllamaBatch $batch)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'prompt',
                InputArgument::REQUIRED,
                'The prompt that will be sent to every selected model.'
            )
            ->addOption(
                'min-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Minimum model size in GiB (inclusive).',
                0
            )
            ->addOption(
                'max-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum model size in GiB (inclusive).',
                1
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $prompt   = $input->getArgument('prompt');
        $minSize  = (int) $input->getOption('min-size');
        $maxSize  = (int) $input->getOption('max-size');

        if ($minSize > $maxSize) {
            $output->writeln('<error>--min-size cannot be greater than --max-size.</error>');
            return Command::FAILURE;
        }

        $this->batch->runBatch($prompt, $minSize, $maxSize);

        return Command::SUCCESS;
    }
}
