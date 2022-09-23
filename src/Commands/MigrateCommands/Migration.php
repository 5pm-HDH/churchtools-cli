<?php


namespace CTExport\Commands\MigrateCommands;


use CTExport\Commands\Collections\MarkdownBuilder;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Migration
{
    private MarkdownBuilder $markdownBuilder;
    private OutputInterface $output;
    private bool $isTestRun = true;

    abstract public function migrateModel($model): void;

    public function setTestRun(bool $isTestRun)
    {
        $this->isTestRun = $isTestRun;
    }

    public function setLog(MarkdownBuilder $markdownBuilder)
    {
        $this->markdownBuilder = $markdownBuilder;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function isTestRun(): bool
    {
        return $this->isTestRun;
    }

    protected function log(string $message)
    {
        if (!is_null($this->markdownBuilder)) {
            $this->markdownBuilder->addListItem($message);
        }
        if (!is_null(($this->output))) {
            $this->output->writeln($message);
        }
    }

    protected function logModel(string $message, $model)
    {
        if (is_object($model)) {
            $className = get_class($model);
            if (method_exists($model, "getId")) {
                $className .= " (#" . $model->getId() . ")";
            }
            $this->log($message . " [" . $className . "]");
        } else {
            $this->log($message);
        }
    }
}