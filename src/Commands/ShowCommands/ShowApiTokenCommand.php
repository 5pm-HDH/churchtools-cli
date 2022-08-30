<?php


namespace CTExport\Commands\ShowCommands;


use CTApi\CTConfig;
use CTExport\Commands\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'show:api-token',
    description: 'Show api-token current Session.',
    hidden: false,
)]
class ShowApiTokenCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("API-Token:");
        $output->writeln(CTConfig::getApiKey() ?? "<null>");

        return Command::SUCCESS;
    }
}