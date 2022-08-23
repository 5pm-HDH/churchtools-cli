<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadCalendars;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;


#[AsCommand(
    name: 'show:calendars',
    description: 'Show all calendars.',
    hidden: false,
)]
class ShowCalendarsCommand extends ShowTableCommand
{
    use LoadCalendars;

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        return TableBuilder::forCalendars($this->loadCalendars());
    }
}