<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadEvents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;


#[AsCommand(
    name: 'show:songs',
    description: 'Show all songs.',
    hidden: false,
)]
class ShowSongsCommand extends ShowTableCommand
{
    use LoadEvents;

    const OPTION_NAME = "name";
    const OPTION_SHOULD_PRACTICE = "should-practice";

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::OPTION_NAME, null, InputOption::VALUE_REQUIRED, "Search for song title.");
        $this->addOption(self::OPTION_SHOULD_PRACTICE, null, InputOption::VALUE_NEGATABLE, "Search for songs that are marked as should-practice.");
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $songTitle = $input->getOption(self::OPTION_NAME);
        $shouldPractice = $input->getOption(self::OPTION_SHOULD_PRACTICE);

        if (!is_null($songTitle)) {
            return TableBuilder::forSongs($this->loadSongsWithTitle($songTitle));
        } else {
            if (!is_null($shouldPractice)) {
                return TableBuilder::forSongs($this->loadSongsShouldPractice($shouldPractice));
            } else {
                return TableBuilder::forSongs($this->loadSongs());
            }
        }
    }

}