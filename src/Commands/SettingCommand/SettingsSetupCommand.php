<?php


namespace CTExport\Commands\SettingCommands;

use CTExport\ApplicationSettings;
use CTExport\Commands\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'settings:setup',
    description: 'Setup ChurchTools-API Settings.',
    hidden: false,
)]
class SettingsSetupCommand extends AbstractCommand
{
    protected function doSetupChurchToolsApi(): bool
    {
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper("question");

        $settings = ApplicationSettings::loadSettings();

        // ChurchTools URL
        $ctUrl = null;
        while ($ctUrl == null) {
            $ctApiQuestion = new Question("Enter ChurchTools-Url:", ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_CT_API));
            $ctUrl = $helper->ask($input, $output, $ctApiQuestion);

            if ($ctUrl == null) {
                $output->writeln("ChurchTools-Url cannot be null.");
            }
        }
        $settings[ApplicationSettings::SETTING_CT_API] = $ctUrl;

        // Select Authentication Method
        $authMethodQuestion = new ChoiceQuestion(
            'Please select the authentication method (default is email and password):',
            ["email and password", "api-token"],
            0
        );
        $authMethod = $helper->ask($input, $output, $authMethodQuestion);
        if ($authMethod == "email and password") {
            $ctEmail = null;
            while ($ctEmail == null) {
                $ctApiQuestion = new Question("Enter E-Mail:", ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_CT_EMAIL));
                $ctEmail = $helper->ask($input, $output, $ctApiQuestion);

                if ($ctEmail == null) {
                    $output->writeln("E-Mail cannot be null.");
                }
            }
            $settings[ApplicationSettings::SETTING_CT_EMAIL] = $ctEmail;

            $ctPassword = null;
            while ($ctPassword == null) {
                $ctApiQuestion = new Question("Enter Password:", ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_CT_PASSWORD));
                $ctPassword = $helper->ask($input, $output, $ctApiQuestion);

                if ($ctPassword == null) {
                    $output->writeln("Password cannot be null.");
                }
            }
            $settings[ApplicationSettings::SETTING_CT_PASSWORD] = $ctPassword;
        } else {
            $ctToken = null;
            while ($ctToken == null) {
                $ctApiQuestion = new Question("Enter api-token:", ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_CT_TOKEN));
                $ctToken = $helper->ask($input, $output, $ctApiQuestion);

                if ($ctToken == null) {
                    $output->writeln("Api-token cannot be null.");
                }
            }
            $settings[ApplicationSettings::SETTING_CT_TOKEN] = $ctToken;
        }
        ApplicationSettings::saveSettings($settings);

        return Command::SUCCESS;
    }
}