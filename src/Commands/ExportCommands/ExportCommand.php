<?php


namespace CTExport\Commands\ExportCommands;


use CTExport\Commands\AbstractCommand;
use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ExportCommand extends AbstractCommand
{

    protected function configure()
    {
        parent::configure();
        $this->addOption(ExportTemplate::COMMAND_OPTION_ADD_TEMPLATE, null, InputOption::VALUE_REQUIRED, "Create new Template for export.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_null($input->getOption(ExportTemplate::COMMAND_OPTION_ADD_TEMPLATE))) {
            $templateName = $input->getOption(ExportTemplate::COMMAND_OPTION_ADD_TEMPLATE);

            if (ExportTemplate::checkIfTemplateExists($templateName)) {
                $output->writeln("Template '" . $templateName . "' already exists. Please use other template-name.");
                return Command::INVALID;
            }

            ExportTemplate::storeTemplate($templateName, $input->getArguments(), $input->getOptions());
            $output->writeln("Template '" . $templateName . "' successfully stored.");
        }

        return Command::SUCCESS;
    }
}