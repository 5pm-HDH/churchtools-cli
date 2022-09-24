<?php


namespace CTExport\Commands\MigrateCommands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'migrate:song-should-practice-clear',
    description: 'Set the should-practice flag of all songs to false.',
    hidden: false,
)]
class MigrateSongShouldPracticeClearCommand extends MigrateSongCommand
{
    protected function getMigration(): Migration
    {
        return new SongShouldPracticeClearMigration();
    }
}