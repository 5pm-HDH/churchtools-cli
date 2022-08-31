<?php


namespace CTExport\Commands;


use CTExport\ApplicationSettings;
use Phar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    const START_DATE = "start-date";
    const END_DATE = "end-date";

    protected function doSetupChurchToolsApi(): bool
    {
        return true;
    }

    function isRunningInPharEnvironment(): bool
    {
        return strlen(Phar::running()) > 0 ? true : false;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        if ($this->doSetupChurchToolsApi()) {
            ApplicationSettings::setupChurchToolsApi();
        }
    }

    /**
     * Create Input-Parameter <code>start_date</code>
     */
    protected function addOptionStartDate()
    {
        $this->addOption(self::START_DATE, null, InputArgument::OPTIONAL, "Start Date", date("Y-m-d", strtotime("-2 years")));
    }

    /**
     * Create Input-Parameter <code>end_date</code>
     */
    protected function addOptionEndDate()
    {
        $this->addOption(self::END_DATE, null, InputArgument::OPTIONAL, "Start Date", date("Y-m-d"));
    }

    protected function getOptionStartDate(InputInterface $input)
    {
        return $this->getOptionAsDate($input, self::START_DATE);
    }

    protected function getOptionEndDate(InputInterface $input)
    {
        return $this->getOptionAsDate($input, self::END_DATE);
    }

    protected function getOptionAsDate(InputInterface $input, string $parameter): string
    {
        $dateValue = $input->getOption($parameter);

        if (is_numeric(strtotime($dateValue))) {
            return $dateValue;
        } else {
            throw new InvalidArgumentException(sprintf("The input %s is not a valid date.", $dateValue));
        }
    }

    protected function getArgumentAsIntegerList(InputInterface $input, string $parameter): array
    {
        $listAsString = $input->getArgument($parameter);
        return $this->castSomethingToIntegerList($listAsString);
    }

    protected function getOptionAsIntegerList(InputInterface $input, string $parameter): array
    {
        $listAsString = $input->getOption($parameter);
        return $this->castSomethingToIntegerList($listAsString);
    }

    private function castSomethingToIntegerList($listAsString): array
    {
        if ($listAsString == null) {
            return [];
        }

        $list = explode(",", $listAsString);
        foreach ($list as $element) {
            if (!is_numeric($element)) {
                throw new InvalidArgumentException(sprintf("The list %s contains non-numeric values.", $listAsString));
            }
        }
        return array_map(function ($listElement) {
            return intval($listElement);
        }, $list);
    }

    protected function createSpreadsheetPath(?string $note = null): string
    {
        return $this->createExportFilePath("xlsx", $note);
    }

    protected function createJsonPath(?string $note = null): string
    {
        return $this->createExportFilePath("json", $note);
    }

    protected function createMarkdownPath(?string $note = null): string
    {
        return $this->createExportFilePath("md", $note);
    }

    private function createExportFilePath(string $fileEnding, ?string $note = null): string
    {
        $name = date("Y-m-d-H-i-s");
        $name .= "-" . str_replace(":", "-", $this->getName() ?? "export");
        if ($note != null) {
            $name .= '-' . $note;
        }
        return $name . '.' . $fileEnding;
    }
}