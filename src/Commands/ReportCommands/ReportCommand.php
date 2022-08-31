<?php

namespace CTExport\Commands\ReportCommands;

use CTExport\Commands\AbstractCommand;
use CTExport\Commands\ReportCommands\ReportBuilders\ReportBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ReportCommand extends AbstractCommand
{
    abstract protected function getReportBuilder(InputInterface $input): ReportBuilder;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reportBuilder = $this->getReportBuilder($input);
        $reportBuilder->withProgressBar();
        $reportBuilder->build($output, $this->createMarkdownPath());

        return Command::SUCCESS;
    }
}