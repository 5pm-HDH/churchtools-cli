<?php


namespace CTExport\Commands\ExportCommands;


use CTExport\Commands\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ExportCommand extends AbstractCommand
{
    public function enableAddTemplate(): bool
    {
        return true;
    }

    protected function canSendMail(): bool
    {
        return true;
    }

    protected function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return parent::execute($input, $output);
    }
}