<?php


namespace CTExport\Commands\MigrateCommands;


use CTExport\Commands\Traits\LoadEvents;

abstract class MigrateSongCommand extends MigrateCommand
{
    use LoadEvents;

    protected function configure()
    {
        parent::configure();
        // option for song-category
        //$this->addOption(ExportTemplate::COMMAND_OPTION_ADD_TEMPLATE, null, InputOption::VALUE_REQUIRED, "Create new Template for export.");
    }

    protected function collectModels(): array
    {
        return $this->loadSongs();
    }
}