<?php


namespace CTExport\Commands\ExportCommands;

use CTExport\Commands\Traits\LoadEvents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:event-setlist',
    description: 'Export the avatars of all members of a group.',
    hidden: false,
    aliases: ['export:setlist']
)]
class ExportEventSetlistCommand extends ExportCommand
{
    use LoadEvents;

    const ARGUMENT_EVENT_ID = "EventId";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_EVENT_ID, InputArgument::REQUIRED, "Event Id.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = $input->getArgument(self::ARGUMENT_EVENT_ID);
        $songs = $this->loadSetlistForEvent($eventId);

        if (empty($songs)) {
            $output->writeln("No songs found for event.");
            return Command::INVALID;
        }

        $progressBar = new ProgressBar($output, sizeof($songs));
        $progressBar->start();

        $exportFolder = $this->createFolderPath();

        $exportSucceed = 0;
        $exportSkipped = 0;
        $skippedSongs = [];
        $exportFailed = 0;
        $songPosition = 1;
        foreach ($songs as $song) {
            $arrangement = $song->requestSelectedArrangement();
            $files = $arrangement?->getFiles() ?? [];
            if (empty($files)) {
                $exportSkipped++;
                $skippedSongs[] = $song->getName();
                continue;
            }
            foreach ($files as $file) {
                $songPositionFormatted = substr('000' . $songPosition, -3);
                $file->setName("Pos" . $songPositionFormatted . "-" . $file->getName()); // sort by position
                $hasSucceed = $file->downloadToPath($exportFolder);
                if ($hasSucceed) {
                    $exportSucceed++;
                } else {
                    $exportFailed++;
                }
            }
            $songPosition++;
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln("");
        $output->writeln("Finished export:");
        $output->writeln("\t- Succeed: " . $exportSucceed);
        $output->writeln("\t- Skipped: " . $exportSkipped . " (" . implode(", ", $skippedSongs) . ")");
        $output->writeln("\t- Failed: " . $exportFailed);
        $output->writeln("Exported setlist to folder: " . $exportFolder);

        return parent::execute($input, $output);
    }
}