<?php


namespace CTExport\Commands;


use CTExport\ExportTemplate\ExportTemplate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'template:list',
    description: 'Show all stored templates.',
    aliases: ['template:show'],
    hidden: false,
)]
class TemplateListCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateAsTable = ExportTemplate::loadAllTemplatesAsTable();

        $table = new Table($output);
        $table->setHeaders(["Name", "Comand", "Arguments", "Options"]);
        $table->setRows($templateAsTable);
        $table->render();

        return Command::SUCCESS;
    }
}