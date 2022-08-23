<?php


namespace CTExport\Commands;


use CTExport\Commands\Traits\LoadCalendars;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'show:calendars',
    description: 'Show all calendars.',
    hidden: false,
)]
class ShowCalendarsCommand extends AbstractCommand
{
    use LoadCalendars;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $calendars = $this->loadCalendars();
        $calendarsAsTable = array_map(function ($calendar) {
            return [$calendar->id, $calendar->name]; // TODO: Replace this Code with ChurchTools-API-Request: https://github.com/5pm-HDH/churchtools-api/issues/90
        }, $calendars);
        $table = new Table($output);
        $table->setHeaders(["Id", "Title"])
            ->setRows($calendarsAsTable);
        $table->render();

        return Command::SUCCESS;
    }
}