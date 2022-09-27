<?php


namespace CTExport\Commands\MigrateCommands;


use CTApi\Models\Person;
use CTExport\Commands\Collections\MarkdownBuilder;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Migration
{
    public const RESULT_UNDEFINED = 0;
    public const RESULT_SUCCESS = 1;
    public const RESULT_FAILED = 2;
    public const RESULT_SKIPPED = 3;

    private ?MarkdownBuilder $markdownBuilder = null;
    private ?OutputInterface $output = null;
    private bool $isTestRun = true;

    /**
     * @param $model
     * @return int|array Status Ids
     */
    abstract public function migrateModel($model): int|array;

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

    protected function log(string $message, int $result = self::RESULT_UNDEFINED): int
    {
        switch ($result) {
            case self::RESULT_SUCCESS:
                $message = "SUCCESS: " . $message;
                break;
            case self::RESULT_FAILED:
                $message = "FAILED: " . $message;
                break;
            case self::RESULT_SKIPPED:
                $message = "SKIPPED: " . $message;
                break;
            default:
                $message = "UNDEFINED: " . $message;
        }

        if (!is_null($this->markdownBuilder)) {
            $this->markdownBuilder->addListItem($message);
        }
        if (!is_null(($this->output))) {
            $this->output->writeln($message);
        }
        return $result;
    }

    protected function logModel(string $message, $model, int $result = self::RESULT_UNDEFINED): int
    {
        if (is_object($model)) {
            $className = get_class($model);
            if (method_exists($model, "getId")) {
                $className .= " (#" . $model->getId(); // klammer auf
                if (is_a($model, Person::class)) {
                    $className .= "; " . $model->getFirstName() . " " . $model->getLastName();
                }
                $className .= ")";                      // klammer zu
            }
            $this->log($message . " [" . $className . "]", $result);
        } else {
            $this->log($message, $result);
        }
        return $result;
    }
}