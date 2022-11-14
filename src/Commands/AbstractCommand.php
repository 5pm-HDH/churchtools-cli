<?php


namespace CTExport\Commands;


use CTExport\ApplicationSettings;
use CTExport\ExportTemplate\ExportTemplate;
use CTExport\Mail\MailBuilder;
use Phar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    const START_DATE = "start-date";
    const END_DATE = "end-date";
    const MAIL_TO = "mail-to";

    private array $createdFiles = [];

    protected function doSetupChurchToolsApi(): bool
    {
        return true;
    }

    function isRunningInPharEnvironment(): bool
    {
        return strlen(Phar::running()) > 0 ? true : false;
    }

    function enableAddTemplate(): bool
    {
        return false;
    }

    protected function canSendMail(): bool
    {
        return false;
    }

    protected function configure()
    {
        if ($this->enableAddTemplate()) {
            $this->addOption(ExportTemplate::COMMAND_OPTION_ADD_TEMPLATE, null, InputOption::VALUE_REQUIRED, "Create new Template for export.");
        }
        if ($this->canSendMail()) {
            $this->addOption(self::MAIL_TO, null, InputOption::VALUE_REQUIRED, "Send Mail to given Address.");
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        if ($this->doSetupChurchToolsApi()) {
            ApplicationSettings::setupChurchToolsApi();
        }
    }

    /**
     * Create Input-Parameter <code>start-date</code>
     */
    protected function addOptionStartDate()
    {
        $this->addOption(self::START_DATE, null, InputArgument::OPTIONAL, "Start Date", date("Y-m-d", strtotime("-2 years")));
    }

    /**
     * Create Input-Parameter <code>end-date</code>
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->enableAddTemplate() && !is_null($input->getOption(ExportTemplate::COMMAND_OPTION_ADD_TEMPLATE))) {
            $templateName = $input->getOption(ExportTemplate::COMMAND_OPTION_ADD_TEMPLATE);

            if (ExportTemplate::checkIfTemplateExists($templateName)) {
                $output->writeln("Template '" . $templateName . "' already exists. Please use other template-name.");
                return Command::INVALID;
            }

            ExportTemplate::storeTemplate($templateName, $input->getArguments(), $input->getOptions());
            $output->writeln("Template '" . $templateName . "' successfully stored.");
        }

        // SEND MAIL
        if ($this->canSendMail() && !is_null($input->getOption(self::MAIL_TO))) {
            $mailTo = $input->getOption(self::MAIL_TO);
            $mailToAddresses = explode(",", $mailTo);

            if (empty($this->createdFiles)) {
                $output->writeln("No files created by command. Skip email dispatch.");
            } else {
                $output->writeln("Send Mail with attachments.");
                MailBuilder::forAttachments($this->createdFiles, $mailToAddresses)->send();
                $output->writeln("Successfully send mail.");
            }
        }

        return Command::SUCCESS;
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
        $filePath = $name . '.' . $fileEnding;
        $this->createdFiles[] = $filePath;
        return $filePath;
    }

    public function createFolderPath(?string $note = null): string
    {
        $folderName = date("Y-m-d-H-i-s");
        $folderName .= "-" . str_replace(":", "-", $this->getName() ?? "export");
        if ($note != null) {
            $folderName .= '-' . $note;
        }
        if (!file_exists($folderName)) {
            mkdir($folderName);
        }

        return $folderName;
    }
}