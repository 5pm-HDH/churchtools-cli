<?php


namespace CTExport\Commands\TemplateCommands;


use CTExport\Commands\AbstractCommand;
use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'template:list',
    description: 'Show all stored templates.',
    aliases: ['template:show'],
    hidden: false,
)]
class TemplateListCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->addOption("all", "a", InputOption::VALUE_NONE, "Show arguments and options of templates.", null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $showAll = $input->getOption("all");

        $templateAsTable = ExportTemplate::loadAllTemplatesAsTable($showAll);

        $table = new Table($output);

        if ($showAll) {
            $table->setHeaders(["Name", "Comand", "Arguments", "Options"]);
        } else {
            $table->setHeaders(["Name", "Comand"]);
        }
        $table->setRows($templateAsTable);
        $table->render();

        return Command::SUCCESS;
    }
}