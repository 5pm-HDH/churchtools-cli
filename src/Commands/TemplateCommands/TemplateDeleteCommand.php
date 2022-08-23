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
    name: 'template:delete',
    description: 'Delete export-template.',
    aliases: ["template:remove"],
    hidden: false,
)]
class TemplateDeleteCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper("question");

        $selectTemplateQuestion = new ChoiceQuestion(
            'Please select the Template to delete:',
            array_merge(["-"], ExportTemplate::loadAllTemplatesForChoiceQuestion()),
            0
        );

        $selectedTemplate = $helper->ask($input, $output, $selectTemplateQuestion);

        if ($selectedTemplate == "-") {
            $output->writeln("Did not delete any template.");
            return Command::SUCCESS;
        }

        ExportTemplate::deleteTemplate($selectedTemplate);

        $output->writeln("Deleted Template: " . $selectedTemplate);

        return Command::SUCCESS;
    }
}