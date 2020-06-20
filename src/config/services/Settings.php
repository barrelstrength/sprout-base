<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\config\services;

use barrelstrength\sproutbase\config\base\Config as BaseConfig;
use barrelstrength\sproutbase\config\base\ConfigInterface;
use barrelstrength\sproutbase\config\base\Settings as BaseSettings;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class Settings extends Component
{
    /**
     * App context will reconcile all levels of settings
     * including the file-based config overrides
     */
    const SETTINGS_CONTEXT_APP = 'app';

    /**
     * Settings context will not retrieve file-based configs
     * as the goal is to just update Project Config.
     */
    const SETTINGS_CONTEXT_SETTINGS = 'settings';

    public $context = self::SETTINGS_CONTEXT_APP;

    /**
     * Gets settings as defined in project config
     *
     * @param bool $includeFileConfigSettings
     *
     * @return array [
     *     'campaigns' => new CampaignsSetting,
     *     'control-panel' => new ControlPanelSettings,
     *     'notifications' => new NotificationsSettings,
     *     'fields' => new FieldsSettings,
     *     'forms' => new FormsSettings,
     *     'lists' => new ListsSettings,
     *     'metadata' => new MetadataSettings,
     *     'redirects' => new RedirectsSettings,
     *     'reports' => new ReportsSettings,
     *     'sent-email' => new SentEmailSettings,
     *     'sitemaps' => new SitemapsSettings
     * ]
     */
    public function getSettings($includeFileConfigSettings = true): array
    {
        $configTypes = SproutBase::$app->config->getConfigs($includeFileConfigSettings);

        $settings = [];

        foreach ($configTypes as $configType) {
            if ($settingsModel = $configType->getSettings()) {
                $settings[$configType->getKey()] = $settingsModel;
            }
        }

        ksort($settings, SORT_NATURAL);

        return $settings;
    }

    public function getSettingsByConfig(BaseConfig $config)
    {
        $settings = $this->mergeSettings($config);

        if (!$settings) {
            return null;
        }

        // Add enabled settings here for easy access later
        $cpSettings = SproutBase::$app->config->getCpSettings();
        $moduleSettings = $cpSettings->modules[$config::getKey()] ?? null;
        $enabledStatus = !empty($moduleSettings['enabled']) ? true : false;

        $settings->setIsEnabled($enabledStatus);


        return $settings;
    }

    /**
     * Gets current settings as defined in project config and config overrides
     *
     * @param $handle = [
     *     'campaigns',
     *     'control-panel',
     *     'notifications'
     *     'fields',
     *     'forms',
     *     'lists',
     *     'metadata',
     *     'redirects',
     *     'reports',
     *     'sent-email',
     *     'sitemaps'
     * ];
     *
     * @param bool $includeFileConfigSettings
     *
     * @return BaseSettings
     */
    public function getSettingsByKey($handle, $includeFileConfigSettings = true): BaseSettings
    {
        $settings = $this->getSettings($includeFileConfigSettings);

        return $settings[$handle];
    }

    /**
     * @param string $key
     * @param BaseSettings $settings
     * @param bool $packAssociativeArrays
     *
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function saveSettings(string $key, BaseSettings $settings, $packAssociativeArrays = false): bool
    {
        // Consider how validation handles settings that may not be in the current post data
//        if (!$settings->validate()) {
//            return false;
//        }

        $siteSettings = $settings->toArray();

        $newSettings = $packAssociativeArrays
            ? ProjectConfigHelper::packAssociativeArrays($siteSettings)
            : $siteSettings;

        Craft::$app->getProjectConfig()->set($key, $newSettings, "Update Sprout Settings for “{$key}”");

        return true;
    }

    /**
     * Returns all settings for a given config
     *
     * Settings priorities are as follows:
     * 1. Settings found in `config/sprout.php` file (optional)
     * 2. Settings found under `sprout.[configHandle]` in project config
     * 3. Default settings from Settings model
     *
     * @param ConfigInterface $configType
     *
     * @return BaseSettings|null
     */
    public function mergeSettings(ConfigInterface $configType)
    {
        $projectConfigService = Craft::$app->getProjectConfig();
        $allProjectConfigSettings = $projectConfigService->get(Config::CONFIG_SPROUT_KEY) ?? [];

        $settingsModel = $configType->createSettingsModel();

        if ($settingsModel !== null) {
            $defaultSettings = $settingsModel->getAttributes() ?? [];
            $projectConfigSettings = $allProjectConfigSettings[$configType->getKey()] ?? [];

            $mergedSettings = array_merge($defaultSettings, $projectConfigSettings);

            if ($this->context === self::SETTINGS_CONTEXT_APP) {
                $allFileConfigSettings = Craft::$app->getConfig()->getConfigFromFile('sprout');
                $fileConfigSettings = $allFileConfigSettings[$configType->getKey()] ?? [];

                $mergedSettings = array_merge($mergedSettings, $fileConfigSettings);
            }

            $settingsModel->setAttributes($mergedSettings, false);
        }

        return $settingsModel;
    }
}
