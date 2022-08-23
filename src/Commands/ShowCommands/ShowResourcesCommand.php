<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadCalendars;
use CTExport\Commands\Traits\LoadResources;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;


#[AsCommand(
    name: 'show:resources',
    description: 'Show all resources.',
    hidden: false,
)]
class ShowResourcesCommand extends ShowTableCommand
{
    use LoadResources;

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        return TableBuilder::forResources($this->loadResources());
    }
}