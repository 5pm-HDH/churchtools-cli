<?php


namespace CTExport\Commands\MigrateCommands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'migrate:song-arrangement-names',
    description: 'Migrate all song arrangements to contain key of arrangement.',
    hidden: false,
)]
class MigrateSongArrangementNameCommand extends MigrateSongCommand
{
    protected function getMigration(): Migration
    {
        return new SongArrangementNameMigration();
    }
}