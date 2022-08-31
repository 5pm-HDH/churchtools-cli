<?php


namespace CTExport\Commands\Collections;


use CTApi\Exceptions\CTRequestException;
use CTApi\Models\Event;
use CTApi\Models\Service;
use CTApi\Models\Song;
use CTApi\Requests\EventRequest;
use CTApi\Requests\ServiceRequest;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class EventCollection
{
    private array $events = [];

    public function __construct(array $events)
    {
        $this->events = $events;
    }

    public function createSongTable(OutputInterface $output): SpreadsheetDataBuilder
    {
        $progressBar = new ProgressBar($output, sizeof($this->events));
        $progressBar->start();


        $tableData = $this->collectDataAndCreateSpreadsheetBuilder(function (Event $event) use ($output, $progressBar) {
            $progressBar->advance();
            try {
                $songs = $event->requestAgenda()->getSongs();

                return array_map(function (Song $song) {
                    return $song->getName();
                }, $songs);
            } catch (CTRequestException) {
                $output->writeln(" - Could not load Agenda for Event: " . $event->getName() . " (#" . $event->getId() . ")");
                return null;
            }
        });

        $progressBar->finish();
        $output->writeln("");
        return $tableData;
    }

    public function createServicePersonTable(array $serviceIds, OutputInterface $output): SpreadsheetDataBuilder
    {
        $progressBar = new ProgressBar($output, sizeof($this->events));
        $progressBar->start();

        $tableData = $this->collectDataAndCreateSpreadsheetBuilder(function (Event $event) use ($serviceIds, $progressBar) {
            $progressBar->advance();
            $names = [];

            $event = EventRequest::findOrFail((int)$event->getId()); // reload all events to get Service-Information.

            foreach ($serviceIds as $serviceId) {
                $eventService = $event->requestEventServiceWithServiceId($serviceId);
                $person = $eventService?->requestPerson();
                if (!is_null($person)) {
                    $names[] = $person->getFirstName() . " " . $person->getLastName();
                }
            }
            return $names;
        });
        $progressBar->finish();
        $output->writeln("");

        return $tableData;
    }

    public function createServiceInstrumentTable(array $serviceIds, ?ProgressBar $progressBar = null): SpreadsheetDataBuilder
    {
        return $this->collectDataAndCreateSpreadsheetBuilder(function (Event $event) use ($serviceIds, $progressBar) {
            if ($progressBar != null) {
                $progressBar->advance();
            }
            $services = [];
            foreach ($serviceIds as $serviceId) {
                $eventService = $event->requestEventServiceWithServiceId($serviceId);
                if ($eventService != null && $eventService->getPerson() != null) {
                    $serviceId = $eventService->getServiceId();
                    $services[] = ServiceRequest::find((int)$serviceId)?->getName() ?? (new Service());
                }
            }
            return $services;
        });
    }


    /**
     * DataCollector is a callback, that takes a Event as parameter and returns an array of strings.
     *
     * @param $dataCollector
     */
    private function collectDataAndCreateSpreadsheetBuilder($dataCollector): SpreadsheetDataBuilder
    {
        $data = [];
        foreach ($this->events as $event) {
            $eventKey = $this->createKeyForEvent($event);
            $collectedData = $dataCollector($event);
            if ($collectedData != null) {
                $data[$eventKey] = $collectedData;
            }
        }
        return $this->createSpreadsheetBuilderFromData($data);
    }

    private function createKeyForEvent(Event $event): string
    {
        return $event->getName() . " " . $event->getStartDate() . "(#" . $event->getId() . ")";
    }

    /**
     * Contains Data to be formatted as Table. Input-Data
     * <code>
     * [
     *    "Event A" => ["Matthew", "John"],
     *    "Event B" => ["John", "Paul"]
     * ]
     * </code>
     *
     * Will create a Table:
     * <code>
     * [
     *      ["",        "Matthew", "John", "Paul"],
     *      ["Event A", "X",       "X",     ""],
     *      ["Event B", "",        "X",     ""]
     * ]
     * </code>
     * @param array $data
     */
    private function createSpreadsheetBuilderFromData(array $data): SpreadsheetDataBuilder
    {
        return new SpreadsheetDataBuilder($data);
    }

    public static function flipTable(array $table)
    {
        $oldY = sizeof($table);
        $oldX = sizeof(end($table));

        $flippedTable = [];

        for ($newY = 0; $newY < $oldX; $newY++) {
            $newRow = [];

            for ($newX = 0; $newX < $oldY; $newX++) {
                $newRow[] = $table[$newX][$newY];
            }
            $flippedTable[] = $newRow;
        }
        return $flippedTable;
    }
}