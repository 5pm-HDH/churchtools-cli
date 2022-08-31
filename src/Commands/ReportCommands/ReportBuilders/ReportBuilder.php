<?php

namespace CTExport\Commands\ReportCommands\ReportBuilders;

use Symfony\Component\Console\Output\OutputInterface;

interface ReportBuilder
{
    public function withProgressBar(): ReportBuilder;

    public function build(OutputInterface $output, string $outputPath);
}