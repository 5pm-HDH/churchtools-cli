<?php


namespace CTExport\Commands\Collections;


use CTApi\Models\GroupMeeting;
use CTApi\Models\Person;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetCheckInBuilder extends SpreadsheetBuilder
{
    private Worksheet $groupMeetingsTable;
    private Worksheet $groupMeetingsStatistic;

    private function __construct()
    {
        $this->groupMeetingsTable = new Worksheet();
        $this->groupMeetingsTable->setTitle("CheckIn");
        $this->groupMeetingsStatistic = new Worksheet();
        $this->groupMeetingsStatistic->setTitle("Statistic");
    }

    private function addGroupMeeting(GroupMeeting $groupMeeting, array $groupMembers): SpreadsheetCheckInBuilder
    {
        $spreadsheetDTO = SpreadsheetCheckInDTO::findOrCreate($groupMeeting);
        $spreadsheetDTO->addGroupMembers($groupMembers);
        return $this;
    }

    public function build(string $filePathWithName): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->addSheet($this->groupMeetingsTable);
        $spreadsheet->addSheet($this->groupMeetingsStatistic);

        $this->buildCheckInTable();
        $this->buildStatisticTable();

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePathWithName);
    }

    private function buildCheckInTable()
    {
        $persons = SpreadsheetCheckInDTO::getAllPersons();
        $groupMeetingDTOs = SpreadsheetCheckInDTO::getAllDTOs();

        $y = 0;
        $x = 0;

        foreach ($groupMeetingDTOs as $groupMeetingDTO) {
            if (is_a($groupMeetingDTO, SpreadsheetCheckInDTO::class)) {

                $x = 0;
                foreach ($persons as $person) {
                    if (is_a($person, Person::class)) {

                        // insert columns
                        if ($y == 0) {
                            $this->groupMeetingsTable->setCellValueByColumnAndRow(1, $x + 2, $this->generatePersonIdentifier($person));
                            $memberStatus = $groupMeetingDTO->getPersonMemberStatus($person->getIdAsInteger());
                            $this->groupMeetingsTable->setCellValueByColumnAndRow(2, $x + 2, $memberStatus ?? "undefined");
                        }

                        // insert headings
                        if ($x == 0) {
                            $this->groupMeetingsTable->setCellValueByColumnAndRow($y + 3, 1, $groupMeetingDTO->getIdentifierForHeading());
                        }

                        // insert headings / columns
                        if ($x == 0 && $y == 0) {
                            $this->groupMeetingsTable->setCellValueByColumnAndRow(1, 1, "Person");
                            $this->groupMeetingsTable->setCellValueByColumnAndRow(2, 1, "Member Status");
                        }

                        // insert status value
                        $status = $groupMeetingDTO->getAttendeeStatusOfPerson($person->getIdAsInteger());
                        $this->groupMeetingsTable->setCellValueByColumnAndRow($y + 3, $x + 2, $status);
                        if ($status == "Y") {
                            $this->groupMeetingsTable->getStyleByColumnAndRow($y + 3, $x + 2)->getFill()->setFillType(Fill::FILL_SOLID)
                                ->setStartColor(new Color(Color::COLOR_GREEN));
                        } else if ($status == "N") {
                            $this->groupMeetingsTable->getStyleByColumnAndRow($y + 3, $x + 2)->getFill()->setFillType(Fill::FILL_SOLID)
                                ->setStartColor(new Color(Color::COLOR_RED));
                        }
                    }
                    $x++;
                }

                $y++;
            }
        }

        $this->groupMeetingsTable->getStyleByColumnAndRow(1, 1, $y + 2, $x + 1)->applyFromArray(parent::getStyleTable());
        $this->groupMeetingsTable->getStyleByColumnAndRow(1, 1, $y + 2, 1)->applyFromArray(parent::getStyleHeading());
        $this->groupMeetingsTable->getStyleByColumnAndRow(1, 2, 2, $x + 1)->applyFromArray(parent::getStyleHeading());
        $this->groupMeetingsTable->getColumnDimensionByColumn(1)->setAutoSize(true);
        $this->groupMeetingsTable->getColumnDimensionByColumn(2)->setAutoSize(true);
        $this->groupMeetingsTable->setAutoFilterByColumnAndRow(1, 1, $y + 2, $x + 1);
    }

    private function buildStatisticTable()
    {
        $groupMeetingDTOs = SpreadsheetCheckInDTO::getAllDTOs();

        $y = 0;
        $x = 0;
        foreach ($groupMeetingDTOs as $groupMeetingDTO) {
            if (is_a($groupMeetingDTO, SpreadsheetCheckInDTO::class)) {

                $x = 0;
                foreach ($groupMeetingDTO->getStatistics() as $groupMemberStatus => $attendeeStatistic) {
                    foreach ($attendeeStatistic as $status => $numberOfPersons) {
                        // insert headings / columns
                        if ($x == 0 && $y == 0) {
                            $this->groupMeetingsStatistic->setCellValueByColumnAndRow(1, 1, "Member Status");
                            $this->groupMeetingsStatistic->setCellValueByColumnAndRow(2, 1, "Attendee Status");
                        }

                        // insert columns
                        if ($y == 0) {
                            $this->groupMeetingsStatistic->setCellValueByColumnAndRow(1, $x + 2, $groupMemberStatus);
                            $this->groupMeetingsStatistic->setCellValueByColumnAndRow(2, $x + 2, $status ?? "undefined");
                        }

                        // insert headings
                        if ($x == 0) {
                            $this->groupMeetingsStatistic->setCellValueByColumnAndRow($y + 3, 1, $groupMeetingDTO->getIdentifierForHeading());
                        }

                        // insert status value
                        $this->groupMeetingsStatistic->setCellValueByColumnAndRow($y + 3, $x + 2, $numberOfPersons);
                        if ($status == "present") {
                            $this->groupMeetingsStatistic->getStyleByColumnAndRow($y + 3, $x + 2)->getFill()->setFillType(Fill::FILL_SOLID)
                                ->setStartColor(new Color(Color::COLOR_GREEN));
                        } else if ($status == "absent") {
                            $this->groupMeetingsStatistic->getStyleByColumnAndRow($y + 3, $x + 2)->getFill()->setFillType(Fill::FILL_SOLID)
                                ->setStartColor(new Color(Color::COLOR_RED));
                        }
                        $x++;
                    }
                }

                $y++;
            }
        }

        $this->groupMeetingsStatistic->getStyleByColumnAndRow(1, 1, $y + 2, $x + 1)->applyFromArray(parent::getStyleTable());
        $this->groupMeetingsStatistic->getStyleByColumnAndRow(1, 1, $y + 2, 1)->applyFromArray(parent::getStyleHeading());
        $this->groupMeetingsStatistic->getStyleByColumnAndRow(1, 2, 2, $x + 1)->applyFromArray(parent::getStyleHeading());
        $this->groupMeetingsStatistic->getColumnDimensionByColumn(1)->setAutoSize(true);
        $this->groupMeetingsStatistic->getColumnDimensionByColumn(2)->setAutoSize(true);
        $this->groupMeetingsStatistic->setAutoFilterByColumnAndRow(1, 1, $y + 2, $x + 1);


    }

    private function generatePersonIdentifier(?Person $person): string
    {
        if ($person == null) {
            return "-";
        }
        return $person->getFirstName() . " " . $person->getLastName() . " (#" . $person->getId() . ")";
    }

    public static function forGroupMeetings(array $groupMeetings): SpreadsheetCheckInBuilder
    {
        $checkInBuilder = new SpreadsheetCheckInBuilder();

        foreach ($groupMeetings as $groupMeeting) {
            if (is_a($groupMeeting, GroupMeeting::class)) {
                $groupMembers = $groupMeeting->requestMembers()?->get() ?? [];
                $checkInBuilder->addGroupMeeting($groupMeeting, $groupMembers);
            }
        }
        return $checkInBuilder;
    }
}