<?php


namespace CTExport;


use CTApi\CTConfig;
use CTApi\CTLog;
use InvalidArgumentException;

class ApplicationSettings
{
    public const SETTING_CT_API = "CT_API";
    public const SETTING_CT_EMAIL = "CT_EMAIL";
    public const SETTING_CT_PASSWORD = "CT_PASSWORD";
    public const SETTING_CT_TOKEN = "CT_TOKEN";

    public const SETTING_MAIL_HOST = "MAIL_HOST";
    public const SETTING_MAIL_USER = "MAIL_USER";
    public const SETTING_MAIL_PASSWORD = "MAIL_PASSWORD";
    public const SETTING_MAIL_PORT = "MAIL_PORT";
    public const SETTING_MAIL_FROM = "MAIL_FROM";

    private static string $SETTINGS_FOLDER = "config";
    private static string $SETTINGS_FILE = "/settings.json";

    private static array $settings = [];

    public static function getAvailableSettingKeys(): array
    {
        return [
            self::SETTING_CT_API,
            self::SETTING_CT_EMAIL,
            self::SETTING_CT_PASSWORD,
            self::SETTING_CT_TOKEN,
            self::SETTING_MAIL_HOST,
            self::SETTING_MAIL_USER,
            self::SETTING_MAIL_PASSWORD,
            self::SETTING_MAIL_PORT,
            self::SETTING_MAIL_FROM,
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
        CTLog::enableFileLog(false); // disable file-log because phar can't edit log-file
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
        if (!file_exists(self::$SETTINGS_FOLDER)) {
            mkdir(self::$SETTINGS_FOLDER, 0777, true);
        }
        if (!file_exists(self::$SETTINGS_FOLDER . self::$SETTINGS_FILE)) {
            self::createEmptySettingsFile();
        }

        $fileContent = file_get_contents(self::$SETTINGS_FOLDER . self::$SETTINGS_FILE);
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
        if (!file_exists(self::$SETTINGS_FOLDER)) {
            mkdir(self::$SETTINGS_FOLDER, 0777, true);
        }

        self::validateSettingsInput($settings);
        self::$settings = $settings;
        $jsonString = json_encode($settings);
        file_put_contents(self::$SETTINGS_FOLDER . self::$SETTINGS_FILE, $jsonString);
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