<?php


namespace CTExport\Commands;

use CTExport\ApplicationSettings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'settings:list',
    description: 'Show Settings.',
    hidden: false,
)]
class SettingsShowCommand extends AbstractCommand
{
    protected function doSetupChurchToolsApi(): bool
    {
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Settings:");
        foreach (ApplicationSettings::loadSettings() as $key => $value) {
            if ($key == ApplicationSettings::SETTING_CT_PASSWORD) {
                $asteriskString = "";
                for ($i = 0; $i < strlen($value); $i++) {
                    $asteriskString .= "*";
                }
                $value = $asteriskString;
            }

            $output->writeln(" - " . $key . ": " . (is_null($value) ? "<null>" : $value));
        }
        return Command::SUCCESS;
    }

}