<?php


namespace CTExport\Commands\TemplateCommands;


use CTExport\Commands\AbstractCommand;
use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'template:clear',
    description: 'Delete all export-templates.',
    hidden: false,
)]
class TemplateClearCommand extends AbstractCommand
{
    protected function doSetupChurchToolsApi(): bool
    {
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numberOfTemplates = ExportTemplate::clearTemplates();
        $output->writeln("Removed " . $numberOfTemplates . " Templates.");

        return Command::SUCCESS;
    }
}