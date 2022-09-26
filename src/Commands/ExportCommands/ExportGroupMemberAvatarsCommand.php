<?php


namespace CTExport\Commands\ExportCommands;

use CTApi\Requests\FileRequest;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:group-member-avatars',
    description: 'Export the avatars of all members of a group.',
    hidden: false,
)]
class ExportGroupMemberAvatarsCommand extends ExportCommand
{
    use LoadGroups;

    const ARGUMENT_GROUP_ID = "GroupIds";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_GROUP_ID, InputArgument::REQUIRED, "Group Ids as comma-separated list.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupIds = $this->getArgumentAsIntegerList($input, self::ARGUMENT_GROUP_ID);
        $members = [];
        foreach ($groupIds as $groupId) {
            $members = array_merge($members, $this->loadGroupMember($groupId));
        }

        $progressBar = new ProgressBar($output, sizeof($members));
        $progressBar->start();

        $exportFolder = $this->createFolderPath();

        $exportSucceed = 0;
        $exportSkipped = 0;
        $exportFailed = 0;
        $exportedPersonIds = [];

        foreach ($members as $member) {
            $personId = $member->getPersonId();
            $avatarArray = FileRequest::forAvatar($personId)->get();
            if (!empty($avatarArray) && !in_array($personId, $exportedPersonIds)) {
                $avatar = end($avatarArray);
                $avatar->setName("PID" . $personId . "-" . $avatar->getName()); // prevent overriding of image names
                $hasSucceed = $avatar->downloadToPath($exportFolder);
                $exportedPersonIds[] = $personId;
                if ($hasSucceed) {
                    $exportSucceed++;
                } else {
                    $exportFailed++;
                }
            } else {
                $exportSkipped++;
            }

            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln("");
        $output->writeln("Finished export:");
        $output->writeln("\t- Succeed: " . $exportSucceed);
        $output->writeln("\t- Skipped: " . $exportSkipped);
        $output->writeln("\t- Failed: " . $exportFailed);
        $output->writeln("Exported avatars to folder: " . $exportFolder);

        return parent::execute($input, $output);
    }
}