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
    const INPUT_OPTION_TESTMODE = "testmode";
    const INPUT_OPTION_SILENCE = "silence";
    const INPUT_OPTION_IGNORE_SKIPPED = "ignore-skipped";

    private array $migrationResultCount = [
        Migration::RESULT_SUCCESS => 0,
        Migration::RESULT_SKIPPED => 0,
        Migration::RESULT_FAILED => 0,
        Migration::RESULT_UNDEFINED => 0
    ];

    public function enableAddTemplate(): bool
    {
        return true;
    }

    protected function canSendMail(): bool
    {
        return true;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::INPUT_OPTION_TESTMODE, null, InputOption::VALUE_NEGATABLE, "Execute migration without editing data in production.", true);
        $this->addOption(self::INPUT_OPTION_SILENCE, null, InputOption::VALUE_NEGATABLE, "Prevent display migration log to console.", false);
        $this->addOption(self::INPUT_OPTION_IGNORE_SKIPPED, null, InputOption::VALUE_NEGATABLE, "Ignore skipped migrations and prevent generation of log.", false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isTestrun = $input->getOption(self::INPUT_OPTION_TESTMODE);
        $isSilence = $input->getOption(self::INPUT_OPTION_SILENCE);
        $isIgnoreSkipped = $input->getOption(self::INPUT_OPTION_IGNORE_SKIPPED);

        $output->writeln("Execute Migration " . ($isTestrun ? "as test-run." : "on production data."));

        $models = $this->collectModels($input);
        if (empty($models)) {
            $output->writeln("Found 0 data entries to migrate.");
            return Command::INVALID;
        }
        $lastModel = end($models);
        $output->writeln("Found " . sizeof($models) . " data entries from type " . get_class($lastModel) . " to migrate.");

        $migration = $this->getMigration();
        if (!$isSilence) {
            $migration->setOutput($output);
        }
        $markdownFile = new MarkdownBuilder();
        $migration->setLog($markdownFile);
        $migration->setTestRun($isTestrun);

        foreach ($models as $model) {
            $result = $migration->migrateModel($model);
            if (!is_array($result)) { // capsule result to array if it is no array
                $result = [$result];
            }
            foreach ($result as $resultCode) {
                $this->migrationResultCount[$resultCode]++;
            }
        }
        $migration->postMigration();


        // Migration Results
        $output->writeln("Migration result:");
        $output->writeln("\t- Success: " . $this->migrationResultCount[Migration::RESULT_SUCCESS]);
        $output->writeln("\t- Skipped: " . $this->migrationResultCount[Migration::RESULT_SKIPPED]);
        $output->writeln("\t- Failed: " . $this->migrationResultCount[Migration::RESULT_FAILED]);
        $output->writeln("\t- Undefined: " . $this->migrationResultCount[Migration::RESULT_UNDEFINED]);

        // Skip Log-Generation, if only skipped result.
        if (
            $isIgnoreSkipped &&
            $this->migrationResultCount[Migration::RESULT_SUCCESS] == 0 &&
            $this->migrationResultCount[Migration::RESULT_FAILED] == 0 &&
            $this->migrationResultCount[Migration::RESULT_UNDEFINED] == 0) {
            $output->writeln("\nMigration contains only skipped result. Skip generation of log-file.");
            return parent::execute($input, $output);
        }

        $sortedMarkdownFile = MarkdownBuilder::clone($markdownFile);
        $sortedMarkdownFile->sortMarkdown();

        $this->writeResultToMarkdown($sortedMarkdownFile, $isTestrun);
        $this->writeResultToMarkdown($markdownFile, $isTestrun);

        // Build Markdown Logs
        $logFilePath = $this->createMarkdownPath();
        $logFileSortedPath = $this->createMarkdownPath("sorted");
        $markdownFile->build($logFilePath);
        $sortedMarkdownFile->build($logFileSortedPath);
        $output->writeln("Finished migration in " . ($isTestrun ? "test-mode:" : "production-mode:"));
        $output->writeln("\t- Stored Log to " . $logFilePath);
        $output->writeln("\t- Stored Log to " . $logFileSortedPath);
        if ($isTestrun) {
            $output->writeln("\t- Execute migration again with '--no-" . self::INPUT_OPTION_TESTMODE . "' to run migration on production data");
        }

        return parent::execute($input, $output);
    }

    abstract protected function collectModels(InputInterface $input): array;

    abstract protected function getMigration(): Migration;

    private function writeResultToMarkdown(MarkdownBuilder $markdownFile, bool $isTestMode)
    {
        $markdownFile->prependHeading("Log:");
        $markdownFile->prependText("");
        $markdownFile->prependListItem("Failed: " . $this->migrationResultCount[Migration::RESULT_FAILED]);
        $markdownFile->prependListItem("Skipped: " . $this->migrationResultCount[Migration::RESULT_SKIPPED]);
        $markdownFile->prependListItem("Success: " . $this->migrationResultCount[Migration::RESULT_SUCCESS]);
        $markdownFile->prependListItem("Undefined: " . $this->migrationResultCount[Migration::RESULT_UNDEFINED]);
        $markdownFile->prependHeading("Migration result in " . ($isTestMode ? "test-mode" : "production-mode") . ":");
    }
}