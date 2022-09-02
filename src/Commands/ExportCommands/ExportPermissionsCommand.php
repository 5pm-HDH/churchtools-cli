<?php


namespace CTExport\Commands\ExportCommands;

use CTApi\CTClient;
use CTApi\Utils\CTResponseUtil;
use CTApi\Utils\CTUtil;
use CTExport\Commands\Collections\SpreadsheetDataBuilder;
use CTExport\Commands\Collections\SpreadsheetTableBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:permissions',
    description: 'Export permissions of all tables.',
    hidden: false,
)]
class ExportPermissionsCommand extends ExportCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ctClient = CTClient::getClient();

        $response = $ctClient->post("/", [
            "json" => ["func" => "getMasterdata"],
            "query" => ["q" => "churchauth/ajax"]
        ]);
        $data = CTResponseUtil::dataAsArray($response);

        $this->exportRawJson($data, $output);
        $translation = $this->exportAuthTableAsExcel($data, $output);
        $this->exportPermissionTemplates($data, $output, $translation);

        return parent::execute($input, $output);
    }

    private function exportRawJson(array $outputData, OutputInterface $output)
    {
        $fileName = $this->createJsonPath("raw-output");
        $output->writeln("Export raw json output.");
        file_put_contents($fileName, json_encode($outputData));
    }

    private function exportAuthTableAsExcel(array $outputData, OutputInterface $output): array
    {
        $headings = ["modulename", "permission", "id", "auth", "datenfeld", "bezeichnung", "admindarfsehen_yn", "sortkey"];
        $rows = [];
        $translation = [];
        $authTable = CTUtil::arrayPathGet($outputData, "auth_table");
        if ($authTable == null) {
            $output->writeln("ExportAuthTable: Could not read property auth_table.");
        }
        foreach ($authTable as $module => $permissionArray) {
            foreach ($permissionArray as $permission => $permissionObject) {

                $id = CTUtil::arrayPathGet($permissionObject, "id");
                $rows[] = [
                    CTUtil::arrayPathGet($permissionObject, "modulename"),
                    $permission,
                    $id,
                    CTUtil::arrayPathGet($permissionObject, "auth"),
                    CTUtil::arrayPathGet($permissionObject, "datenfeld"),
                    CTUtil::arrayPathGet($permissionObject, "bezeichnung"),
                    CTUtil::arrayPathGet($permissionObject, "admindarfsehen_yn"),
                    CTUtil::arrayPathGet($permissionObject, "sortkey"),
                ];
                if ($id != null) {
                    $translation[$id] = $permission;
                }
            }
        }

        $spreadsheet = new SpreadsheetTableBuilder($headings, $rows);
        $fileName = $this->createSpreadsheetPath("AuthTable");
        $spreadsheet->build($fileName);
        $output->writeln("Created AuthTable-Spreadsheet.");
        return $translation;
    }

    private function exportPermissionTemplates(array $data, OutputInterface $output, array $translation)
    {
        $templates = CTUtil::arrayPathGet($data, "churchauth.templates");
        $head = ["template-id", "template-name", "template-role", "permissions"];
        $rows = [];
        $rowsHotOneEncoding = [];

        foreach ($templates as $template) {
            $templateId = CTUtil::arrayPathGet($template, "id");
            $templateName = CTUtil::arrayPathGet($template, "bezeichnung");
            $data = CTUtil::arrayPathGet($template, "data");
            foreach ($data as $role => $permissions) {
                $permissionIds = [];
                if (is_array($permissions)) {
                    $permissionIds = array_keys($permissions);
                }
                sort($permissionIds);

                $permissionNames = array_map(function ($permissionId) use ($translation) {
                    $permissionId = str_replace("D", "", $permissionId);
                    if (array_key_exists($permissionId, $translation)) {
                        return $translation[$permissionId];
                    } else {
                        return "#" . $permissionId;
                    }
                }, $permissionIds);

                $permissionString = implode(", ", $permissionNames);

                $rows[] = [
                    $templateId,
                    $templateName,
                    $role,
                    $permissionString
                ];

                $rowsHotOneEncoding[$templateName . ' - ' . $role] = $permissionNames;
            }
        }

        $fileName = $this->createSpreadsheetPath("PermissionTemplates");
        (new SpreadsheetTableBuilder($head, $rows))->build($fileName);

        $fileName2 = $this->createSpreadsheetPath("PermissionTemplatesWithPermission");
        (new SpreadsheetDataBuilder($rowsHotOneEncoding))
            ->withCountColumn()
            ->withDataColumns()
            ->build($fileName2);
        $output->writeln("Created PermissionTemplate-Spreadsheet.");
    }
}