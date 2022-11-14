<?php


namespace CTExport\Commands\SettingCommands;

use CTExport\ApplicationSettings;
use CTExport\Commands\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'settings:setup-mail',
    description: 'Setup mail server settings.',
    hidden: false,
)]
class SettingsSetupMailCommand extends AbstractCommand
{
    protected function doSetupChurchToolsApi(): bool
    {
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = ApplicationSettings::loadSettings();
        $this->askFor("Enter Host:", ApplicationSettings::SETTING_MAIL_HOST, $settings, $input, $output);
        $this->askFor("Enter User:", ApplicationSettings::SETTING_MAIL_USER, $settings, $input, $output);
        $this->askFor("Enter Password:", ApplicationSettings::SETTING_MAIL_PASSWORD, $settings, $input, $output);
        $this->askFor("Enter Port:", ApplicationSettings::SETTING_MAIL_PORT, $settings, $input, $output);
        $this->askFor("Enter From-Address:", ApplicationSettings::SETTING_MAIL_FROM, $settings, $input, $output);
        ApplicationSettings::saveSettings($settings);
        return Command::SUCCESS;
    }

    private function askFor(string $question, string $applicationSetting, &$settings, InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper("question");

        $inputValue = null;
        while ($inputValue == null) {
            $questionObj = new Question($question, ApplicationSettings::getSettingForKey($applicationSetting));
            $inputValue = $helper->ask($input, $output, $questionObj);

            if ($inputValue == null) {
                $output->writeln($applicationSetting . " cannot be null.");
            }
        }
        $settings[$applicationSetting] = $inputValue;
    }
}