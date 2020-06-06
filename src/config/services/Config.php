<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\config\services;

use barrelstrength\sproutbase\config\base\ConfigInterface;
use barrelstrength\sproutbase\config\base\SproutCentralInterface;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use yii\base\Component;

class Config extends Component
{
    const EVENT_REGISTER_SPROUT_CONFIG = 'registerSproutConfig';

    /** The Project Config key where Sprout settings are stored */
    const CONFIG_SPROUT_KEY = Plugins::CONFIG_PLUGINS_KEY.'.sprout';

    /**
     * @return SproutCentralInterface[]
     */
    public function getSproutCentralPlugins(): array
    {
        $plugins = Craft::$app->getPlugins()->getAllPlugins();

        // All Sprout plugins with shared settings implement SproutCentralInterface
        $sproutCentralPlugins = array_filter($plugins, static function($plugin) {
            return $plugin instanceof SproutCentralInterface;
        });

        return $sproutCentralPlugins;
    }

    /**
     * @param bool  $enabledOnly
     * @param array $excludedPluginHandles
     *
     * @return ConfigInterface[]
     */
    public function getConfigs($enabledOnly = true, $excludedPluginHandles = []): array
    {
        $plugins = $this->getSproutCentralPlugins();

        // Make sure we have an array, if only one handle is given
        if (!is_array($excludedPluginHandles)) {
            $excludedPluginHandles = [$excludedPluginHandles];
        }

        $configs = [];

        foreach ($plugins as $plugin) {
            // Enabled?
            $isPluginEnabled = Craft::$app->getPlugins()->isPluginEnabled($plugin->handle);
            if ($enabledOnly && !$isPluginEnabled) {
                continue;
            }

            // Exclude?
            if (in_array($plugin->getHandle(), $excludedPluginHandles, true)) {
                continue;
            }

            $configTypes = $plugin->getSproutConfigs();

            foreach ($configTypes as $configType) {
                $sproutConfig = new $configType();
                $configs[$sproutConfig->getKey()] = $sproutConfig;
            }
        }

        return $configs;
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

    public function runInstallMigrations(SproutCentralPlugin $plugin)
    {
        $sproutConfigTypes = $plugin->getSproutConfigs();

        foreach ($sproutConfigTypes as $sproutConfigType) {

            $config = new $sproutConfigType();

            // Run the safeUp method if our module has an Install migration
            if ($migration = $config->createInstallMigration()) {
                ob_start();
                $migration->safeUp();
                ob_end_clean();
            }
        }
    }

    /**
     * Runs all Install::safeDown() migrations for
     * Sprout Central plugins that are not in use
     *
     * @param SproutCentralPlugin $plugin
     */
    public function runUninstallMigrations(SproutCentralPlugin $plugin)
    {
        $sproutConfigTypes = $plugin->getSproutConfigs();

        foreach ($sproutConfigTypes as $sproutConfigType) {
            $isDependencyInUse = SproutBase::$app->config->isDependencyInUse('sprout-seo', $sproutConfigType);

            if ($isDependencyInUse) {
                continue;
            }

            $config = new $sproutConfigType();

            // Run the safeDown method if our module has an Install migration
            if ($migration = $config->createInstallMigration()) {
                ob_start();
                $migration->safeDown();
                ob_end_clean();
            }
        }
    }

    public function getSproutCpSettings(): array
    {
        $configTypes = $this->getConfigs();

        $settingsPages = [];
        foreach ($configTypes as $configType) {
            $settings = $configType->createSettingsModel();

            if (!$settings) {
                continue;
            }

            $navItem = $settings->getSettingsNavItem();

            if (!isset($navItem['subnav']) || count($navItem['subnav']) === 0) {
                continue;
            }

            $settingsPages[] = [
                'label' => $navItem['label'],
                'url' => $navItem['url'],
                'icon' => $navItem['icon'],
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

            $cpNavItems[$key] = [
                'label' => $navItem['label'],
                'url' => $navItem['url'],
                'icon' => $navItem['icon'],
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

        // Filter out all plugins that don't extend SproutCentralPlugin
        // and exclude the plugin calling this method
        $sproutPluginKeys = array_keys(array_filter($plugins, static function($plugin) {
            return $plugin instanceof SproutCentralPlugin;
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
        $plugins = $this->getSproutCentralPlugins();

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
