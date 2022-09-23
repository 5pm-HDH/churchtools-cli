<?php


namespace CTExport\Commands\MigrateCommands;


use CTExport\Commands\AbstractCommand;
use CTExport\Commands\Collections\MarkdownBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class MigrateCommand extends AbstractCommand
{
    const INPUT_OPTION_TESTRUN = "testrun";

    public function enableAddTemplate(): bool
    {
        return true;
    }

    abstract protected function collectModels(): array;

    abstract protected function getMigration(): Migration;

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::INPUT_OPTION_TESTRUN, null, InputOption::VALUE_NEGATABLE, "Execute migration without editing data in production.", true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isTestrun = $input->getOption(self::INPUT_OPTION_TESTRUN);
        $output->writeln("Execute Migration " . ($isTestrun ? "as test-run." : "on production data."));

        $models = $this->collectModels();
        if (empty($models)) {
            $output->writeln("Found 0 data entries to migrate.");
            return Command::INVALID;
        }
        $lastModel = end($models);
        $output->writeln("Found " . sizeof($models) . " data entries from type " . get_class($lastModel) . " to migrate.");

        $migration = $this->getMigration();
        $migration->setOutput($output);
        $markdownFile = new MarkdownBuilder();
        $migration->setLog($markdownFile);
        $migration->setTestRun($isTestrun);

        foreach ($models as $model) {
            $migration->migrateModel($model);
        }

        $logFilePath = $this->createMarkdownPath();
        $logFileSortedPath = $this->createMarkdownPath("sorted");
        $output->writeln("Finished " . ($isTestrun ? "test-" : "production-") . "migration:");
        $markdownFile->build($logFilePath);
        $markdownFile->sortMarkdown()->build($logFileSortedPath);
        $output->writeln("\t- Stored Log to " . $logFilePath);
        $output->writeln("\t- Stored Log to " . $logFileSortedPath);
        if ($isTestrun) {
            $output->writeln("\t- Execute migration again with '--no-testrun' to run migration on production data");
        }

        return parent::execute($input, $output);
    }
}