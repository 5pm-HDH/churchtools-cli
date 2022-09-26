<?php


namespace CTExport\Commands\ExportCommands;

use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:group-images',
    description: 'Export the title-images of group.',
    hidden: false,
)]
class ExportGroupImagesCommand extends ExportCommand
{
    use LoadGroups;

    const OPTION_MY_GROUPS = "my-groups";

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::OPTION_MY_GROUPS, null, InputOption::VALUE_NONE, "Show only groups im member of.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $myGroups = $input->getOption(self::OPTION_MY_GROUPS);
        if ($myGroups) {
            $groups = $this->loadMyGroups();
        } else {
            $groups = $this->loadGroups();
        }

        $progressBar = new ProgressBar($output, sizeof($groups));
        $progressBar->start();

        $exportFolder = $this->createFolderPath();

        $exportSucceed = 0;
        $exportSkipped = 0;
        $exportFailed = 0;

        foreach ($groups as $group) {
            $groupId = $group->getId();
            $groupImageArray = $group->requestGroupImage()->get();
            if (!empty($groupImageArray)) {
                $groupImage = end($groupImageArray);
                $groupImage->setName("GID" . $groupId . "-" . $groupImage->getName()); // prevent overriding of image names
                $hasSucceed = $groupImage->downloadToPath($exportFolder);
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
        $output->writeln("Exported group-images to folder: " . $exportFolder);

        return parent::execute($input, $output);
    }
}