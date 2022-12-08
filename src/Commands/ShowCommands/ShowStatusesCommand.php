<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;


#[AsCommand(
    name: 'show:statuses',
    description: 'Show all statuses.',
    aliases: ['show:status'],
    hidden: false
)]
class ShowStatusesCommand extends ShowTableCommand
{
    use LoadGroups;

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        return TableBuilder::forStatuses($this->loadStatuses());
    }
}