<?php


namespace CTExport\Commands\ExportCommands;

use CTExport\Commands\Collections\SpreadsheetCheckInBuilder;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:check-in',
    description: 'Export the for list of given groups.',
    hidden: false,
)]
class ExportCheckInCommand extends ExportCommand
{
    use LoadGroups;

    const GROUP_ID = "group_ids";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::GROUP_ID, InputArgument::REQUIRED, "List of group-id separated by comma.");
        $this->addOptionStartDate();
        $this->addOptionEndDate();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startDate = $this->getOptionStartDate($input);
        $endDate = $this->getOptionEndDate($input);
        $groupId = (int)$input->getArgument(self::GROUP_ID);

        $output->writeln("Load CheckIn-Data for period " . $startDate . " - " . $endDate . " and group-id " . $groupId);

        $meetings = $this->loadGroupMeetings($groupId, $startDate, $endDate);
        $output->writeln("Found " . sizeof($meetings) . " GroupMeetings.");


        if (empty($meetings)) {
            $output->writeln("0 Groupmeetings loaded for Start- / End-Date and Calendars.");
            return Command::INVALID;
        }

        $spreadsheet = SpreadsheetCheckInBuilder::forGroupMeetings($meetings);
        $fileName = $this->createSpreadsheetPath();

        $spreadsheet->build($fileName);
        return parent::execute($input, $output);
    }
}