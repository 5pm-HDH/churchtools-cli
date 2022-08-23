<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ShowTableCommand extends AbstractCommand
{

    private const OPTION_EXPORT_SPREADSHEET = "export";
    private const OPTION_EXPORT_JSON = "export-json";
    private const OPTION_EXPORT_JSON_OBJECTS = "export-json-objects";

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::OPTION_EXPORT_SPREADSHEET, null, InputOption::VALUE_NONE, "Export table-result to excel-sheet.");
        $this->addOption(self::OPTION_EXPORT_JSON, null, InputOption::VALUE_NONE, "Export table-result to json-file.");
        $this->addOption(self::OPTION_EXPORT_JSON_OBJECTS, null, InputOption::VALUE_NONE, "Export objects of table to json-file.");
    }

    abstract protected function getTableBuilder(InputInterface $input): TableBuilder;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tableBuilder = $this->getTableBuilder($input);
        $tableBuilder->withProgressBar();
        $tableBuilder->build($output);

        if ($input->getOption(self::OPTION_EXPORT_SPREADSHEET)) {
            $tableBuilder->exportToSpreadsheet($this->createSpreadsheetPath());
            $output->writeln("Exported table to spreadsheet successfully.");
        }

        if ($input->getOption(self::OPTION_EXPORT_JSON)) {
            $tableBuilder->exportTableRowsToJson($this->createJsonPath("rows"));
            $output->writeln("Exported table to JSON successfully.");
        }

        if ($input->getOption(self::OPTION_EXPORT_JSON_OBJECTS)) {
            $tableBuilder->exportTableObjectsToJson($this->createJsonPath("row-objects"));
            $output->writeln("Exported table row-objects to JSON successfully.");
        }

        return Command::SUCCESS;
    }
}