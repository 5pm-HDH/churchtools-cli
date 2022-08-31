<?php


namespace CTExport\Commands\TemplateCommands;


use CTExport\Commands\AbstractCommand;
use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'template:delete',
    description: 'Delete export-template.',
    aliases: ["template:remove"],
    hidden: false,
)]
class TemplateDeleteCommand extends AbstractCommand
{
    private const ARGUMENT_TEMPLATE_NAME = "TemplateName";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_TEMPLATE_NAME, InputArgument::OPTIONAL, "Name of template to delete.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateName = $input->getArgument(self::ARGUMENT_TEMPLATE_NAME);
        if (is_null($templateName)) {
            $templateName = $this->selectTemplateInteractive($input, $output);
        }

        if ($templateName == "-") {
            $output->writeln("Did not delete any template.");
            return Command::SUCCESS;
        }

        ExportTemplate::deleteTemplate($templateName);

        $output->writeln("Deleted Template: " . $templateName);

        return Command::SUCCESS;
    }

    private function selectTemplateInteractive(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper("question");

        $selectTemplateQuestion = new ChoiceQuestion(
            'Please select the Template to delete:',
            array_merge(["-"], ExportTemplate::loadAllTemplatesForChoiceQuestion()),
            0
        );

        $selectedTemplate = $helper->ask($input, $output, $selectTemplateQuestion);
        return $selectedTemplate;
    }
}