<?php


namespace CTExport;


use CTExport\Commands\ExportCommands\ExportPermissionsCommand;
use CTExport\Commands\ExportCommands\ExportPersonTagsCommand;
use CTExport\Commands\ExportCommands\ExportServicePersonCommand;
use CTExport\Commands\ExportCommands\ExportSongUsageCommand;
use CTExport\Commands\ReportCommands\ReportGroupIntersectionCommand;
use CTExport\Commands\SettingCommands\SettingsClearCommand;
use CTExport\Commands\SettingCommands\SettingsSetupCommand;
use CTExport\Commands\SettingCommands\SettingsShowCommand;
use CTExport\Commands\ShowCommands\ShowApiTokenCommand;
use CTExport\Commands\ShowCommands\ShowBookingsCommand;
use CTExport\Commands\ShowCommands\ShowCalendarsCommand;
use CTExport\Commands\ShowCommands\ShowEventsCommand;
use CTExport\Commands\ShowCommands\ShowGroupMembersCommand;
use CTExport\Commands\ShowCommands\ShowGroupsCommand;
use CTExport\Commands\ShowCommands\ShowResourcesCommand;
use CTExport\Commands\ShowCommands\ShowServicesCommand;
use CTExport\Commands\ShowCommands\ShowSongCategoriesCommand;
use CTExport\Commands\ShowCommands\ShowSongsCommand;
use CTExport\Commands\TemplateCommands\TemplateClearCommand;
use CTExport\Commands\TemplateCommands\TemplateDeleteCommand;
use CTExport\Commands\TemplateCommands\TemplateListCommand;
use CTExport\Commands\TemplateCommands\TemplateRunCommand;
use Symfony\Component\Console\Application;

class ChurchToolsCliApplication extends Application
{
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        // Register Commands
        $this->add(new ShowApiTokenCommand());
        $this->add(new ShowCalendarsCommand());
        $this->add(new ShowServicesCommand());
        $this->add(new ShowGroupsCommand());
        $this->add(new ShowResourcesCommand());
        $this->add(new ShowBookingsCommand());
        $this->add(new ShowEventsCommand());
        $this->add(new ShowSongsCommand());
        $this->add(new ShowSongCategoriesCommand());
        $this->add(new ShowGroupMembersCommand());

        // Export Commands
        $this->add(new ExportSongUsageCommand());
        $this->add(new ExportServicePersonCommand());
        $this->add(new ExportPermissionsCommand());
        $this->add(new ExportPersonTagsCommand());

        // Report Commands
        $this->add(new ReportGroupIntersectionCommand());

        // Settings Commands
        $this->add(new SettingsShowCommand());
        $this->add(new SettingsSetupCommand());
        $this->add(new SettingsClearCommand());

        // Template Commands
        $this->add(new TemplateListCommand());
        $this->add(new TemplateRunCommand());
        $this->add(new TemplateDeleteCommand());
        $this->add(new TemplateClearCommand());
    }

    public static function create(): ChurchToolsCliApplication
    {
        return new ChurchToolsCliApplication();
    }
}