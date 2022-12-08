<?php


namespace CTExport\Commands\ExportCommands;


use CTApi\Models\Person;
use CTExport\Commands\Collections\SpreadsheetTableBuilder;
use CTExport\Commands\Traits\LoadPersons;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:round-birthdays',
    description: 'Export round birthdays for given status ids.',
    hidden: false,
)]
class ExportRoundBirthdays extends ExportCommand
{
    use LoadPersons;

    const ARGUMENT_STATUS_IDS = "StatusIds";
    const ARGUMENT_YEAR = "Year";

    const AGE_START = 50;
    const ROUND_AGES = [50, 60, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115, 120, 125, 130];

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_STATUS_IDS, InputArgument::REQUIRED, "User-Status Ids that should be considered.");
        $this->addArgument(self::ARGUMENT_YEAR, InputArgument::REQUIRED, "Year when round birthdays should be exported.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statusIds = $this->getArgumentAsIntegerList($input, self::ARGUMENT_STATUS_IDS);
        $year = (int)$input->getArgument(self::ARGUMENT_YEAR);

        $birthdayBeforeYear = ($year + 1) - self::AGE_START;
        $birthdayBefore = $birthdayBeforeYear . "-01-01";

        $output->writeln("Load all persons with birthday before " . $birthdayBefore . " and statuses: " . implode(", ", $statusIds));

        $persons = $this->loadBirthdayPerson($birthdayBefore, $statusIds);
        $output->writeln("Found " . sizeof($persons) . " Persons with birthday before " . $birthdayBefore);

        if (sizeof($persons) <= 0) {
            $output->writeln("Terminate command.");
            return parent::INVALID;
        }

        $birthdayPersonData = array_filter(array_map(function (Person $person) use ($year, $output) {
            return $this->parsePersonToBirthdayInformation($person, $year, $output);
        }, $persons), function ($birthdayData) {
            return $this->filterBirthdayInformation($birthdayData);
        });

        $output->writeln("Filtered " . sizeof($birthdayPersonData) . " persons with round birthdays.");

        $folderPath = $this->createFolderPath("ical");
        $this->createCalendarFile($birthdayPersonData, $year, $folderPath, $output);

        $spreadsheetPath = $this->createSpreadsheetPath();
        SpreadsheetTableBuilder::fromJsonArray($birthdayPersonData)->build($spreadsheetPath);
        $output->writeln("Stored Excel with birthdays to: " . $spreadsheetPath);

        return parent::execute($input, $output);
    }

    private function parsePersonToBirthdayInformation(Person $person, int $year, OutputInterface $output): null|array
    {
        $birthday = $person->getBirthday();
        if ($birthday == null) {
            $output->writeln("Person " . $person->getFirstName() . " " . $person->getLastName() . " (#" . $person->getId() . ") has no birthday in churchtools.");
            return null;
        }

        $birthYear = date('Y', strtotime($birthday));
        $age = $year - $birthYear;

        return [
            "id" => $person->getId(),
            "firstName" => $person->getFirstName(),
            "lastName" => $person->getLastName(),
            "birthName" => $person->getBirthName(),
            "birthday" => $birthday,
            "birthYear" => $birthYear,
            "age" => $age
        ];
    }

    private function filterBirthdayInformation(array|null $birthdayInformation)
    {
        if ($birthdayInformation == null) {
            return false;
        }
        return in_array($birthdayInformation["age"], self::ROUND_AGES);
    }

    private function createCalendarFile(array $birthdayPersons, int $year, string $icalFolderPath, OutputInterface $output)
    {

        foreach ($birthdayPersons as $birthdayPerson) {
            // CREATE OVERHEAD
            $icalPre = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH\n";
            $icalPost = "\nEND:VCALENDAR\n";

            // CREATE EVENT
            $event = "BEGIN:VEVENT\n";

            $birthdayThisYear = $year . substr($birthdayPerson["birthday"], 4, 6);

            $event .= "DTSTART:" . date("Ymd\THis\Z", strtotime($birthdayThisYear . " 08:00")) . "\nDTEND:" . date("Ymd\THis\Z", strtotime($birthdayThisYear . " 16:00")) . "\nTRANSP: OPAQUE\nSEQUENCE:0\nUID:\nDTSTAMP:" . date("Ymd\THis\Z");

            $title = $birthdayPerson["firstName"] . " " . $birthdayPerson["lastName"] . " (" . $birthdayPerson["age"] . "ter)";
            $description = $title . " feiert heute seinen " . $birthdayPerson["age"] . "ten Geburtstag!";
            $event .= "\nSUMMARY:" . $title . "\nDESCRIPTION:" . $description . "\nPRIORITY:1\nCLASS:PUBLIC\n";
            $event .= "BEGIN:VALARM\nTRIGGER:-PT10080M\nACTION:DISPLAY\nDESCRIPTION:Reminder\nEND:VALARM\nEND:VEVENT";

            // CREATE ICAL_FILE
            $ical = $icalPre . $event . $icalPost;
            $icalFileName = $birthdayPerson["id"] . "_" . $birthdayPerson["firstName"] . "_" . $birthdayPerson["lastName"];
            $icalPath = $icalFolderPath . "/" . $icalFileName . ".ics";
            file_put_contents($icalPath, $ical);

            $output->writeln("Stored birthday event: " . $icalPath);
        }
    }


}