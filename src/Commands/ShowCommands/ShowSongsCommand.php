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

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::OPTION_NAME, null, InputOption::VALUE_REQUIRED, "Search for song title.");
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $songTitle = $input->getOption(self::OPTION_NAME);
        if(!is_null($songTitle)){
            return TableBuilder::forSongs($this->loadSongsWithTitle($songTitle));
        }else{
            return TableBuilder::forSongs($this->loadSongs());
        }
    }

}