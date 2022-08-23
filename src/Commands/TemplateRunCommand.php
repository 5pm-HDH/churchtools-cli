<?php


namespace CTExport\Commands;


use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
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
    protected function execute(InputInterface $input, OutputInterface $output)
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
        $command = $this->getApplication()->find($commandString);

        $input = new ArrayInput($templateContent["arguments"]);

        return $command->run($input, $output);
    }
}