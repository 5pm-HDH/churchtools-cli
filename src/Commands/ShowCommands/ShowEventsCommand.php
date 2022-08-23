<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadEvents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;


#[AsCommand(
    name: 'show:events',
    description: 'Show all events.',
    hidden: false,
)]
class ShowEventsCommand extends ShowTableCommand
{
    use LoadEvents;

    const CALENDAR_IDS = "calendar-ids";
    const OPTION_MY_EVENTS = "my-events";

    protected function configure()
    {
        parent::configure();
        $this->addOptionStartDate();
        $this->addOptionEndDate();
        $this->addArgument(self::CALENDAR_IDS, InputArgument::OPTIONAL, "List of calendar Ids.");
        $this->addOption(self::OPTION_MY_EVENTS, null, InputOption::VALUE_NONE, "Load events for logged in user.");
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $startDate = $this->getOptionStartDate($input);
        $endDate = $this->getOptionEndDate($input);
        $calendarIds = $this->getArgumentAsIntegerList($input, self::CALENDAR_IDS);
        $myEvents = $input->getOption(self::OPTION_MY_EVENTS);
        if ($myEvents) {
            return TableBuilder::forEvents($this->loadMyEvents());
        } else { // cant process calendar-ids if load my-events
            if (empty($calendarIds)) {
                return TableBuilder::forEvents($this->loadEvents($startDate, $endDate));
            } else {
                return TableBuilder::forEvents($this->loadEventsForCalendar($startDate, $endDate, $calendarIds));
            }
        }
    }

}