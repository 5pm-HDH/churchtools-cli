<?php


namespace CTExport\Commands\ExportCommands;


use CTApi\Models\Tag;
use CTApi\Requests\PersonRequest;
use CTExport\Commands\Collections\SpreadsheetDataBuilder;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:person-tags',
    description: 'Export the tags for all person in the given groups.',
    hidden: false,
)]
class ExportPersonTagsCommand extends ExportCommand
{
    use LoadGroups;

    const GROUP_IDS = "group_ids";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::GROUP_IDS, InputArgument::REQUIRED, "List of group-ids separated by comma.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupIds = $this->getArgumentAsIntegerList($input, self::GROUP_IDS);

        $groups = $this->loadGroupsByIds($groupIds);

        if (empty($groups)) {
            $output->writeln("0 Groups loaded.");
            return Command::INVALID;
        }

        $output->writeln("Load Group-Members:");
        $personIds = $this->loadPersonIdsFromGroups($output, $groups);

        $output->writeln("Load Person-Data:");
        $data = $this->loadTagDataForPersonIds($output, $personIds);
        $spreadsheet = new SpreadsheetDataBuilder($data);

        $fileName = $this->createSpreadsheetPath();
        $spreadsheet->withCountColumn()
            ->withDataColumns()
            ->build($fileName);
        $output->writeln("Stored export to: " . $fileName);

        $fileNameByTags = $this->createSpreadsheetPath("by-tags");
        $spreadsheet->doFlipAxes()->build($fileNameByTags);
        $output->writeln("Stored export to: " . $fileNameByTags);

        return parent::execute($input, $output);
    }

    private function loadPersonIdsFromGroups(OutputInterface $output, array $groups): array
    {
        $progressBar = new ProgressBar($output, sizeof($groups));
        $progressBar->start();

        $personIds = [];

        foreach ($groups as $group) {
            $members = $group->requestMembers()?->get() ?? [];

            $personIds = array_merge($personIds, array_map(function ($member) {
                return $member?->getPersonId();
            }, $members));

            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln("");

        return array_unique($personIds);
    }

    private function loadTagDataForPersonIds(OutputInterface $output, array $personIds): array
    {
        $progressBar = new ProgressBar($output, sizeof($personIds));
        $progressBar->start();

        $tableData = [];

        foreach ($personIds as $personId) {
            $person = PersonRequest::find((int)$personId);
            if ($person != null) {
                $personIdentifier = $person->getFirstName() . " " . $person->getLastName() . " (#" . $person->getId() . ")";
                $tags = $person->requestTags()?->get() ?? [];

                $tableData[$personIdentifier] = array_map(function (Tag $tag) {
                    return $tag->getName() . " (#" . $tag->getId() . ")";
                }, $tags);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("");

        return $tableData;
    }
}