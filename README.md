# ChurchTools-CLI-Tool
The ChurchTools-CLI-Tool provides direct data access to your ChurchTools application instance. Interaction is via easy to learn commands in a CLI application (cmd on windows, terminal on mac, bash on linux). The focus is on:

- 🔍 the [display of data](#31-show-data)
- 📃 the [export of data](#32-export-data)
- 📈 the [creation of reports](#35-report)
- 🔨 the [migration and import of data](#34-migrations)

**Support:** If there is a functionality or command that you are missing, feel free to email me with your requirements. The implementation usually takes place in less than 3 days: lukas.dumberger@gmail.com

## Demo - Export Songs to Excel

This Demo shows how to export all songs from ChurchTools with the CLI-Tool:
![DemoApplication](./docs/cli-demo.gif)

## 1. Prerequisites

To use this CLI-Tool you need to install PHP 8.0 on your System.

- Tutorial: [Install PHP on Windows](https://www.w3resource.com/php/installation/install-php-on-windows.php)
- Tutorial: [Install PHP on Mac](https://daily-dev-tips.com/posts/installing-php-on-your-mac/)

## 2. Usage / Installation

**Option 1: Download phar**

Download the packed phar executable: [ct.phar](https://github.com/5pm-HDH/churchtools-cli/raw/main/dist/ct.phar)

Execute the phar from your bash/cmd and e.q. list all available commands:

```bash
php ct.phar list
```

**Option 2: Clone this repo**

Clone this repo and execute the CLI from your bash/cmd and e.q. with this command to print all available commands:

```bash
php ct list
```

## 3. Setup

Before you can retrieve data from ChurchTools, you must set up the configuration. Call this command:

```bash
php ct.phar settings:setup
```

![Setup](./docs/setup.gif)

### 3.1 Show data

Retrieve date from ChurchTools with the `show`-commands. For example show all available calendars:

![ShowCalendars-Command](./docs/show-calendars.gif)

Other available commands:

- `show:api-token`
- `show:calendars`
- `show:services`
- `show:groups`
- `show:resources`
- `show:bookings`
- `show:events`
- `show:songs`
- `show:group-members`
- `show:absence`

**Options:**

- `--help` get additional context
- `--export` export the displayed table to an excel file
- `--export-json` export the displayed table to an json-file
- `--export-json-objects` export the displayed models as raw json-objects to an json-file
- `--add-template=[TEMPLATE_NAME]` create template from command (see section [Templates](#templates))

### 3.2 Export data

Export data to excel files with the `export`-commands. For example the `export:song-usage`-command:

![ExportData](./docs/export-with-help.gif)

**Options:**

- `--help` get additional context
- `--add-template=[TEMPLATE_NAME]` create template from command (see section [Templates](#templates))

#### Export Song-Usage

```bash
php ct.phar export:song-usage 42 --start_date=2019-02-01 --end_date=2020-04-01
```

Export Usage of songs of all events that are created in the calendar with the id 42. You can optional add start- /
end-date flags.

#### Export Service-Person

```bash
php ct.phar export:service-person 42 12,13,14 --start_date=2019-02-01 --end_date=2020-04-01
```

Export the services of all events that are created in the calendar with the id 42. You can optional add start- /
end-date flags.

#### Export Permissions

```bash
php ct.phar export:permissions
```

Detailed information: [Export-Permission](./docs/examples/permissions.md)

#### Export Person Tags

```bash
php ct.phar export:person-tags 11,18,16
```

Export the Tags of all Group-Members in the given groups. In the example above the export will retrieve all members from group 11, 18 and 16.

### 3.3 Templates

Templates are useful to store frequently executed commands. With the option `--add-template=[TEMPLATE-NAME]` you can
store the command with all arguments and options to a template:

![StoreTemplate](./docs/export-with-template.gif)

With the `template:list` command you can display all stored templates. To execute a template simply call `template:run`
with the template-name as argument.

- `template:list` list all stored export-template
- `tepmlate:run` run a stored export-template
- `template:delete` delete a stored export-template
- `template:clear` deletes all templates

### 3.4 Migrations

With the CLI tool you can also update (or migrate) data. The migrate-commands are available for this purpose:

- `php ct.phar migrate:song-arrangement-bpm`
- `php ct.phar migrate:song-arrangement-names`
- `php ct.phar migrate:song-should-practice-clear`

**IMPORTANT:** By default all migrate commands run in "Test-Mode". This means the data is not really changed. It only simulates the commands to give you the opportunity to check if the migration updates the data correctly. To run the migration on production-data please add the option `--no-testmode`.

**Options:**
- `--add-template=TemplateName` create Template for Migration
- `--testmode` run migration in testmode (this is default case)
- `--no-testmode` run migration on production data
- `--silence` dont print migration results to cli-console
- `--song-categories` (only for song-migrations) filter by song-categories as id-list

Example on how to use migrations: [Example how to use migrations](/docs/examples/migrate-songs.md)

### 3.5 Report

Reports generate Markdown files as output. There is currently one report available:

#### Report group-intersection

This reports needs two arguments:

- parent-group-id
- children-group-ids (as comma-seperated list)

The report processes all child-groups and checks if all members of a child-group are also member of the parent group.
Also it checks is all members of a parent group are in at least one child-group.

![GroupIntersectionReport](./docs/report-with-help.gif)

The output of the report can look like this:

![ReportResult](./docs/report-result.PNG)

### 3.6 Settings

You can list, update and edit the settings:

![SettingsCommand](./docs/settings-list.gif)

- `settings:clear` remove all settings
- `settings:list` list all available settings
- `settings:setup` setup settings interactive