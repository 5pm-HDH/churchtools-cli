<?php


namespace CTExport\Commands\TemplateCommands;


use CTExport\Commands\AbstractCommand;
use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'template:run',
    description: 'Run a export-templates.',
    aliases: ['template:exec', 'template:execute', 'template:use', 'template:start'],
    hidden: false,
)]
class TemplateRunCommand extends AbstractCommand
{
    private const ARGUMENT_TEMPLATE_NAME = "TemplateName";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_TEMPLATE_NAME, InputArgument::OPTIONAL, "Name of template to execute.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateName = $input->getArgument(self::ARGUMENT_TEMPLATE_NAME);
        if (is_null($templateName)) {
            return $this->selectTemplateInteractive($input, $output);
        } else {
            if (!ExportTemplate::checkIfTemplateExists($templateName)) {
                $output->writeln("Template '" . $templateName . "' does not exist and can't be executed.");
                return Command::FAILURE;
            } else {
                $templateContent = ExportTemplate::getTemplateContent($templateName);
                return $this->executeTemplate($templateContent, $output);
            }
        }
    }

    private function selectTemplateInteractive(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper("question");


        $selectTemplateQuestion = new ChoiceQuestion(
            'Please select a Template to run:',
            ExportTemplate::loadAllTemplatesForChoiceQuestion(),
            0
        );

        $selectedTemplate = $helper->ask($input, $output, $selectTemplateQuestion);

        $output->writeln("Execute template: " . $selectedTemplate);
        $templateContent = ExportTemplate::getTemplateContent($selectedTemplate);

        return $this->executeTemplate($templateContent, $output);
    }

    private function executeTemplate(array $templateContent, OutputInterface $output): int
    {
        $commandString = $templateContent["arguments"]["command"];
        $command = $this->getApplication()?->find($commandString);

        $argumentsForCommand = $templateContent["arguments"];
        foreach ($templateContent["options"] as $option => $value) {
            $argumentsForCommand["--" . $option] = $value;
        }
        $input = new ArrayInput($argumentsForCommand);

        return $command?->run($input, $output) ?? Command::FAILURE;
    }
}