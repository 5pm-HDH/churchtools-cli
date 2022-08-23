<?php

namespace CTExport\ExportTemplate;

use InvalidArgumentException;

class ExportTemplate
{
    private static string $TEMPLATE_DIR = __DIR__ . "/../../config/";

    /**
     * Load all available Templates as table with columns:
     * <li>template-name</li>
     * <li>command</li>
     * <li>argument</li>
     * <li>options</li>
     * @return array
     */
    public static function loadAllTemplatesAsTable(): array
    {
        $table = [];
        $templates = self::loadAllTemplates();
        foreach ($templates as $name => $content) {
            $table[] = [
                $name ?? "-",
                $content["arguments"]["command"] ?? "-",
                self::parseArgumentOrOptionForTable($content["arguments"]),
                self::parseArgumentOrOptionForTable($content["options"])
            ];
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
        $templateFile = self::$TEMPLATE_DIR . $templateName . ".json";
        if (!file_exists($templateFile)) {
            throw new InvalidArgumentException("Could not find Template " . $templateName . " from disk: " . $templateFile);
        }
        unlink($templateFile);
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
        return self::$TEMPLATE_DIR . $templateName . ".json";
    }


}