<?php


namespace CTExport\Commands\ShowCommands;


use CTApi\Models\Event;
use CTApi\Models\GroupMember;
use CTApi\Models\Person;
use CTApi\Models\Resource;
use CTApi\Models\ResourceBooking;
use CTApi\Models\Song;
use CTExport\Commands\Collections\SpreadsheetTableBuilder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class TableBuilder
{
    private ?ProgressBar $progressBar;
    private bool $showProgressBar = false;
    private array $tableRowBuffer = [];

    /**
     * TableBuilder constructor.
     * @param array $columnNames Array of strings with column-names
     * @param array $tableData Array with models to display in table
     * @param $rowCollector Callback that converts a model from tableData-Array to a array-row ["colVal1", "colVal2", "colVal3"]
     * @param ?callable $rowFilter Filter-callback that checks if model should be included in the Table.
     * @param ?callable $rowSorter Sort-callback to table data. Row-Array is given in (not model).
     */
    private function __construct(
        private array $columnNames,
        private array $tableData,
        private $rowCollector,
        private $rowFilter = null,
        private $rowSorter = null
    )
    {
        // Filter Rows
        if (isset($this->rowFilter)) {
            $this->tableData = array_values(array_filter($this->tableData, $this->rowFilter));
        }
    }

    public function withProgressBar(): TableBuilder
    {
        $this->showProgressBar = true;
        return $this;
    }


    public function exportToSpreadsheet(string $spreadsheetPath)
    {
        $rows = $this->createTableRowsLazy();
        $excelBuilder = new SpreadsheetTableBuilder($this->columnNames, $rows);
        $excelBuilder->build($spreadsheetPath);
    }

    /**
     * Build table and render it to console-output.
     * @param OutputInterface $output
     */
    public function build(OutputInterface $output)
    {
        // Create ProgressBar
        $progressBar = ($this->showProgressBar ? new ProgressBar($output) : null);

        // Get TableRows
        $rows = $this->createTableRowsLazy($progressBar);
        $output->writeln(""); //Break Line after ProgressBar

        $table = new Table($output);
        $table->setHeaders($this->columnNames)
            ->setRows($rows);
        $table->render();
    }

    /**
     * Create Table-Row-Lazy. If table-rows are already calculated it returns the buffered table rows (<code>$this->tableRowBuffer</code>)
     * @param ProgressBar|null $progressBar Only update progress-bar if it is present.
     * @return array [ ["val1", "val2"], ["val11", "val22"], ... ]
     */
    private function createTableRowsLazy(?ProgressBar $progressBar = null): array
    {
        if (empty($this->tableRowBuffer)) {
            $rows = [];

            // Process Rows
            if (isset($progressBar)) {
                $progressBar->setMaxSteps(sizeof($this->tableData));
            }

            foreach ($this->tableData as $tableRow) {
                $rowCollectorMethod = $this->rowCollector;
                $rows[] = $rowCollectorMethod($tableRow);
                if (isset($progressBar)) {
                    $progressBar->advance();
                }
            }

            if (isset($progressBar)) {
                $progressBar->finish();
            }

            // Sort-Table if TableSorter is present
            if (isset($this->rowSorter)) {
                usort($rows, $this->rowSorter);
            }

            $this->tableRowBuffer = $rows;
        }

        return $this->tableRowBuffer;
    }

    /**
     * Create TableBuilder for Calendars
     * @param array $calendarModels Collection of Calendars
     * @return TableBuilder
     */
    public static function forCalendars(array $calendarModels): TableBuilder
    {
        return new TableBuilder(
            ["Id", "Name"],
            $calendarModels,
            function ($calendarModel) {
                return [$calendarModel->getId(), $calendarModel->getName()];
            }
        );
    }

    public static function forResources(array $resourceModels): TableBuilder
    {
        return new TableBuilder(
            ["Id", "Name", "Resource-Type Id", "Resource-Type Name"],
            $resourceModels,
            function (Resource $resourceModel) {
                return [$resourceModel->getId(), $resourceModel->getName(), $resourceModel->getResourceType()?->getId(), $resourceModel->getResourceType()?->getName()];
            },
            null,
            function ($rowA, $rowB) {
                return strcmp($rowA[2], $rowB[2]); // compare Resource-Type-Id
            }
        );
    }

    public static function forPersons($loadResources): TableBuilder
    {
        return new TableBuilder(
            ["Id", "First name", "Last name", "Birthday"],
            $loadResources,
            function (Person $person) {
                return [
                    $person->getId(),
                    $person->getFirstName(),
                    $person->getLastName(),
                    $person->getBirthday()
                ];
            },
            null,
            function ($rowA, $rowB) {
                return strcmp($rowA[0], $rowB[0]);
            }
        );
    }


    public static function forGroupMembers(array $groupMemberModels)
    {
        return new TableBuilder(
            ["Person Id", "Person first name", "Person last name", "Member start date", "Comment", "Group Role Id", "Waitinglist position"],
            $groupMemberModels,
            function (GroupMember $groupMember) {
                return [
                    $groupMember->getPerson()?->getId(),
                    $groupMember->getPerson()?->getFirstName(),
                    $groupMember->getPerson()?->getLastName(),
                    $groupMember->getMemberStartDate(),
                    $groupMember->getComment(),
                    $groupMember->getGroupTypeRoleId(),
                    $groupMember->getWaitinglistPosition()
                ];
            },
            null,
            function ($rowA, $rowB) {
                return strcmp($rowA[5], $rowB[5]); // sort by group role id
            }
        );
    }

    public static function forEvents(array $eventModels): TableBuilder
    {
        return new TableBuilder(
            ["Id", "Name", "Start-date", "End-date"],
            $eventModels,
            function (Event $eventModel) {
                return [
                    $eventModel->getId(),
                    $eventModel->getName(),
                    $eventModel->getStartDate(),
                    $eventModel->getEndDate()
                ];
            },
            null,
            function ($rowA, $rowB) {
                return strcmp($rowA[2], $rowB[2]); // compare Start-Date
            }
        );
    }

    public static function forSongs(array $songModels): TableBuilder
    {
        return new TableBuilder(
            ["Id", "Name", "Ccli", "Category Id", "Category Name"],
            $songModels,
            function (Song $songModel) {
                return [
                    $songModel->getId(),
                    $songModel->getName(),
                    $songModel->getCcli(),
                    $songModel->getCategory()?->getId(),
                    $songModel->getCategory()?->getName(),
                ];
            },
            null,
            function ($rowA, $rowB) {
                return strcmp($rowA[1], $rowB[1]); // sort by title
            }
        );
    }

    public static function forBookings(array $bookingResources): TableBuilder
    {
        return new TableBuilder(
            ["Id", "Caption", "Requester Id", "Requester Name", "Start-date", "End-date"],
            $bookingResources,
            function (ResourceBooking $booking) {
                return [
                    $booking->getId(),
                    $booking->getCaption(),
                    $booking->getPersonId(),
                    $booking->requestPerson()?->getFirstName() . " " . $booking->requestPerson()?->getLastName(),
                    $booking->getStartDate(),
                    $booking->getEndDate()
                ];
            },
            null,
            function ($rowA, $rowB) {
                return strcmp($rowA[4], $rowB[4]);
            }
        );
    }


    /**
     * Create TableBuilder for Services
     * @param array $serviceModels Collection of Services
     * @param array $filterServiceGroupIds Filter only services from service group
     * @return TableBuilder
     */
    public static function forServices(array $serviceModels, array $filterServiceGroupIds): TableBuilder
    {
        return new TableBuilder(
            ["Service Id", "Service Name", "Service-Group Id", "Service-Group Name"],
            $serviceModels,
            function ($serviceModel) {
                return [
                    $serviceModel->getId(),
                    $serviceModel->getName(),
                    $serviceModel->requestServiceGroup()?->getId(),
                    $serviceModel->requestServiceGroup()?->getName()
                ];
            },
            function ($serviceModel) use ($filterServiceGroupIds) {
                if (!empty($filterServiceGroupIds)) {
                    return in_array($serviceModel->getServiceGroupId(), $filterServiceGroupIds);
                } else {
                    return true;
                }
            },
            function ($tableRowA, $tableRowB) {
                return strcmp($tableRowA[3], $tableRowB[3]); // index 3 = ServiceGroupName
            }
        );
    }


    public static function forGroups(array $groupModels)
    {
        return new TableBuilder(
            ["Id", "Name"],
            $groupModels,
            function ($groupModel) {
                return [
                    $groupModel->getId(),
                    $groupModel->getName()
                ];
            }
        );
    }

    public function exportTableRowsToJson(string $jsonPath)
    {
        $rows = $this->createTableRowsLazy();
        $columnNames = $this->columnNames;
        $tableWithKeys = array_map(function ($row) use ($columnNames) {
            $index = 0;
            $rowWithKeys = [];
            foreach ($columnNames as $columnName) {
                $jsonKey = $this->createJsonKey($columnName);

                $rowWithKeys[$jsonKey] = $row[$index];
                $index++;
            }
            return $rowWithKeys;
        }, $rows);

        $jsonContent = json_encode($tableWithKeys);
        file_put_contents($jsonPath, $jsonContent);
    }

    /**
     * Convert Key-String to Json-Key with kebab-case.
     * @param string $key
     * @return string
     */
    private function createJsonKey(string $key): string
    {
        $key = str_replace(" ", "-", $key);
        $key = str_replace(" ", "*", $key);
        $key = strtolower($key);
        return $key;
    }

    public function exportTableObjectsToJson(string $jsonPath)
    {
        $jsonData = array_map(function ($modelData) {
            if (is_object($modelData)) {
                return (array)$modelData;
            }
            return $modelData;
        }, $this->tableData);
        $jsonContent = json_encode($jsonData);
        file_put_contents($jsonPath, $jsonContent);
    }
}