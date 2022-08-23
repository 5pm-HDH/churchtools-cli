<?php


namespace CTExport;


use CTApi\CTConfig;
use CTExport\Commands\ExportServicePersonCommand;
use CTExport\Commands\ExportSongUsageCommand;
use CTExport\Commands\SettingsClearCommand;
use CTExport\Commands\SettingsSetupCommand;
use CTExport\Commands\SettingsShowCommand;
use CTExport\Commands\ShowApiTokenCommand;
use CTExport\Commands\ShowCalendarsCommand;
use CTExport\Commands\ShowServicesCommand;
use CTExport\Commands\TemplateDeleteCommand;
use CTExport\Commands\TemplateListCommand;
use CTExport\Commands\TemplateRunCommand;
use Symfony\Component\Console\Application;

class ExportApplication extends Application
{
    private array $settings = [];

    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        // Register Commands
        $this->add(new ShowApiTokenCommand());
        $this->add(new ShowCalendarsCommand());
        $this->add(new ShowServicesCommand());
        $this->add(new ExportSongUsageCommand());
        $this->add(new ExportServicePersonCommand());
        $this->add(new SettingsShowCommand());
        $this->add(new SettingsSetupCommand());
        $this->add(new SettingsClearCommand());
        $this->add(new TemplateListCommand());
        $this->add(new TemplateRunCommand());
        $this->add(new TemplateDeleteCommand());
    }

    public static function create(): ExportApplication
    {
        return new ExportApplication();
    }
}