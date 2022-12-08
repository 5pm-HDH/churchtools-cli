<?php

namespace CTExport\ExportTemplate;

use InvalidArgumentException;

class ExportTemplate
{
    public const COMMAND_OPTION_ADD_TEMPLATE = "add-template";

    private static string $TEMPLATE_DIR = "config/";

    /**
     * Load all available Templates as table with columns:
     * <li>template-name</li>
     * <li>command</li>
     * <li>argument</li>
     * <li>options</li>
     * @param bool $showAll determine if show all columns
     * @return array
     */
    public static function loadAllTemplatesAsTable(bool $showAll): array
    {
        $table = [];
        $templates = self::loadAllTemplates();
        foreach ($templates as $name => $content) {
            if ($showAll) {
                $table[] = [
                    $name ?? "-",
                    $content["arguments"]["command"] ?? "-",
                    self::parseArgumentOrOptionForTable($content["arguments"]),
                    self::parseArgumentOrOptionForTable($content["options"])
                ];
            } else {
                $table[] = [
                    $name ?? "-",
                    $content["arguments"]["command"] ?? "-"
                ];
            }
        }
        return $table;
    }

    private static function parseArgumentOrOptionForTable(?array $argumentOrOption): string
    {
        if ($argumentOrOption == null) {
            return "-";
        }
        $returnString = "";
        $ignoreKeys = ["command", "help", "quiet", "verbose", "version", "ansi", "no-interaction"];
        foreach ($argumentOrOption as $key => $value) {
            if (!in_array($key, $ignoreKeys)) {
                $returnString .= $key . ": " . $value . ", ";
            }
        }
        return $returnString;
    }

    /**
     * Return list of Template-Names for ChoiceQuestion.
     * @return array list of template-names
     */
    public static function loadAllTemplatesForChoiceQuestion(): array
    {
        $templates = self::loadAllTemplates();
        $templateNames = [];
        foreach ($templates as $name => $content) {
            $templateNames[] = $name;
        }
        return $templateNames;
    }

    /**
     * Load all Templates. Key is template-name and value is json-file-content.
     * @return array
     */
    private static function loadAllTemplates(): array
    {
        if (!file_exists(self::$TEMPLATE_DIR)) {
            mkdir(self::$TEMPLATE_DIR, 0777, true);
        }

        $blackListFiles = [".", "..", ".gitignore", "settings.json"];

        $templateFiles = array_filter(scandir(self::$TEMPLATE_DIR), function ($filename) use ($blackListFiles) {
            return !in_array($filename, $blackListFiles);
        });

        $templates = [];
        foreach ($templateFiles as $file) {
            $fileContent = file_get_contents(self::$TEMPLATE_DIR . $file);
            $templateName = str_replace(".json", "", $file);
            $templates[$templateName] = json_decode($fileContent, true);
        }
        return $templates;
    }

    public static function getTemplateContent(string $templateName): array
    {
        if (!file_exists(self::$TEMPLATE_DIR)) {
            mkdir(self::$TEMPLATE_DIR, 0777, true);
        }

        $templateFile = self::$TEMPLATE_DIR . $templateName . ".json";
        if (!file_exists($templateFile)) {
            throw new InvalidArgumentException("Could not load Template " . $templateName . " from disk: " . $templateFile);
        }
        $fileContent = file_get_contents($templateFile);
        $templateContent = json_decode($fileContent, true);
        return $templateContent;
    }

    public static function deleteTemplate(string $templateName): void
    {
        if (!file_exists(self::$TEMPLATE_DIR)) {
            mkdir(self::$TEMPLATE_DIR, 0777, true);
        }

        $templateFile = self::$TEMPLATE_DIR . $templateName . ".json";
        if (!file_exists($templateFile)) {
            throw new InvalidArgumentException("Could not find Template " . $templateName . " from disk: " . $templateFile);
        }
        unlink($templateFile);
    }

    public static function clearTemplates(): int
    {
        $deleteNr = 0;
        foreach (self::loadAllTemplates() as $templateName => $templateContent) {
            $deleteNr++;
            unlink(self::$TEMPLATE_DIR . $templateName . '.json');
        }
        return $deleteNr;
    }

    /**
     * Store Template to JSON-File
     * @param string $templateName
     * @param array $arguments
     * @param array $options
     */
    public static function storeTemplate(string $templateName, array $arguments, array $options)
    {
        $templateFile = self::createPathForTemplate($templateName);

        if (array_key_exists(self::COMMAND_OPTION_ADD_TEMPLATE, $options)) {
            unset($options[self::COMMAND_OPTION_ADD_TEMPLATE]);
        }

        $jsonData = json_encode([
            "arguments" => $arguments,
            "options" => $options
        ]);
        file_put_contents($templateFile, $jsonData);
    }

    public static function checkIfTemplateExists(string $templateName): bool
    {
        $templatePath = self::createPathForTemplate($templateName);
        return file_exists($templatePath);
    }

    private static function createPathForTemplate(string $templateName)
    {
        if (!file_exists(self::$TEMPLATE_DIR)) {
            mkdir(self::$TEMPLATE_DIR, 0777, true);
        }

        return self::$TEMPLATE_DIR . $templateName . ".json";
    }
}