<?php


namespace CTExport\Commands;

use CTExport\ApplicationSettings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'settings:clear',
    description: 'Clear all ChurchTools-API Settings.',
    hidden: false,
)]
class SettingsClearCommand extends AbstractCommand
{
    protected function doSetupChurchToolsApi(): bool
    {
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = ApplicationSettings::loadSettings();
        foreach ($settings as $key => $value) {
            $settings[$key] = null;
        }
        ApplicationSettings::saveSettings($settings);
        $output->writeln("Cleared Settings.");

        return Command::SUCCESS;
    }
}