<?php


namespace CTExport\Commands;


use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class ExportCommand extends AbstractCommand
{
    protected function createSpreadsheetPath(?string $note = null): string
    {
        $name = __DIR__ . '/../../export/' . date("Y-m-d-H-i-s");
        $name .= "-" . str_replace(":", "-", $this->getName());
        if ($note != null) {
            $name .= '-' . $note;
        }
        return $name . '.xlsx';
    }

    protected function askStoreExportTemplate(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper("question");
        $saveExportQuestion = new ConfirmationQuestion("Save this export as template (y/n)?", false);

        if ($helper->ask($input, $output, $saveExportQuestion)) { // Save Export as Template
            $templateNameQuestion = new Question("Please enter a template-name:");
            $templateName = null;
            while ($templateName == null) {
                $templateName = $helper->ask($input, $output, $templateNameQuestion);

                if ($templateName == null) {
                    $output->writeln("Template-name cannot be empty.");
                }

                if (ExportTemplate::checkIfTemplateExists($templateName)) {
                    $output->writeln("Template already exists. Please use other template-name.");
                    $templateName = null;
                }
            }

            ExportTemplate::storeTemplate($templateName, $input->getArguments(), $input->getOptions());

            $output->writeln("Template stored successfully.");
        }
    }
}