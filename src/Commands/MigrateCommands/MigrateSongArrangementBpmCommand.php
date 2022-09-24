<?php


namespace CTExport\Commands\MigrateCommands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'migrate:song-arrangement-bpm',
    description: 'Set the bpm to all arrangement of song.',
    hidden: false,
)]
class MigrateSongArrangementBpmCommand extends MigrateSongCommand
{

    protected function getMigration(): Migration
    {
        return new SongArrangementBpmMigration();
    }
}