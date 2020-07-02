<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\config\services;

use barrelstrength\sproutbase\config\base\Config as BaseConfig;
use barrelstrength\sproutbase\config\base\ConfigInterface;
use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\config\configs\ControlPanelConfig;
use barrelstrength\sproutbase\config\models\settings\ControlPanelSettings;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Plugin;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\helpers\StringHelper;
use craft\services\Plugins;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class Config extends Component
{
    const EVENT_REGISTER_SPROUT_CONFIG = 'registerSproutConfig';

    /** The Project Config key where Sprout settings are stored */
    const CONFIG_SPROUT_KEY = Plugins::CONFIG_PLUGINS_KEY.'.sprout';

    /**
     * @var array
     */
    protected $_configs = [];

    /**
     * @var ControlPanelSettings
     */
    private $_cpSettings;

    private $_configLoadStatus = 'not-loaded';

    private $_cpSettingsLoadStatus = 'not-loaded';

    public function getCpSettings(): ControlPanelSettings
    {
        if (!$this->_cpSettings) {
            $this->initControlPanelSettings();
        }

        return $this->_cpSettings;
    }

    /**
     * @return SproutBasePlugin[]
     */
    public function getSproutBasePlugins(): array
    {
        $plugins = Craft::$app->getPlugins()->getAllPlugins();

        // All Sprout plugins with shared settings extend SproutBasePlugin
        $sproutCentralPlugins = array_filter($plugins, static function($plugin) {
            return $plugin instanceof SproutBasePlugin;
        });

        return $sproutCentralPlugins;
    }

    /**
     * @param bool $includeFileSettings
     *
     * @return array [
     *     'campaigns' => CampaignsConfig(),
     *     'control-panel' => ControlPanelConfig(),
     *     'email' => EmailConfig(),
     *     'fields' => FieldsConfig(),
     *     'forms' => FormsConfig(),
     *     'lists' => ListsConfig(),
     *     'metadata' => MetadataConfig(),
     *     'redirects' => RedirectsConfig(),
     *     'reports' => ReportsConfig(),
     *     'sent-email' => SentEmailConfig(),
     *     'sitemaps' => SitemapsConfig()
     * ]
     */
    public function getConfigs($includeFileSettings = true): array
    {
        if (!$this->_configs) {
            $this->initConfigs($includeFileSettings);
        }

        return $this->_configs;
    }

    /**
     * @param string $handle
     * @param bool $includeFileSettings
     *
     * @return BaseConfig
     */
    public function getConfigByKey(string $handle, $includeFileSettings = true): BaseConfig
    {
        $this->getConfigs($includeFileSettings);

        return $this->_configs[$handle];
    }

    /**
     * @param bool $includeFileSettings
     */
    private function initConfigs($includeFileSettings = true)
    {
        $this->prepareContext($includeFileSettings);

        if ($this->_configLoadStatus === 'loaded' || $this->_configLoadStatus === 'loading') {
            return;
        }

        $this->_configLoadStatus = 'loading';

        $plugins = $this->getSproutBasePlugins();

        $cpConfig = new ControlPanelConfig();
        $cpConfig->setSettings($this->getCpSettings());
        $this->_configs['control-panel'] = $cpConfig;

        foreach ($plugins as $pluginHandle => $plugin) {

            $isPluginEnabled = Craft::$app->getPlugins()->isPluginEnabled($plugin->handle);
            if (!$isPluginEnabled) {
                continue;
            }

            $currentUser = Craft::$app->getUser()->getIdentity();
            $request = Craft::$app->getRequest();

            $configTypes = $plugin::getSproutConfigs();

            foreach ($configTypes as $configType) {

                /** @var BaseConfig $config */
                $config = new $configType();

                $permissionKey = StringHelper::camelCase($config::getKey());
                if ($request->getIsCpRequest() &&
                    !$request->getIsActionRequest() &&
                    $config::hasControlPanelSettings() &&
                    !$currentUser->can('sprout:'.$permissionKey.':accessModule')) {
                    continue;
                }

                if (isset($this->_configs[$config::getKey()])) {
                    continue;
                }

                $this->populateConfig($config);
                $this->_configs[$config::getKey()] = $config;

                $subModuleConfigTypes = $config::getSproutConfigDependencies();

                foreach ($subModuleConfigTypes as $subModuleConfigType) {

                    $subModuleConfig = new $subModuleConfigType();

                    $permissionKey = StringHelper::camelCase($subModuleConfig::getKey());
                    if ($request->getIsCpRequest() &&
                        !$request->getIsActionRequest() &&
                        $subModuleConfig::hasControlPanelSettings() &&
                        !$currentUser->can('sprout:'.$permissionKey.':accessModule')) {
                        continue;
                    }

                    if (isset($this->_configs[$subModuleConfig::getKey()])) {
                        continue;
                    }

                    $this->populateConfig($subModuleConfig);
                    $this->_configs[$subModuleConfig::getKey()] = $subModuleConfig;
                }
            }
        }

        $this->_configLoadStatus = 'loaded';
    }

    /**
     * Returns true if the given dependency exists in any other plugin
     *
     * @param        $pluginHandle
     * @param string $configType
     *
     * @return bool
     */
    public function isDependencyInUse($pluginHandle, string $configType): bool
    {
        $dependenciesInUse = $this->getDependenciesInUse($pluginHandle);

        if (in_array($configType, $dependenciesInUse, true)) {
            return true;
        }

        return false;
    }

    /**
     * @param SproutBasePlugin $plugin
     *
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function runInstallMigrations(SproutBasePlugin $plugin)
    {
        $sproutConfigTypes = $plugin->getSproutConfigs();

        foreach ($sproutConfigTypes as $sproutConfigType) {
            $sproutConfig = new $sproutConfigType();
            $subModuleConfigTypes = $sproutConfigType::getSproutConfigDependencies();

            // Run the safeUp method if our module has an Install migration
            if ($migration = $sproutConfig->createInstallMigration()) {
                ob_start();
                $migration->safeUp();
                ob_end_clean();

                $projectConfigSettingsKey = self::CONFIG_SPROUT_KEY.'.'.$sproutConfig::getKey();
                $settings = $sproutConfig->createSettingsModel();

                if ($settings) {
                    SproutBase::$app->settings->saveSettings($projectConfigSettingsKey, $settings);
                }
            }

            foreach ($subModuleConfigTypes as $subModuleConfigType) {
                $subModuleConfig = new $subModuleConfigType();

                // Run the safeUp method if our module has an Install migration
                if ($migration = $subModuleConfig->createInstallMigration()) {
                    ob_start();
                    $migration->safeUp();
                    ob_end_clean();

                    $projectConfigSettingsKey = self::CONFIG_SPROUT_KEY.'.'.$subModuleConfig::getKey();
                    $settings = $subModuleConfig->createSettingsModel();

                    if ($settings) {
                        SproutBase::$app->settings->saveSettings($projectConfigSettingsKey, $settings);
                    }
                }
            }
        }
    }

    /**
     * Runs all Install::safeDown() migrations for Sprout Central plugins
     *
     * @param SproutBasePlugin $plugin
     */
    public function runUninstallMigrations(SproutBasePlugin $plugin)
    {
        $sproutConfigTypes = $plugin->getSproutConfigs();

        foreach ($sproutConfigTypes as $sproutConfigType) {
            $isDependencyInUse = SproutBase::$app->config->isDependencyInUse('sprout-seo', $sproutConfigType);

            if ($isDependencyInUse) {
                continue;
            }

            // Run the safeDown method if our module has an Install migration
            if ($migration = $sproutConfigType->createInstallMigration()) {
                ob_start();
                $migration->safeDown();
                ob_end_clean();
            }

            $this->removeConfigSettingsToProjectConfig($sproutConfigType);

            $subModuleConfigTypes = $sproutConfigType::getSproutConfigDependencies();

            foreach ($subModuleConfigTypes as $subModuleConfigType) {
                $subModuleConfig = new $subModuleConfigType();

                // Run the safeUp method if our module has an Install migration
                if ($migration = $subModuleConfig->createInstallMigration()) {
                    ob_start();
                    $migration->safeDown();
                    ob_end_clean();
                }

                $this->removeConfigSettingsToProjectConfig($subModuleConfigType);
            }
        }
    }

    /**
     * @param ConfigInterface $config
     *
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function addConfigSettingsToProjectConfig(ConfigInterface $config)
    {
        if ($settings = $config->createSettingsModel()) {

            $settings->beforeAddDefaultSettings();

            $projectConfigSettingsKey = self::CONFIG_SPROUT_KEY.'.'.$config::getKey();
            $newSettings = ProjectConfigHelper::packAssociativeArrays($settings->toArray());

            Craft::$app->getProjectConfig()->set($projectConfigSettingsKey, $newSettings, "Added default Sprout Settings for “{$config::getKey()}”");
        }
    }

    /**
     * @param ConfigInterface $config
     */
    public function removeConfigSettingsToProjectConfig(ConfigInterface $config)
    {
        $projectConfigSettingsKey = self::CONFIG_SPROUT_KEY.'.'.$config::getKey();

        Craft::$app->getProjectConfig()->remove($projectConfigSettingsKey);
    }

    /**
     * @return array
     */
    public function getSproutCpSettings(): array
    {
        $configTypes = $this->getConfigs(false);

        foreach ($configTypes as $configType) {

            $settings = $configType->getSettings();

            if (!$settings || !$settings->getIsEnabled()) {
                continue;
            }

            $navItem = $settings->getSettingsNavItem();

            if (count($navItem) === 0) {
                continue;
            }

            $label = $configType->getName();

            $settingsPages[] = [
                'label' => $label,
                'url' => 'sprout/settings/'.$configType->getKey(),
                'icon' => Craft::getAlias('@sproutbaseassets/sprout/icons/'.$configType->getKey().'/icon.svg'),
            ];
        }

        return $settingsPages ?? [];
    }

    public function buildSproutNavItems(): array
    {
        $configTypes = $this->getConfigs(false);

        $cpNavItems = [];
        foreach ($configTypes as $key => $config) {
            $navItem = $config->getCpNavItem();

            if (empty($navItem)) {
                continue;
            }

            $settings = SproutBase::$app->settings->getSettingsByKey($config::getKey());

            if (!$settings->getIsEnabled()) {
                continue;
            }

            $label = !empty($config->getName())
                ? $config->getName()
                : $navItem['label'];

            $cpNavItems[$key] = [
                'label' => $label,
                'url' => $navItem['url'],
                'icon' => Craft::getAlias('@sproutbaseassets/sprout/icons/'.$config::getKey().'/icon-mask.svg'),
            ];

            if (!isset($navItem['subnav']) || count($navItem['subnav']) === 0) {
                continue;
            }

            $cpNavItems[$key]['subnav'] = $navItem['subnav'];
        }

        return $cpNavItems;
    }

    /**
     * @param array $cpNavItems
     *
     * @return array
     */
    public function updateCpNavItems(array $cpNavItems): array
    {
        $beforePluginNavItemKeys = [
            'dashboard',
            'entries',
            'globals',
            'categories',
            'assets',
            'users',
        ];

        $afterPluginNavItemKeys = [
            'graphql',
            'utilities',
            'settings',
            'plugin-store',
        ];

        $newCpNavItems = [];
        $afterCpNavItems = [];
        $otherCpNavItems = [];

        // Break out the current nav into multiple arrays that we can re-assemble later
        // 1. Craft defaults at the top of the nav
        // 2. Plugins and stuff
        // 3. Craft defaults and settings at bottom of nav
        foreach ($cpNavItems as $cpNavItem) {
            switch (true) {
                case (in_array($cpNavItem['url'], $beforePluginNavItemKeys, true)):
                    $newCpNavItems[] = $cpNavItem;
                    break;
                case (in_array($cpNavItem['url'], $afterPluginNavItemKeys, true)):
                    $afterCpNavItems[] = $cpNavItem;
                    break;
                default:
                    $otherCpNavItems[] = $cpNavItem;
                    break;
            }
        }

        $sproutNavItems = $this->buildSproutNavItems();

        // Add our module nav items to the plugins and stuff
        foreach ($sproutNavItems as $sproutNavItem) {
            $otherCpNavItems[] = $sproutNavItem;
        }

        // Sort custom nav items alphabetically by label
        uasort($otherCpNavItems, static function($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        // Add the custom nav items back to the nav
        foreach ($otherCpNavItems as $otherCpNavItem) {
            $newCpNavItems[] = $otherCpNavItem;
        }

        // Add the Craft defaults back to the bottom of the nav
        foreach ($afterCpNavItems as $afterCpNavItem) {
            $newCpNavItems[] = $afterCpNavItem;
        }

        return $newCpNavItems;
    }

    /**
     * Check if a plugin is a specific Edition
     *
     * @param $handle
     * @param $targetEdition
     *
     * @return bool
     */
    public function isEdition($handle, $targetEdition): bool
    {
        $config = $this->getConfigByKey($handle);

        if (!$config) {
            return false;
        }

        $currentEdition = $config->getEdition();

        return $currentEdition === $targetEdition;
    }

    public function isPluginEdition($pluginHandle, $edition): bool
    {
        /** @var Plugin $plugin */
        $plugin = Craft::$app->plugins->getPlugin($pluginHandle);

        if ($plugin === null) {
            return false;
        }

        return $plugin->is($edition) ? true : false;
    }

    public function getEdition($handle): string
    {
        $config = $this->getConfigByKey($handle);

        return $config->getEdition();
    }

    private function getDependenciesInUse($pluginHandle): array
    {
        $plugins = $this->getSproutBasePlugins();

        $configDependencies = [];
        foreach ($plugins as $key => $plugin) {
            // Exclude the plugin called in this method
            if ($plugin->getHandle() === $pluginHandle) {
                continue;
            }

            $configTypes = $plugin::getSproutConfigs();
            foreach ($configTypes as $configType) {
                $configDependencies[] = $configType;

                $subModuleConfigTypes = $configType::getSproutConfigDependencies();
                foreach ($subModuleConfigTypes as $subModuleConfigType) {
                    $configDependencies[] = $subModuleConfigType;
                }
            }
        }

        return array_unique($configDependencies);
    }

    /**
     * @param bool $includeFileSettings
     */
    private function prepareContext(bool $includeFileSettings)
    {
        $oldContext = SproutBase::$app->settings->context;

        if ($includeFileSettings === false) {
            $newContext = Settings::SETTINGS_CONTEXT_SETTINGS;
        } else {
            $newContext = Settings::SETTINGS_CONTEXT_APP;
        }

        if ($oldContext !== $newContext) {
            SproutBase::$app->settings->context = $newContext;

            // Rebuild the config array if a new context triggered
            $this->_configLoadStatus = 'not-loaded';
        }
    }

    private function initControlPanelSettings()
    {
        if ($this->_cpSettings ||
            $this->_cpSettingsLoadStatus === 'loaded' ||
            $this->_cpSettingsLoadStatus === 'loading') {
            return;
        }

        $this->_cpSettingsLoadStatus = 'loading';

        $cpConfig = new ControlPanelConfig();

        /** @var ControlPanelSettings $cpSettings */
        $cpSettings = SproutBase::$app->settings->mergeSettings($cpConfig);

        // Control Panel module has unique needs when returning settings
        $moduleSettings = $cpSettings->modules;

        // Update settings to be indexed by module key
        $moduleKeys = array_column($moduleSettings, 'moduleKey');
        $moduleSettings = array_combine($moduleKeys, $moduleSettings);

        $cpSettings->modules = $moduleSettings;

        $this->_cpSettings = $cpSettings;

        $this->_cpSettingsLoadStatus = 'loaded';
    }

    public function getUserPermissions(): array
    {
        $configTypes = SproutBase::$app->config->getConfigs(false);

        $permissions = [];
        foreach ($configTypes as $configType) {
            // Don't worry about it if no permissions exist
            if (!method_exists($configType, 'getUserPermissions')) {
                continue;
            }

            $nestedPermissions = [];
            foreach ($configType->getUserPermissions() as $permissionName => $permissionArray) {
                $nestedPermissions[$permissionName] = $permissionArray;
            }

            $permissionKey = StringHelper::camelCase($configType->getKey());
            $permissions['sprout:'.$permissionKey.':accessModule'] = [
                'label' => 'Access '.$configType->getName(),
                'nested' => $nestedPermissions,
            ];
        }

        ksort($permissions, SORT_NATURAL);

        return $permissions;
    }

    /**
     * @param string $method
     *
     * @return array
     */
    public function getComponentMap(string $method): array
    {
        $cpSettings = SproutBase::$app->config->getCpSettings();

        $sproutModules = SproutBase::SPROUT_MODULES;
        $mapping = [];

        foreach ($sproutModules as $moduleClassName) {
            // Only load enabled, modules with control panel settings
            // Any modules with no control panel settings should be added
            // to barrelstrength\sproutbase\config\base\Config::getSproutConfigs()
            // as config submodules
            if ($moduleClassName::hasControlPanelSettings() === true &&
                !$cpSettings->isModuleEnabled($moduleClassName::getKey())) {
                continue;
            }

            if (!method_exists($moduleClassName, $method)) {
                continue;
            }

            $moduleMap = $moduleClassName::$method();
            $subModules = $moduleClassName::getSproutConfigDependencies();

            foreach ($moduleMap as $key => $className) {
                $mapping[$key] = $className;

                foreach ($subModules as $subModuleClassName) {
                    if (!method_exists($moduleClassName, $method)) {
                        continue;
                    }

                    $subModuleMap = $subModuleClassName::$method();
                    foreach ($subModuleMap as $subKey => $subClassName) {
                        $mapping[$subKey] = $subClassName;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * @param BaseConfig $config
     */
    private function populateConfig(BaseConfig $config)
    {
        $cpSettings = SproutBase::$app->config->getCpSettings();

        $config->setEdition();

        $moduleSettings = $cpSettings->modules[$config::getKey()] ?? null;

        $alternateName = !empty($moduleSettings['alternateName'])
            ? $moduleSettings['alternateName']
            : null;
        $enableUpgradeMessages = !empty($cpSettings['enableUpgradeMessages'])
            ? true
            : false;

        $config->setAlternateName($alternateName);
        $config->setEnableUpgradeMessages($enableUpgradeMessages);

        if ($settings = SproutBase::$app->settings->getSettingsByConfig($config)) {
            $config->setSettings($settings);
        }
    }
}
