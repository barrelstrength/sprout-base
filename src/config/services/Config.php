<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\config\services;

use barrelstrength\sproutbase\config\base\ConfigInterface;
use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Plugin;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\services\Plugins;
use ReflectionException;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

/**
 *
 * @property ConfigInterface[]  $configs
 * @property SproutBasePlugin[] $sproutBasePlugins
 * @property array              $sproutCpSettings
 */
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
     * @var bool Whether Configs have been loaded yet for this request
     */
    private $_configsLoaded = false;

    /**
     * @var bool Whether Configs are in the middle of being loaded
     */
    private $_configsLoading = false;

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
     * @return array
     */
    public function getConfigs(): array
    {
        $this->loadConfigs();

        return $this->_configs;
    }

    public function getConfig(string $handle)
    {
        $this->loadConfigs();

        return $this->_configs[$handle] ?? null;
    }

    public function loadConfigs()
    {
        if ($this->_configsLoaded === true || $this->_configsLoading === true) {
            return;
        }

        $this->_configsLoading = true;

        $plugins = $this->getSproutBasePlugins();

        foreach ($plugins as $plugin) {

            $isPluginEnabled = Craft::$app->getPlugins()->isPluginEnabled($plugin->handle);
            if (!$isPluginEnabled) {
                continue;
            }

            $configTypes = $plugin::getSproutConfigs();

            foreach ($configTypes as $configType) {
                $sproutConfig = new $configType();

                // Assumes we only have two editions in any given plugin
                if ($sproutConfig->getEdition() !== 'pro') {
                    $sproutConfig->setEdition($plugin->edition);
                }

                $configSettings = $sproutConfig->getConfigSettings();

                $configSettingsArray = [];
                foreach ($configSettings as $settingName => $settings) {
                    $configSettingsArray[$settingName] = $settings;
                }

                $sproutConfig->setSettings($configSettingsArray);

                $this->_configs[$sproutConfig->getKey()] = $sproutConfig;
            }
        }

        $this->_configsLoading = false;
        $this->_configsLoaded = true;
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

            // Run the safeUp method if our module has an Install migration
            if ($migration = $sproutConfigType->createInstallMigration()) {
                ob_start();
                $migration->safeUp();
                ob_end_clean();
            }

            $this->addConfigSettingsToProjectConfig($sproutConfigType);
        }
    }

    /**
     * Runs all Install::safeDown() migrations for Sprout Central plugins
     *
     * @param SproutBasePlugin $plugin
     *
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

            $projectConfigSettingsKey = self::CONFIG_SPROUT_KEY.'.'.$config->getKey();
            $newSettings = ProjectConfigHelper::packAssociativeArrays($settings->toArray());

            Craft::$app->getProjectConfig()->set($projectConfigSettingsKey, $newSettings, "Added default Sprout Settings for “{$config->getKey()}”");
        }
    }

    /**
     * @param ConfigInterface $config
     */
    public function removeConfigSettingsToProjectConfig(ConfigInterface $config)
    {
        $projectConfigSettingsKey = self::CONFIG_SPROUT_KEY.'.'.$config->getKey();

        Craft::$app->getProjectConfig()->remove($projectConfigSettingsKey);
    }

    public function getSproutCpSettings(): array
    {
        $configTypes = $this->getConfigs();

        $settingsPages = [];
        foreach ($configTypes as $configType) {
            $settings = SproutBase::$app->settings->getSettingsByKey($configType->getKey());

            if (!$settings || !$settings->getIsEnabled()) {
                continue;
            }


            $navItem = $settings->getSettingsNavItem();

            if (count($navItem) === 0) {
                continue;
            }

            $label = $settings->getAlternateName() ?? $configType::displayName();

            $settingsPages[] = [
                'label' => $label,
                'url' => 'sprout/settings/'.$configType->getKey(),
                'icon' => Craft::getAlias('@sproutbaseassets/sprout/icons/'.$configType->getKey().'/icon.svg'),
            ];
        }

        return $settingsPages;
    }

    public function buildSproutNavItems(): array
    {
        $configTypes = $this->getConfigs();

        $cpNavItems = [];
        foreach ($configTypes as $key => $configType) {
            $config = new $configType();
            $navItem = $config->getCpNavItem();

            if (empty($navItem)) {
                continue;
            }

            $settings = SproutBase::$app->settings->getSettingsByKey($configType->getKey());

            if (!$settings->getIsEnabled()) {
                continue;
            }

            $label = $settings->getAlternateName() ?? $navItem['label'];

            $cpNavItems[$key] = [
                'label' => $label,
                'url' => $navItem['url'],
                'icon' => Craft::getAlias('@sproutbaseassets/sprout/icons/'.$configType->getKey().'/icon-mask.svg'),
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
     * @param array $sproutNavItems
     *
     * @return array
     */
    public function updateCpNavItems(array $cpNavItems, array $sproutNavItems): array
    {
        $plugins = Craft::$app->getPlugins()->getAllPlugins();

        // Filter out all plugins that don't extend SproutBasePlugin
        // and exclude the plugin calling this method
        $sproutPluginKeys = array_keys(array_filter($plugins, static function($plugin) {
            return $plugin instanceof SproutBasePlugin;
        }));

        $defaultSproutCpNavItems = array_filter($cpNavItems, static function($navItem) use ($sproutPluginKeys) {
            return in_array($navItem['url'], $sproutPluginKeys, true);
        });

        $firstPosition = null;
        foreach ($defaultSproutCpNavItems as $key => $defaultSproutCpNavItem) {
            if ($firstPosition === null) {
                $firstPosition = $key;
            }
            unset($cpNavItems[$key]);
        }

        foreach ($sproutNavItems as $sproutNavItem) {
            $cpNavItems[] = $sproutNavItem;
        }

        return $cpNavItems;
    }

    /**
     * Check if a plugin is a specific Edition
     *
     * @param $pluginHandle
     * @param $edition
     *
     * @return bool
     */
    public function isEdition($pluginHandle, $edition): bool
    {
        /** @var Plugin $plugin */
        $plugin = Craft::$app->plugins->getPlugin($pluginHandle);

        return $plugin !== null ? $plugin->is($edition) : false;
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

            $configTypes = $plugin->getSproutConfigs();
            foreach ($configTypes as $configType) {
                $configDependencies[] = $configType;
            }
        }

        return array_unique($configDependencies);
    }
}
