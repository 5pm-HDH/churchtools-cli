<?php


namespace CTExport\Commands\ExportCommands;


use CTExport\Commands\Collections\EventCollection;
use CTExport\Commands\Traits\LoadEvents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:service-person',
    description: 'Export person that are serving in the given events of calendar. Filter by service-ids.',
    hidden: false,
)]
class ExportServicePersonCommand extends ExportCommand
{
    use LoadEvents;

    const CALENDAR_IDS = "calendar_ids";
    const SERVICE_IDS = "service_ids";

    protected function configure()
    {
        parent::configure();
        $this->addArgument("" . self::CALENDAR_IDS . "", InputArgument::REQUIRED, "List of calendars-id separated by comma.");
        $this->addArgument("" . self::SERVICE_IDS . "", InputArgument::REQUIRED, "List of service-ids separated by comma.");
        $this->addOptionStartDate();
        $this->addOptionEndDate();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startDate = $this->getOptionStartDate($input);
        $endDate = $this->getOptionEndDate($input);
        $calendarIds = $this->getArgumentAsIntegerList($input, self::CALENDAR_IDS);
        $serviceIds = $this->getArgumentAsIntegerList($input, self::SERVICE_IDS);

        $events = $this->loadEventsForCalendar($startDate, $endDate, $calendarIds);

        if (empty($events)) {
            $output->writeln("0 Events loaded for Start- / End-Date and Calendars.");
            return Command::INVALID;
        }

        $output->writeln("Load Event-Data:");

        $eventCollection = new EventCollection($events);
        $songSpreadsheet = $eventCollection->createServicePersonTable($serviceIds, $output);

        $fileName = $this->createSpreadsheetPath();
        $songSpreadsheet->withCountColumn()
            ->doFlipAxes()
            ->withDataColumns()
            ->build($fileName);
        $output->writeln("Stored export to: " . $fileName);

        return parent::execute($input, $output);
    }
}