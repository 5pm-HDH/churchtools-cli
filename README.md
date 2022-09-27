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
- `show:absence` (options: `--start-date`, `--end-date`)
- `show:calendars`
- `show:groups` (option: `--my-groups`)
- `show:group-members`
- `show:resources`
- `show:bookings` (options: `--start-date`, `--end-date`)
- `show:events` (options: `--my-events`, `--start-date`, `--end-date`)
- `show:services` (option: `--service-group-ids`)
- `show:songs` (options: `--name`, `--should-practice`)
- `show-song-categories`

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

#### Export Event Setlist

Loads event (e.q. 281) and retrieve all songs that are linked in the event agenda. Then downloads all file-attachments
of the selected arrangement in the right order.

```bash
php ct.phar export:event-setlist 281
```

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

Export the Tags of all Group-Members in the given groups. In the example above the export will retrieve all members from
group 11, 18 and 16.

#### Export Group Images

Export the images of all accessible groups. The images will be stored in a separate folder:

```bash
php ct.phar export:group-images
```

Export only images of groups i am member of:

```bash
php ct.phar export:group-images --my-groups
```

#### Export Group Member Avatars

Export the avatars of all group members and store them to a local folder. Pass as argument the group-ids you want to
export the avatar-images from.

```bash
php ct.phar export:group-member-avatars 1192,2921
```

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
- `php ct.phar migrate:group-member-hierarchy`

**IMPORTANT:** By default all migrate commands run in "Test-Mode". This means the data is not really changed. It only simulates the commands to give you the opportunity to check if the migration updates the data correctly. To run the migration on production-data please add the option `--no-testmode`.

**Options:**
- `--add-template=TemplateName` create Template for Migration
- `--testmode` run migration in testmode (this is default case)
- `--no-testmode` run migration on production data
- `--silence` dont print migration results to cli-console
- `--song-categories` (only for song-migrations) filter by song-categories as id-list

Example on how to use migrations: 
- [migrate for songs](/docs/examples/migrate-songs.md)
- [migrate group-members](/docs/examples/migrate-group-members.md)

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

#### Report group-hierarchy

The report displays the hierarchy of this group as list. It need a group-id as argument.

Example output:

```md
# Group-Hierarchy Report

## Group:

- ID: 1141
- GUID: [GUID]
- Name: 5pm:  WORSHIP & PRODUCTION

## Children-Groups:

- 5pm:  WORSHIP & PRODUCTION (#1141)
    - 5pm:  PRODUCTION (#1156)
        - 5pm: PRODUCTION - Sound (#1838)
        - 5pm: PRODUCTION - Light (#1841)
        - 5pm: PRODUCTION - Visualisation (#1844)
    - 5pm: WORSHIP Musiker Pool (#1469)
    - 5pm: W&P Leitungsteam (#1825)

## Parent-Groups:

- 5pm:  WORSHIP & PRODUCTION (#1141)
    - 5pm:  KERNTEAM (#1138)
```

### 3.6 Settings

You can list, update and edit the settings:

![SettingsCommand](./docs/settings-list.gif)

- `settings:clear` remove all settings
- `settings:list` list all available settings
- `settings:setup` setup settings interactive