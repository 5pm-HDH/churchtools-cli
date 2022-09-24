<?php


namespace CTExport\Commands\MigrateCommands;


use CTExport\Commands\Traits\LoadEvents;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class MigrateSongCommand extends MigrateCommand
{
    use LoadEvents;

    const OPTION_SONG_CATEGORIES = "song-categories";

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::OPTION_SONG_CATEGORIES, null, InputOption::VALUE_REQUIRED, "Filter for song-categories as id-list.");
    }

    protected function collectModels(InputInterface $input): array
    {
        $songCategoryIds = $this->getOptionAsIntegerList($input, self::OPTION_SONG_CATEGORIES);
        if (empty($songCategoryIds)) {
            return $this->loadSongs();
        } else {
            return $this->loadSongsOfCategories($songCategoryIds);
        }
    }
}