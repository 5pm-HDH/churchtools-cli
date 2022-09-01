<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadEvents;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;


#[AsCommand(
    name: 'show:song-categories',
    description: 'Show all song-categories.',
    hidden: false,
)]
class ShowSongCategoriesCommand extends ShowTableCommand
{
    use LoadEvents;

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        return TableBuilder::forSongCategories($this->loadSongs());
    }
}