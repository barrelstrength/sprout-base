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
use barrelstrength\sproutbase\config\configs\ControlPanelConfig;
use barrelstrength\sproutbase\config\models\settings\ControlPanelSettings;
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
     * @var ControlPanelSettings
     */
    private $_controlPanelSettings;

    private $_cpSettingsLoadStatus = 'not-loaded';

    public function initControlPanelSettings()
    {
        if ($this->_controlPanelSettings ||
            $this->_cpSettingsLoadStatus === 'loaded' ||
            $this->_cpSettingsLoadStatus === 'loading') {
            return;
        }

        $this->_cpSettingsLoadStatus = 'loading';

        $cpConfig = new ControlPanelConfig();

        /** @var ControlPanelSettings $cpSettings */
        $cpSettings = $this->mergeSettings($cpConfig);

        // Control Panel module has unique needs when returning settings
        $moduleSettings = $cpSettings->modules;

        // Update settings to be indexed by module key
        $moduleKeys = array_column($moduleSettings, 'moduleKey');
        $moduleSettings = array_combine($moduleKeys, $moduleSettings);

        $cpSettings->modules = $moduleSettings;

        $this->_controlPanelSettings = $cpSettings;

        $this->_cpSettingsLoadStatus = 'loaded';
    }

    /**
     * Gets settings as defined in project config
     *
     * @param bool $includeFileConfigSettings
     *
     * @return array [
     *     'campaigns' => new CampaignsSetting,
     *     'control-panel' => new ControlPanelSettings,
     *     'email' => new EmailSettings,
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
        $this->initControlPanelSettings();

        $settings = $this->mergeSettings($config);

        if (!$settings) {
            return null;
        }

        // Update the settings to add alternateName and enabled settings
        $cpSettings = $this->_controlPanelSettings ?? null;

        if (!$cpSettings) {
            return $settings;
        }

        $moduleSettings = $cpSettings->modules[$config->getKey()] ?? null;

        $alternateName = !empty($moduleSettings['alternateName'])
            ? $moduleSettings['alternateName']
            : null;
        $enabledStatus = !empty($moduleSettings['enabled'])
            ? $moduleSettings['enabled']
            : false;

        $settings->setAlternateName($alternateName);
        $settings->setIsEnabled($enabledStatus);

        return $settings;
    }

    /**
     * Gets current settings as defined in project config and config overrides
     *
     * @param $handle = [
     *     'campaigns',
     *     'control-panel',
     *     'email'
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
    protected function mergeSettings(ConfigInterface $configType)
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
