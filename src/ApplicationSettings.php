<?php


namespace CTExport;


use CTApi\CTConfig;
use InvalidArgumentException;

class ApplicationSettings
{
    public const SETTING_CT_API = "CT_API";
    public const SETTING_CT_EMAIL = "CT_EMAIL";
    public const SETTING_CT_PASSWORD = "CT_PASSWORD";
    public const SETTING_CT_TOKEN = "CT_TOKEN";

    private static string $SETTINGS_FILE = __DIR__ . "/../config/settings.json";

    private static array $settings = [];

    public static function getAvailableSettingKeys(): array
    {
        return [
            self::SETTING_CT_API,
            self::SETTING_CT_EMAIL,
            self::SETTING_CT_PASSWORD,
            self::SETTING_CT_TOKEN
        ];
    }

    public static function getSettingForKey(string $key)
    {
        if (sizeof(self::$settings) <= 0) {
            self::loadSettings();
        }

        if (!array_key_exists($key, self::$settings)) {
            throw new InvalidArgumentException("Key " . $key . " not found in Settings.");
        }
        return self::$settings[$key];
    }

    public static function setupChurchToolsApi()
    {
        $ctApi = self::getSettingForKey(ApplicationSettings::SETTING_CT_API);
        if (is_null($ctApi)) {
            throw new InvalidArgumentException("Please add Url for ChurchTools-Api. Call command settings:setup");
        }
        CTConfig::setApiUrl($ctApi);

        $apiToken = self::getSettingForKey(ApplicationSettings::SETTING_CT_TOKEN);
        if (!is_null($apiToken)) { // authenticate with api-token
            CTConfig::setApiKey($apiToken);
        } else {
            $apiEmail = self::getSettingForKey(ApplicationSettings::SETTING_CT_EMAIL);
            $apiPassword = self::getSettingForKey(ApplicationSettings::SETTING_CT_PASSWORD);

            if (!is_null($apiEmail) && !is_null($apiPassword)) {
                CTConfig::authWithCredentials($apiEmail, $apiPassword);
            } else {
                throw new InvalidArgumentException("Please add api-token or email and password to the settings. Call command settings:setup");
            }
        }
    }

    public static function loadSettings(): array
    {
        if (!file_exists(self::$SETTINGS_FILE)) {
            self::createEmptySettingsFile();
        }
        $fileContent = file_get_contents(self::$SETTINGS_FILE);
        $settings = json_decode($fileContent, true);
        foreach (self::getAvailableSettingKeys() as $settingKey) {
            if (!array_key_exists($settingKey, $settings)) {
                $settings[$settingKey] = null;
            }
        }
        self::$settings = $settings;
        return self::$settings;
    }

    public static function saveSettings(array $settings)
    {
        self::validateSettingsInput($settings);
        self::$settings = $settings;
        $jsonString = json_encode($settings);
        file_put_contents(self::$SETTINGS_FILE, $jsonString);
    }

    private static function createEmptySettingsFile()
    {
        $emptySettings = [];
        foreach (self::getAvailableSettingKeys() as $settingKey) {
            $emptySettings[$settingKey] = null;
        }
        self::saveSettings($emptySettings);
    }

    private static function validateSettingsInput(array $settings)
    {
        foreach ($settings as $key => $value) {
            if (!in_array($key, self::getAvailableSettingKeys())) {
                throw new InvalidArgumentException("Key " . $key . " is not valid for Settings.");
            }
        }
    }


}